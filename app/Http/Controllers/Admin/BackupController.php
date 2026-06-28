<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyBackupRequest;
use App\Http\Requests\StoreBackupRequest;
use App\Http\Requests\UpdateBackupRequest;
use App\Models\Backup;
use App\Models\Cartographer;
use App\Models\LogicalServer;
use App\Models\StorageDevice;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class BackupController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('backup_access') ? null : Cartographer::allowedIdsFor($user, Backup::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $backups = Backup::with('logicalServers', 'storageDevices')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Backup::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.backups.index', compact('backups'));
    }

    public function create()
    {
        abort_if(Gate::denies('backup_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalServers = LogicalServer::query()->orderBy('name')->pluck('name', 'id');
        $storageDevices = StorageDevice::query()->orderBy('name')->pluck('name', 'id');
        $type_list = Backup::query()->select('type')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.backups.create',
            compact(
                'logicalServers',
                'storageDevices',
                'type_list',
                'attributes_list'));
    }

    public function store(StoreBackupRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $backup = Backup::query()->create($request->all());

        $backup->logicalServers()->sync($request->input('logical_server_ids', []));
        $backup->storageDevices()->sync($request->input('storage_device_ids', []));

        return redirect()->route('admin.backups.index');
    }

    public function edit(Backup $backup)
    {
        abort_if(Gate::denies('edit-object', $backup), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalServers = LogicalServer::query()->orderBy('name')->pluck('name', 'id');
        $storageDevices = StorageDevice::query()->orderBy('name')->pluck('name', 'id');
        $type_list = Backup::query()->select('type')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        $backup->load('logicalServers', 'storageDevices');

        return view('admin.backups.edit',
            compact(
                'backup',
                'logicalServers',
                'storageDevices',
                'type_list',
                'attributes_list'));
    }

    public function update(UpdateBackupRequest $request, Backup $backup)
    {
        abort_if(Gate::denies('edit-object', $backup), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $backup->update($request->all());

        $backup->logicalServers()->sync($request->input('logical_server_ids', []));
        $backup->storageDevices()->sync($request->input('storage_device_ids', []));

        return redirect()->route('admin.backups.index');
    }

    public function show(Backup $backup)
    {
        abort_if(Gate::denies('show-object', $backup), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $backup->load('logicalServers', 'storageDevices');

        return view('admin.backups.show', compact('backup'));
    }

    public function destroy(Backup $backup)
    {
        abort_if(Gate::denies('backup_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $backup->delete();

        return redirect()->route('admin.backups.index');
    }

    public function massDestroy(MassDestroyBackupRequest $request)
    {
        Backup::query()->whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = Backup::query()
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
