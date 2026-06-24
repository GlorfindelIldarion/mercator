<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyZoneRequest;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\AdminUser;
use App\Models\Building;
use App\Models\Cartographer;
use App\Models\Zone;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class ZoneController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('zone_access') ? null : Cartographer::allowedIdsFor($user, Zone::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $zones = Zone::with('parentZones', 'childZones')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Zone::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')

            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.zones.index', compact('zones'));
    }

    public function create()
    {
        abort_if(Gate::denies('zone_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zones = Zone::orderBy('name')->pluck('name', 'id');
        $buildings = Building::orderBy('name')->pluck('name', 'id');
        $adminUsers = AdminUser::orderBy('user_id')->pluck('user_id', 'id');
        $type_list = Zone::query()->select('type')->whereNotNull('type')->where('type', '<>', '')->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.zones.create', compact('zones', 'buildings', 'adminUsers', 'type_list', 'attributes_list'));
    }

    public function store(StoreZoneRequest $request)
    {
        $request['attributes'] = implode(' ', $request->input('attributes') ?? []);
        $zone = Zone::create($request->only(['name', 'type', 'attributes', 'description']));
        $zone->parentZones()->sync($request->input('parentZones', []));
        $zone->childZones()->sync($request->input('childZones', []));
        $zone->buildings()->sync($request->input('buildings', []));
        $zone->adminUsers()->sync($request->input('adminUsers', []));

        return redirect()->route('admin.zones.index');
    }

    public function edit(Zone $zone)
    {
        abort_if(Gate::denies('edit-object', $zone), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zones = Zone::orderBy('name')->pluck('name', 'id');
        $buildings = Building::orderBy('name')->pluck('name', 'id');
        $adminUsers = AdminUser::orderBy('user_id')->pluck('user_id', 'id');
        $type_list = Zone::query()->select('type')->whereNotNull('type')->where('type', '<>', '')->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();
        $zone->load('parentZones', 'childZones', 'buildings', 'adminUsers');

        return view('admin.zones.edit', compact('zone', 'zones', 'buildings', 'adminUsers', 'type_list', 'attributes_list'));
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        abort_if(Gate::denies('edit-object', $zone), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->input('attributes') ?? []);
        $zone->update($request->only(['name', 'type', 'attributes', 'description']));
        $zone->parentZones()->sync($request->input('parentZones', []));
        $zone->childZones()->sync($request->input('childZones', []));
        $zone->buildings()->sync($request->input('buildings', []));
        $zone->adminUsers()->sync($request->input('adminUsers', []));

        return redirect()->route('admin.zones.index');
    }

    public function show(Zone $zone)
    {
        abort_if(Gate::denies('show-object', $zone), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zone->load('parentZones', 'childZones', 'buildings', 'adminUsers');

        return view('admin.zones.show', compact('zone'));
    }

    public function destroy(Zone $zone)
    {
        abort_if(Gate::denies('zone_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zone->delete();

        return redirect()->route('admin.zones.index');
    }

    public function massDestroy(MassDestroyZoneRequest $request)
    {
        Zone::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes(): array
    {
        $raw = Zone::query()->select('attributes')->whereNotNull('attributes')->where('attributes', '<>', '')->pluck('attributes');
        $res = [];
        foreach ($raw as $item) {
            foreach (explode(' ', $item) as $tag) {
                $tag = trim($tag);
                if ($tag !== '') {
                    $res[] = $tag;
                }
            }
        }
        sort($res);

        return array_unique($res);
    }
}
