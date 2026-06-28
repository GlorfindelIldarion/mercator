<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyVlanRequest;
use App\Http\Requests\StoreVlanRequest;
use App\Http\Requests\UpdateVlanRequest;
use App\Models\Cartographer;
use App\Models\Subnetwork;
use App\Models\Vlan;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VlanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('vlan_access') ? null : Cartographer::allowedIdsFor($user, Vlan::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $vlans = Vlan::with('subnetworks')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Vlan::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')

            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.vlans.index', compact('vlans'));
    }

    public function clone(Request $request)
    {
        abort_if(Gate::denies('vlan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');

        // Get Vlan
        $vlan = Vlan::find($request['id']);

        // Vlan not found
        abort_if($vlan === null, Response::HTTP_NOT_FOUND, '404 Not Found');

        $request->merge($vlan->only($vlan->getFillable()));
        $request->merge(['subnetworks' => $vlan->subnetworks()->pluck('id')->unique()->toArray()]);
        $request->flash();

        return view('admin.vlans.create', compact('subnetworks'));
    }

    public function store(StoreVlanRequest $request)
    {
        $vlan = Vlan::create($request->all());

        DB::table('subnetworks')
            ->where('vlan_id', $vlan->id)
            ->update(['vlan_id' => null]);

        DB::table('subnetworks')
            ->whereIn('id', $request->input('subnetworks', []))
            ->update(['vlan_id' => $vlan->id]);

        return redirect()->route('admin.vlans.index');
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('vlan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');

        return view('admin.vlans.create', compact('subnetworks'));
    }

    public function update(UpdateVlanRequest $request, Vlan $vlan)
    {
        abort_if(Gate::denies('edit-object', $vlan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlan->update($request->all());

        DB::table('subnetworks')
            ->where('vlan_id', $vlan->id)
            ->update(['vlan_id' => null]);

        DB::table('subnetworks')
            ->whereIn('id', $request->input('subnetworks', []))
            ->update(['vlan_id' => $vlan->id]);

        return redirect()->route('admin.vlans.index');
    }

    public function edit(Vlan $vlan)
    {
        abort_if(Gate::denies('edit-object', $vlan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlan->load('subnetworks');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');

        return view('admin.vlans.edit', compact('vlan', 'subnetworks'));
    }

    public function show(Vlan $vlan)
    {
        abort_if(Gate::denies('show-object', $vlan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlan->load('subnetworks');

        return view('admin.vlans.show', compact('vlan'));
    }

    public function destroy(Vlan $vlan)
    {
        abort_if(Gate::denies('vlan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlan->delete();

        return redirect()->route('admin.vlans.index');
    }

    public function massDestroy(MassDestroyVlanRequest $request)
    {
        Vlan::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
