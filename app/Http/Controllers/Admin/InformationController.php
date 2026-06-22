<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyInformationRequest;
use App\Http\Requests\StoreInformationRequest;
use App\Http\Requests\UpdateInformationRequest;
use Gate;
use App\Models\Information;
use App\Models\Process;
use Symfony\Component\HttpFoundation\Response;

class InformationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('information_access') ? null : \App\Models\Cartographer::allowedIdsFor($user, \App\Models\Information::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $information = Information::query()
            ->with('parents','children')
            
        ->when(request('search'), function ($q, $search) {
            $q->where(function ($q) use ($search) {
                foreach (Information::$searchable as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        })
        ->orderBy('name')
        
        ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.information.index', compact('information'));
    }

    public function create()
    {
        abort_if(Gate::denies('information_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $processes = Process::all()->sortBy('name')->pluck('name', 'id');
        $information_list = Information::all()->sortBy('name')->pluck('name', 'id');

        // lists
        $owner_list = Information::select('owner')->where('owner', '<>', null)->distinct()->orderBy('owner')->pluck('owner');
        $storage_list = Information::select('storage')->where('storage', '<>', null)->distinct()->orderBy('storage')->pluck('storage');
        $sensitivity_list = Information::select('sensitivity')->where('sensitivity', '<>', null)->distinct()->orderBy('sensitivity')->pluck('sensitivity');
        $administrator_list = Information::select('administrator')->where('administrator', '<>', null)->distinct()->orderBy('administrator')->pluck('administrator');
        $type_list = Information::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.information.create', compact(
            'processes',
            'owner_list',
            'storage_list',
            'sensitivity_list',
            'administrator_list',
            'information_list',
            'type_list',
            'attributes_list'
        ));
    }

    public function store(StoreInformationRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $information = Information::create($request->all());

        $information->processes()->sync($request->input('processes', []));
        $information->parents()->sync($request->input('parents', []));
        $information->children()->sync($request->input('children', []));

        return redirect()->route('admin.information.index');
    }

    public function edit(Information $information)
    {
        abort_if(Gate::denies('edit-object', $information), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information->load('processes');

        // links
        $processes = Process::all()->sortBy('name')->pluck('name', 'id');
        $information_list = Information::all()->sortBy('name')->pluck('name', 'id');

        // lists
        $owner_list = Information::select('owner')->where('owner', '<>', null)->distinct()->orderBy('owner')->pluck('owner');
        $storage_list = Information::select('storage')->where('storage', '<>', null)->distinct()->orderBy('storage')->pluck('storage');
        $sensitivity_list = Information::select('sensitivity')->where('sensitivity', '<>', null)->distinct()->orderBy('sensitivity')->pluck('sensitivity');
        $administrator_list = Information::select('administrator')->where('administrator', '<>', null)->distinct()->orderBy('administrator')->pluck('administrator');
        $type_list = Information::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.information.edit', compact(
            'processes',
            'information',
            'owner_list',
            'storage_list',
            'sensitivity_list',
            'administrator_list',
            'information_list',
            'type_list',
            'attributes_list'
        ));
    }

    public function update(UpdateInformationRequest $request, Information $information)
    {
        abort_if(Gate::denies('edit-object', $information), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $information->update($request->all());

        $information->processes()->sync($request->input('processes', []));
        $information->parents()->sync($request->input('parents', []));
        $information->children()->sync($request->input('children', []));

        return redirect()->route('admin.information.index');
    }

    public function show(Information $information)
    {
        abort_if(Gate::denies('show-object', $information), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information->load('processes', 'parents', 'children');

        return view('admin.information.show', compact('information'));
    }

    public function destroy(Information $information)
    {
        abort_if(Gate::denies('information_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information->delete();

        return redirect()->route('admin.information.index');
    }

    public function massDestroy(MassDestroyInformationRequest $request)
    {
        Information::query()->whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = Information::query()
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
