<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyNetworkRequest;
use App\Http\Requests\StoreNetworkRequest;
use App\Http\Requests\UpdateNetworkRequest;
use App\Models\Cartographer;
use App\Models\Network;
use App\Models\Subnetwork;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class NetworkController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('network_access') ? null : Cartographer::allowedIdsFor($user, Network::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $networks = Network::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Network::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.networks.index', compact('networks'));
    }

    public function create()
    {
        abort_if(Gate::denies('network_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');
        $type_list = Network::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.networks.create', compact('subnetworks', 'type_list', 'attributes_list'));
    }

    public function store(StoreNetworkRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        Network::create($request->all());
        // $network->subnetworks()->sync($request->input('subnetworks', []));

        return redirect()->route('admin.networks.index');
    }

    public function edit(Network $network)
    {
        abort_if(Gate::denies('edit-object', $network), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');

        $network->load('subnetworks');
        $type_list = Network::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.networks.edit', compact('subnetworks', 'network', 'type_list', 'attributes_list'));
    }

    public function update(UpdateNetworkRequest $request, Network $network)
    {
        abort_if(Gate::denies('edit-object', $network), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $network->update($request->all());

        // Subnetwork::where('network_id', $network->id)->update(['network_id' => null]);
        // Subnetwork::whereIn('id', $request->input('subnetworks', []))->update(['network_id' => $network->id]);

        return redirect()->route('admin.networks.index');
    }

    public function show(Network $network)
    {
        abort_if(Gate::denies('show-object', $network), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $network->load('subnetworks');

        return view('admin.networks.show', compact('network'));
    }

    public function destroy(Network $network)
    {
        abort_if(Gate::denies('network_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $network->delete();

        return redirect()->route('admin.networks.index');
    }

    public function massDestroy(MassDestroyNetworkRequest $request)
    {
        Network::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = Network::query()
            ->select('attributes')
            ->where('attributes', '<>', null)
            ->pluck('attributes');
        $res = [];
        foreach ($attributes_list as $i) {
            foreach (explode(' ', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        sort($res);

        return array_unique($res);
    }
}
