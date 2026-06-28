<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAnnuaireRequest;
use App\Http\Requests\StoreAnnuaireRequest;
use App\Http\Requests\UpdateAnnuaireRequest;
use App\Models\Annuaire;
use App\Models\Cartographer;
use App\Models\ZoneAdmin;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class AnnuaireController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('annuaire_access') ? null : Cartographer::allowedIdsFor($user, Annuaire::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $annuaires = Annuaire::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Annuaire::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.annuaires.index', compact('annuaires'));
    }

    public function store(StoreAnnuaireRequest $request)
    {
        Annuaire::create($request->all());

        return redirect()->route('admin.annuaires.index');
    }

    public function create()
    {
        abort_if(Gate::denies('annuaire_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zone_admins = ZoneAdmin::all()->sortBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.annuaires.create', compact('zone_admins'));
    }

    public function edit(Annuaire $annuaire)
    {
        abort_if(Gate::denies('edit-object', $annuaire), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zone_admins = ZoneAdmin::all()->sortBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $annuaire->load('zoneAdmin');

        return view('admin.annuaires.edit', compact('zone_admins', 'annuaire'));
    }

    public function update(UpdateAnnuaireRequest $request, Annuaire $annuaire)
    {
        abort_if(Gate::denies('edit-object', $annuaire), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $annuaire->update($request->all());

        return redirect()->route('admin.annuaires.index');
    }

    public function show(Annuaire $annuaire)
    {
        abort_if(Gate::denies('show-object', $annuaire), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $annuaire->load('zoneAdmin');

        return view('admin.annuaires.show', compact('annuaire'));
    }

    public function destroy(Annuaire $annuaire)
    {
        abort_if(Gate::denies('annuaire_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $annuaire->delete();

        return redirect()->route('admin.annuaires.index');
    }

    public function massDestroy(MassDestroyAnnuaireRequest $request)
    {
        Annuaire::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
