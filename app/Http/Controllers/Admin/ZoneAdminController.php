<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyZoneAdminRequest;
use App\Http\Requests\StoreZoneAdminRequest;
use App\Http\Requests\UpdateZoneAdminRequest;
use App\Models\Cartographer;
use App\Models\ZoneAdmin;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class ZoneAdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('zone_admin_access') ? null : Cartographer::allowedIdsFor($user, ZoneAdmin::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $zoneAdmins = ZoneAdmin::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (ZoneAdmin::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.zoneAdmins.index', compact('zoneAdmins'));
    }

    public function create()
    {
        abort_if(Gate::denies('zone_admin_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.zoneAdmins.create');
    }

    public function store(StoreZoneAdminRequest $request)
    {
        ZoneAdmin::create($request->all());

        return redirect()->route('admin.zone-admins.index');
    }

    public function edit(ZoneAdmin $zoneAdmin)
    {
        abort_if(Gate::denies('edit-object', $zoneAdmin), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.zoneAdmins.edit', compact('zoneAdmin'));
    }

    public function update(UpdateZoneAdminRequest $request, ZoneAdmin $zoneAdmin)
    {
        abort_if(Gate::denies('edit-object', $zoneAdmin), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zoneAdmin->update($request->all());

        return redirect()->route('admin.zone-admins.index');
    }

    public function show(ZoneAdmin $zoneAdmin)
    {
        abort_if(Gate::denies('show-object', $zoneAdmin), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zoneAdmin->load('annuaires', 'forestAds');

        return view('admin.zoneAdmins.show', compact('zoneAdmin'));
    }

    public function destroy(ZoneAdmin $zoneAdmin)
    {
        abort_if(Gate::denies('zone_admin_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zoneAdmin->delete();

        return redirect()->route('admin.zone-admins.index');
    }

    public function massDestroy(MassDestroyZoneAdminRequest $request)
    {
        ZoneAdmin::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
