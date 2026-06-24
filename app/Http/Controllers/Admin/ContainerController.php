<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyContainerRequest;
use App\Http\Requests\StoreContainerRequest;
use App\Http\Requests\UpdateContainerRequest;
use App\Models\Application;
use App\Models\Cartographer;
use App\Models\Container;
use App\Models\Database;
use App\Models\LogicalServer;
use App\Services\IconUploadService;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class ContainerController extends Controller
{
    public function __construct(private readonly IconUploadService $iconUploadService) {}

    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('container_access') ? null : Cartographer::allowedIdsFor($user, Container::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $containers = Container::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Container::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')

            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.containers.index', compact('containers'));
    }

    public function create()
    {
        abort_if(Gate::denies('container_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get lists
        $icons = Container::select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');
        $type_list = Container::select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $logical_servers = LogicalServer::all()->sortBy('name')->pluck('name', 'id');
        $databases = Database::all()->sortBy('name')->pluck('name', 'id');
        $applications = Application::all()->sortBy('name')->pluck('name', 'id');

        return view('admin.containers.create', compact('icons', 'type_list', 'logical_servers', 'applications', 'databases'));
    }

    public function store(StoreContainerRequest $request)
    {
        // Create container
        $container = Container::create($request->all());

        // Save Relations
        $container->applications()->sync($request->input('applications', []));
        $container->logicalServers()->sync($request->input('logical_servers', []));
        $container->databases()->sync($request->input('databases', []));

        // Save icon
        $this->iconUploadService->handle($request, $container);

        // Save container
        $container->save();

        return redirect()->route('admin.containers.index');
    }

    public function edit(Container $container)
    {
        abort_if(Gate::denies('edit-object', $container), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $icons = Container::select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');
        $type_list = Container::select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $logical_servers = LogicalServer::all()->sortBy('name')->pluck('name', 'id');
        $applications = Application::all()->sortBy('name')->pluck('name', 'id');
        $databases = Database::all()->sortBy('name')->pluck('name', 'id');

        return view(
            'admin.containers.edit',
            compact('container', 'icons', 'type_list', 'logical_servers', 'applications', 'databases')
        );
    }

    public function update(UpdateContainerRequest $request, Container $container)
    {
        abort_if(Gate::denies('edit-object', $container), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Save icon
        $this->iconUploadService->handle($request, $container);

        // Update container
        $container->update($request->all());

        // Save Relations
        $container->applications()->sync($request->input('applications', []));
        $container->logicalServers()->sync($request->input('logical_servers', []));
        $container->databases()->sync($request->input('databases', []));

        return redirect()->route('admin.containers.index');
    }

    public function show(Container $container)
    {
        abort_if(Gate::denies('show-object', $container), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $container->load('applications', 'logicalServers', 'databases');

        return view('admin.containers.show', compact('container'));
    }

    public function destroy(Container $container)
    {
        abort_if(Gate::denies('container_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $container->delete();

        return redirect()->route('admin.containers.index');
    }

    public function massDestroy(MassDestroyContainerRequest $request)
    {
        Container::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
