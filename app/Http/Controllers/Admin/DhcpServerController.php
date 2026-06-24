<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyDhcpServerRequest;
use App\Http\Requests\StoreDhcpServerRequest;
use App\Http\Requests\UpdateDhcpServerRequest;
use App\Models\Cartographer;
use App\Models\DhcpServer;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class DhcpServerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('dhcp_server_access') ? null : Cartographer::allowedIdsFor($user, DhcpServer::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $dhcpServers = DhcpServer::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (DhcpServer::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.dhcpServers.index', compact('dhcpServers'));
    }

    public function create()
    {
        abort_if(Gate::denies('dhcp_server_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.dhcpServers.create');
    }

    public function store(StoreDhcpServerRequest $request)
    {
        DhcpServer::create($request->all());

        return redirect()->route('admin.dhcp-servers.index');
    }

    public function edit(DhcpServer $dhcpServer)
    {
        abort_if(Gate::denies('edit-object', $dhcpServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.dhcpServers.edit', compact('dhcpServer'));
    }

    public function update(UpdateDhcpServerRequest $request, DhcpServer $dhcpServer)
    {
        abort_if(Gate::denies('edit-object', $dhcpServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dhcpServer->update($request->all());

        return redirect()->route('admin.dhcp-servers.index');
    }

    public function show(DhcpServer $dhcpServer)
    {
        abort_if(Gate::denies('show-object', $dhcpServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.dhcpServers.show', compact('dhcpServer'));
    }

    public function destroy(DhcpServer $dhcpServer)
    {
        abort_if(Gate::denies('dhcp_server_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dhcpServer->delete();

        return redirect()->route('admin.dhcp-servers.index');
    }

    public function massDestroy(MassDestroyDhcpServerRequest $request)
    {
        DhcpServer::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
