<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyDnsserverRequest;
use App\Http\Requests\StoreDnsserverRequest;
use App\Http\Requests\UpdateDnsserverRequest;
use App\Models\Cartographer;
use App\Models\Dnsserver;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class DnsserverController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('dnsserver_access') ? null : Cartographer::allowedIdsFor($user, Dnsserver::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $dnsservers = Dnsserver::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Dnsserver::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.dnsservers.index', compact('dnsservers'));
    }

    public function create()
    {
        abort_if(Gate::denies('dnsserver_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.dnsservers.create');
    }

    public function store(StoreDnsserverRequest $request)
    {
        Dnsserver::create($request->all());

        return redirect()->route('admin.dnsservers.index');
    }

    public function edit(Dnsserver $dnsserver)
    {
        abort_if(Gate::denies('edit-object', $dnsserver), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.dnsservers.edit', compact('dnsserver'));
    }

    public function update(UpdateDnsserverRequest $request, Dnsserver $dnsserver)
    {
        abort_if(Gate::denies('edit-object', $dnsserver), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dnsserver->update($request->all());

        return redirect()->route('admin.dnsservers.index');
    }

    public function show(Dnsserver $dnsserver)
    {
        abort_if(Gate::denies('show-object', $dnsserver), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.dnsservers.show', compact('dnsserver'));
    }

    public function destroy(Dnsserver $dnsserver)
    {
        abort_if(Gate::denies('dnsserver_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dnsserver->delete();

        return redirect()->route('admin.dnsservers.index');
    }

    public function massDestroy(MassDestroyDnsserverRequest $request)
    {
        Dnsserver::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
