<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyGatewayRequest;
use App\Http\Requests\StoreGatewayRequest;
use App\Http\Requests\UpdateGatewayRequest;
use App\Models\Cartographer;
use App\Models\Gateway;
use App\Models\Subnetwork;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class GatewayController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('gateway_access') ? null : Cartographer::allowedIdsFor($user, Gateway::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $gateways = Gateway::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Gateway::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.gateways.index', compact('gateways'));
    }

    public function create()
    {
        abort_if(Gate::denies('gateway_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');

        return view('admin.gateways.create', compact('subnetworks'));
    }

    public function store(StoreGatewayRequest $request)
    {
        $gateway = Gateway::create($request->all());

        Subnetwork::whereIn('id', $request->input('subnetworks', []))
            ->update(['gateway_id' => $gateway->id]);

        return redirect()->route('admin.gateways.index');
    }

    public function edit(Gateway $gateway)
    {
        abort_if(Gate::denies('edit-object', $gateway), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');

        return view('admin.gateways.edit', compact('gateway', 'subnetworks'));
    }

    public function update(UpdateGatewayRequest $request, Gateway $gateway)
    {
        abort_if(Gate::denies('edit-object', $gateway), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $gateway->update($request->all());

        Subnetwork::where('gateway_id', $gateway->id)
            ->update(['gateway_id' => null]);

        Subnetwork::whereIn('id', $request->input('subnetworks', []))
            ->update(['gateway_id' => $gateway->id]);

        return redirect()->route('admin.gateways.index');
    }

    public function show(Gateway $gateway)
    {
        abort_if(Gate::denies('show-object', $gateway), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $gateway->load('subnetworks');

        return view('admin.gateways.show', compact('gateway'));
    }

    public function destroy(Gateway $gateway)
    {
        abort_if(Gate::denies('gateway_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $gateway->delete();

        return redirect()->route('admin.gateways.index');
    }

    public function massDestroy(MassDestroyGatewayRequest $request)
    {
        Gateway::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
