<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyManRequest;
use App\Http\Requests\StoreManRequest;
use App\Http\Requests\UpdateManRequest;
use App\Models\Cartographer;
use App\Models\Lan;
use App\Models\Man;
use App\Models\Wan;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class ManController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('man_access') ? null : Cartographer::allowedIdsFor($user, Man::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $mans = Man::query()
            ->with('wans', 'lans', 'parentMan')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Man::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.mans.index', compact('mans'));
    }

    public function create()
    {
        abort_if(Gate::denies('man_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lans = Lan::query()->orderBy('name')->pluck('name', 'id');
        $mans = Man::query()->orderBy('name')->pluck('name', 'id');
        $wans = Wan::query()->orderBy('name')->pluck('name', 'id');

        return view('admin.mans.create',
            compact('lans', 'mans', 'wans'));
    }

    public function store(StoreManRequest $request)
    {
        $man = Man::query()->create($request->all());

        $man->wans()->sync($request->input('wans', []));
        $man->lans()->sync($request->input('lans', []));

        return redirect()->route('admin.mans.index');
    }

    public function edit(Man $man)
    {
        abort_if(Gate::denies('edit-object', $man), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $wans = Wan::query()->orderBy('name')->pluck('name', 'id');
        $lans = Lan::query()->orderBy('name')->pluck('name', 'id');
        $mans = Man::query()->where('id', '!=', $man->id)
            ->orderBy('name')->pluck('name', 'id');

        $man->load('lans');

        return view('admin.mans.edit',
            compact('lans', 'mans', 'wans', 'man'));
    }

    public function update(UpdateManRequest $request, Man $man)
    {
        abort_if(Gate::denies('edit-object', $man), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $man->update($request->all());

        $man->wans()->sync($request->input('wans', []));
        $man->lans()->sync($request->input('lans', []));

        return redirect()->route('admin.mans.index');
    }

    public function show(Man $man)
    {
        abort_if(Gate::denies('show-object', $man), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $man->load('lans', 'wans');

        return view('admin.mans.show', compact('man'));
    }

    public function destroy(Man $man)
    {
        abort_if(Gate::denies('man_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $man->delete();

        return redirect()->route('admin.mans.index');
    }

    public function massDestroy(MassDestroyManRequest $request)
    {
        Man::query()->whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
