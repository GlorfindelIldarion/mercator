<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyLogicalServerRequest;
use App\Http\Requests\StoreLogicalServerRequest;
use App\Http\Requests\UpdateLogicalServerRequest;
use App\Models\Application;
use App\Models\Backup;
use App\Models\Cartographer;
use App\Models\Cluster;
use App\Models\Database;
use App\Models\Domain;
use App\Models\LogicalServer;
use App\Models\PhysicalServer;
use App\Services\IconUploadService;
use Gate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\DataTables;

class LogicalServerController extends Controller
{
    public function __construct(private readonly IconUploadService $iconUploadService) {}

    public function getData(Request $request)
    {
        $logicalServers = LogicalServer::query()
            ->select('id', 'name', 'type', 'attributes', 'description')
            ->get();

        return DataTables::of($logicalServers)->make(true);
    }

    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('logical_server_access') ? null : Cartographer::allowedIdsFor($user, LogicalServer::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $logicalServers = LogicalServer::with('applications:id,name', 'physicalServers:id,name', 'clusters:id,name')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (LogicalServer::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.logicalServers.index', compact('logicalServers'));
    }

    public function create()
    {
        abort_if(Gate::denies('logical_server_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalServers = PhysicalServer::query()->orderBy('name')->pluck('name', 'id');
        $databases = Database::query()->orderBy('name')->pluck('name', 'id');
        $applications = Application::query()->orderBy('name')->pluck('name', 'id');
        $clusters = Cluster::query()->orderBy('name')->pluck('name', 'id');
        $domains = Domain::query()->orderBy('name')->pluck('name', 'id');
        $icons = LogicalServer::query()->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');
        $backups = Backup::query()->orderBy('name')->pluck('name', 'id');

        // Lists
        $type_list = LogicalServer::query()->select('type')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $operating_system_list = LogicalServer::query()->select('operating_system')->whereNotNull('operating_system')->distinct()->orderBy('operating_system')->pluck('operating_system');
        $environment_list = LogicalServer::query()->select('environment')->whereNotNull('environment')->distinct()->orderBy('environment')->pluck('environment');
        $attributes_list = $this->getAttributes();

        // default active
        $active = true;

        return view(
            'admin.logicalServers.create',
            compact(
                'domains',
                'clusters',
                'icons',
                'physicalServers',
                'backups',
                'applications',
                'databases',
                'type_list',
                'environment_list',
                'operating_system_list',
                'attributes_list',
                'active'
            )
        );
    }

    public function store(StoreLogicalServerRequest $request)
    {
        $request['active'] = $request->has('active');
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $logicalServer = LogicalServer::create($request->all());

        // Save icon
        $this->iconUploadService->handle($request, $logicalServer);

        // Save LogicalServer
        $logicalServer->save();

        // Relations
        $logicalServer->physicalServers()->sync($request->input('physicalServers', []));
        $logicalServer->applications()->sync($request->input('applications', []));
        $logicalServer->databases()->sync($request->input('databases', []));
        $logicalServer->clusters()->sync($request->input('clusters', []));

        // Backups
        $logicalServer->backups()->sync($request->input('backup_ids', []));

        return redirect()->route('admin.logical-servers.index');
    }

    public function edit(LogicalServer $logicalServer)
    {
        abort_if(Gate::denies('edit-object', $logicalServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalServers = PhysicalServer::query()->orderBy('name')->pluck('name', 'id');
        $databases = Database::query()->orderBy('name')->pluck('name', 'id');
        $applications = Application::query()->orderBy('name')->pluck('name', 'id');
        $clusters = Cluster::query()->orderBy('name')->pluck('name', 'id');
        $domains = Domain::query()->orderBy('name')->pluck('name', 'id');
        $icons = LogicalServer::query()->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');
        $backups = Backup::query()->orderBy('name')->pluck('name', 'id');

        // Lists
        $type_list = LogicalServer::query()->select('type')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $operating_system_list = LogicalServer::query()->select('operating_system')->where('operating_system', '<>', null)->distinct()->orderBy('operating_system')->pluck('operating_system');
        $environment_list = LogicalServer::query()->select('environment')->where('environment', '<>', null)->distinct()->orderBy('environment')->pluck('environment');
        $attributes_list = $this->getAttributes();

        $logicalServer->load('physicalServers', 'applications', 'backups');

        return view(
            'admin.logicalServers.edit',
            compact(
                'domains',
                'icons',
                'clusters',
                'physicalServers',
                'backups',
                'applications',
                'databases',
                'type_list',
                'operating_system_list',
                'environment_list',
                'attributes_list',
                'logicalServer'
            )
        );
    }

    public function update(UpdateLogicalServerRequest $request, LogicalServer $logicalServer)
    {
        abort_if(Gate::denies('edit-object', $logicalServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);
        $request['active'] = $request->has('active');

        // Save icon
        $this->iconUploadService->handle($request, $logicalServer);

        // Save LogicalServer
        $logicalServer->update($request->all());

        // Relations
        $logicalServer->physicalServers()->sync($request->input('physicalServers', []));
        $logicalServer->applications()->sync($request->input('applications', []));
        $logicalServer->databases()->sync($request->input('databases', []));
        $logicalServer->clusters()->sync($request->input('clusters', []));

        // Backups
        $logicalServer->backups()->sync($request->input('backup_ids', []));

        return redirect()->route('admin.logical-servers.index');
    }

    public function show(LogicalServer $logicalServer)
    {
        abort_if(Gate::denies('show-object', $logicalServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalServer->load('physicalServers', 'applications', 'backups.storageDevices');

        $logicalServer->setRelation(
            'backups',
            $logicalServer->backups->sortBy(fn ($b) => $b->storageDevices->first()?->name)
        );

        return view('admin.logicalServers.show', compact('logicalServer'));
    }

    public function destroy(LogicalServer $logicalServer)
    {
        abort_if(Gate::denies('logical_server_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalServer->delete();

        return redirect()->route('admin.logical-servers.index');
    }

    public function massDestroy(MassDestroyLogicalServerRequest $request)
    {
        LogicalServer::query()->whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = LogicalServer::select('attributes')
            ->where('attributes', '<>', null)
            ->distinct()
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
