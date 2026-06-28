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
        $models = $this->cartographiableModels();
        $routes = Cartographer::cartographiableRoutesMap();

        return view('admin.cartographers.index', compact('cartographers', 'models', 'routes'));
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('cartographer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $models = $this->cartographiableModels();
        asort($models);
        $users = User::orderBy('name')->pluck('name', 'id');
        $roles = Role::orderBy('title')->pluck('title', 'id');
        $userId = $request->query('user_id');
        $roleId = $request->query('role_id');

        return view('admin.cartographers.create', compact('models', 'users', 'roles', 'userId', 'roleId'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('cartographer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'objects' => ['required', 'array', 'min:1'],
            'objects.*' => ['string'],
        ]);

        if (empty($validated['user_id']) && empty($validated['role_id'])) {
            return back()->withErrors(['user_id' => trans('cruds.cartographer.errors.user_or_role_required')])->withInput();
        }

        if (! empty($validated['user_id']) && ! empty($validated['role_id'])) {
            return back()->withErrors(['user_id' => trans('cruds.cartographer.errors.user_and_role_exclusive')])->withInput();
        }

        $allowedTypes = $this->cartographiableModels();

        foreach ($validated['objects'] as $entry) {
            $parts = explode('|', $entry, 2);
            if (count($parts) !== 2) {
                continue;
            }
            [$type, $rawId] = $parts;
            if (! array_key_exists($type, $allowedTypes)) {
                continue;
            }
            $id = filter_var($rawId, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                continue;
            }

            Cartographer::firstOrCreate([
                'cartographiable_type' => $type,
                'cartographiable_id' => $id,
                'user_id' => $validated['user_id'] ?? null,
                'role_id' => $validated['role_id'] ?? null,
            ]);
        }

        return redirect()->route('admin.cartographers.index')->with('status', trans('cruds.cartographer.saved'));
    }

    public function associatedObjects(Request $request): JsonResponse
    {
        abort_if(Gate::denies('cartographer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userId = $request->query('user_id');
        $roleId = $request->query('role_id');

        if (! $userId && ! $roleId) {
            return response()->json([]);
        }

        $models = $this->cartographiableModels();

        $query = Cartographer::query()->with('cartographiable');
        if ($userId) {
            $query->where('user_id', (int) $userId);
        } else {
            $query->where('role_id', (int) $roleId);
        }

        $rows = $query->get();

        $results = [];
        foreach ($rows as $c) {
            $typeLabel = $models[$c->cartographiable_type] ?? $c->cartographiable_type;
            $related = $c->cartographiable;
            $objectName = $related !== null
                ? (string) ($related->getAttribute('name') ?? '(id:'.$c->cartographiable_id.')')
                : '(id:'.$c->cartographiable_id.')';
            $results[] = [
                'type' => $c->cartographiable_type,
                'id' => $c->cartographiable_id,
                'name' => $objectName,
                'label' => $typeLabel.' — '.$objectName,
            ];
        }

        return response()->json($results);
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
        $user = auth()->user();
        $models = $this->cartographiableModels();
        $routes = Cartographer::cartographiableRoutesMap();

        $roleIds = $user->roles()->pluck('roles.id');

        $cartographers = Cartographer::where('user_id', $user->id)
            ->orWhereIn('role_id', $roleIds)
            ->with('cartographiable')
            ->orderBy('cartographiable_type')
            ->get()
            ->unique(fn ($c) => $c->cartographiable_type.'#'.$c->cartographiable_id);

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
