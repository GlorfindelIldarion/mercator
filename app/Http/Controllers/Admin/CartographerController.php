<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyCartographerRequest;
use App\Models\Cartographer;
use App\Models\Role;
use App\Models\User;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CartographerController extends Controller
{
    private function cartographiableModels(): array
    {
        return Cartographer::cartographiableModelsList();
    }

    public function index()
    {
        abort_if(Gate::denies('cartographer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $cartographers = Cartographer::with(['user', 'role', 'cartographiable'])->orderBy('cartographiable_type')->paginate(50);
        $models  = $this->cartographiableModels();
        $routes  = Cartographer::cartographiableRoutesMap();

        return view('admin.cartographers.index', compact('cartographers', 'models', 'routes'));
    }

    public function create()
    {
        abort_if(Gate::denies('cartographer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $models = $this->cartographiableModels();
        asort($models);
        $users  = User::orderBy('name')->pluck('name', 'id');
        $roles  = Role::orderBy('title')->pluck('title', 'id');

        return view('admin.cartographers.create', compact('models', 'users', 'roles'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('cartographer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'cartographiable_type' => ['required', 'string', 'in:' . implode(',', array_keys($this->cartographiableModels()))],
            'cartographiable_id'   => ['required', 'integer', 'min:1'],
            'user_id'              => ['nullable', 'integer', 'exists:users,id'],
            'role_id'              => ['nullable', 'integer', 'exists:roles,id'],
        ]);

        if (empty($validated['user_id']) && empty($validated['role_id'])) {
            return back()->withErrors(['user_id' => trans('cruds.cartographer.errors.user_or_role_required')])->withInput();
        }

        if (! empty($validated['user_id']) && ! empty($validated['role_id'])) {
            return back()->withErrors(['user_id' => trans('cruds.cartographer.errors.user_and_role_exclusive')])->withInput();
        }

        Cartographer::firstOrCreate(array_filter([
            'cartographiable_type' => $validated['cartographiable_type'],
            'cartographiable_id'   => $validated['cartographiable_id'],
            'user_id'              => $validated['user_id'] ?? null,
            'role_id'              => $validated['role_id'] ?? null,
        ]));

        return redirect()->route('admin.cartographers.index')->with('status', 'Cartographe ajouté.');
    }

    public function edit(Cartographer $cartographer)
    {
        abort_if(Gate::denies('edit-object', $cartographer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $models = $this->cartographiableModels();
        asort($models);
        $users  = User::orderBy('name')->pluck('name', 'id');
        $roles  = Role::orderBy('title')->pluck('title', 'id');

        return view('admin.cartographers.edit', compact('cartographer', 'models', 'users', 'roles'));
    }

    public function update(Request $request, Cartographer $cartographer)
    {
        abort_if(Gate::denies('edit-object', $cartographer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ]);

        if (empty($validated['user_id']) && empty($validated['role_id'])) {
            return back()->withErrors(['user_id' => trans('cruds.cartographer.errors.user_or_role_required')])->withInput();
        }

        if (! empty($validated['user_id']) && ! empty($validated['role_id'])) {
            return back()->withErrors(['user_id' => trans('cruds.cartographer.errors.user_and_role_exclusive')])->withInput();
        }

        $cartographer->update([
            'user_id' => $validated['user_id'] ?? null,
            'role_id' => $validated['role_id'] ?? null,
        ]);

        return redirect()->route('admin.cartographers.index')->with('status', 'Cartographe modifié.');
    }

    public function destroy(Cartographer $cartographer)
    {
        abort_if(Gate::denies('cartographer_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $cartographer->delete();

        return redirect()->route('admin.cartographers.index')->with('status', 'Cartographe supprimé.');
    }

    public function massDestroy(MassDestroyCartographerRequest $request)
    {
        Cartographer::query()->whereIn('id', $request->input('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function list()
    {
        $user    = auth()->user();
        $models  = $this->cartographiableModels();
        $routes  = Cartographer::cartographiableRoutesMap();

        $roleIds = $user->roles()->pluck('roles.id');

        $cartographers = Cartographer::where('user_id', $user->id)
            ->orWhereIn('role_id', $roleIds)
            ->with('cartographiable')
            ->orderBy('cartographiable_type')
            ->get()
            ->unique(fn ($c) => $c->cartographiable_type . '#' . $c->cartographiable_id);

        return view('admin.cartographers.list', compact('cartographers', 'models', 'routes'));
    }

    public function getObjects(Request $request): JsonResponse
    {
        abort_if(Gate::denies('cartographer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $type = $request->query('type');

        if (! array_key_exists($type, $this->cartographiableModels())) {
            return response()->json([]);
        }

        return response()->json(
            $type::orderBy('name')->get(['id', 'name'])
        );
    }
}
