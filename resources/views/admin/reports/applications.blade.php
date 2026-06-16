@extends('layouts.admin')

@section('title')
    {{ trans("cruds.menu.application.title") }}
@endsection

@section('content')
<div class="graph-card-sticky">
    <div class="card mb-3">
        <div class="card-header">
            {{ trans("cruds.menu.application.title") }}
        </div>
        <form action="/admin/report/applications">

            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-sm-5">
                    <table class="table table-bordered table-striped table-hover table-select">
                        <tr>
                            <td style="min-width: 280px;">
                                {{ trans("cruds.applicationBlock.title") }} :
                                <select name="applicationBlock" id="applicationBlock"
                                        class="form-control select2"
                                        onchange="this.form.application.value='';this.form.submit()">
                                    <option value="">-- All --</option>
                                    @foreach ($all_applicationBlocks as $applicationBlock)
                                        <option value="{{$applicationBlock->id}}" {{ Session::get('applicationBlock')==$applicationBlock->id ? "selected" : "" }}>{{ $applicationBlock->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="min-width: 280px;">
                                {{ trans("cruds.application.title") }} :
                                <select name="application" id="application" class="form-control select2"
                                        onchange="this.form.submit()">
                                    <option value="">-- All --</option>
                                    @if ($all_applications!=null)
                                        @foreach ($all_applications as $application)
                                            <option value="{{$application->id}}" {{ Session::get('application')==$application->id ? "selected" : "" }}>{{ $application->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="graph-container">
                    <div class="graphviz" id="graph"></div>
                    <div class="graph-resize-handle"></div>
                </div>
                <div class="row p-1">
                    <div class="col-4">

                        @php($engines=["dot", "fdp",  "osage", "circo" ])
                        @php($engine = request()->get('engine', 'dot'))

                        <label class="inline-flex items-center ps-1 pe-1">
                            <a href="#" id="downloadSvg"><i class="bi bi-download"></i></a>
                        </label>

                        <label class="inline-flex items-center">
                            Rendu :
                        </label>
                        @foreach($engines as $value)
                            <label class="inline-flex items-center ps-1">
                                <input
                                        type="radio"
                                        name="engine"
                                        value="{{ $value }}"
                                        @checked($engine === $value)
                                        onchange="this.form.submit();"
                                >
                                <span>{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="report-scroll-area">
@canAccess(App\Models\ApplicationBlock::class)
    @if ($applicationBlocks->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.applicationBlock.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.applicationBlock.description") }}</p>
                @foreach($applicationBlocks as $applicationBlock)
                    <div class="row">
                        <div class="col">
                            @include('admin.applicationBlocks._details', [
                                'applicationBlock' => $applicationBlock,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\Application::class)
    @if ($applications->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.application.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.application.description") }}</p>
                @foreach($applications as $application)
                    <div class="row">
                        <div class="col">
                            @include('admin.applications._details', [
                                'application' => $application,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\ApplicationService::class)
    @if ($applicationServices->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.applicationService.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.applicationService.description") }}</p>
                @foreach($applicationServices as $applicationService)
                    <div class="row">
                        <div class="col">
                            @include('admin.applicationServices._details', [
                                'applicationService' => $applicationService,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\ApplicationModule::class)
    @if ($applicationModules->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.applicationModule.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.applicationModule.description") }}</p>
                @foreach($applicationModules as $applicationModule)
                    <div class="row">
                        <div class="col">
                            @include('admin.applicationModules._details', [
                                'applicationModule' => $applicationModule,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\Database::class)
    @if ($databases->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.database.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.database.description") }}</p>
                @foreach($databases as $database)
                    <div class="row">
                        <div class="col">
                            @include('admin.databases._details', [
                                'database' => $database,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\ApplicationFlow::class)
    @if ($flows->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.flux.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.flux.description") }}</p>
                @foreach($flows as $flow)
                    <div class="row">
                        <div class="col">

                            @include('admin.application-flows._details', [
                                'flux' => $flow,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endcan
    </div>
@endsection

@section('scripts')
@vite(['resources/js/graphviz.js'])
<script>
const dotSrc = `digraph  {

@foreach($applicationBlocks as $ab)
AB{{ $ab->id }} [label="{{ $ab->name }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/applicationblock.png" href="#{{$ab->getUID()}}"]
@endforeach

@foreach($applications as $application)
A{{ $application->id }} [label="{{ $application->name }}" shape=none labelloc="b"  width=1 height=1.1 image="{{ $application->icon_id === null ? '/images/application.png' : route('admin.documents.show', $application->icon_id) }}" href="#{{$application->getUID()}}"]

@foreach($application->services as $service)
    @if($applicationServices->contains('id', $service->id))
    A{{ $application->id }} -> AS{{ $service->id}}
    @endif
@endforeach

@foreach($application->databases as $database)
    @if($databases->contains('id', $database->id))
    A{{ $application->id }} -> DB{{ $database->id}}
    @endif
@endforeach

@if ($application->application_block_id!=null && $applicationBlocks->contains('id', $application->application_block_id))
    AB{{ $application->application_block_id }} -> A{{ $application->id}}
@endif

@endforeach

@foreach($applicationServices as $service)
AS{{ $service->id }} [label="{{ $service->name }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/applicationservice.png" href="#{{$service->getUID()}}"]
@foreach($service->modules as $module)
    @if($applicationModules->contains('id', $module->id))
    AS{{ $service->id }} -> M{{$module->id}}
    @endif
@endforeach
@endforeach

@foreach($applicationModules as $module)
M{{ $module->id }} [label="{{ $module->name }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/applicationmodule.png" href="#{{$module->getUID()}}"]
@endforeach

@foreach($databases as $database)
DB{{ $database->id }} [label="{{ $database->name }}" shape=none labelloc="b"  width=1 height=1.1 image="{{ $database->icon_id === null ? '/images/database.png' : route('admin.documents.show', $database->icon_id) }}" href="#{{$database->getUID()}}"]
@endforeach
}`;

document.addEventListener('graphvizReady', () => {
    document.getElementById("graph").innerHTML = window.graphviz.layout(
        dotSrc,
        "svg",
        "{{ $engine }}",
        {
            images: [
                { path: "/images/applicationblock.png",   width: "64px", height: "64px" },
                { path: "/images/application.png",        width: "64px", height: "64px" },
                { path: "/images/applicationservice.png", width: "64px", height: "64px" },
                { path: "/images/applicationmodule.png",  width: "64px", height: "64px" },
                { path: "/images/database.png",           width: "64px", height: "64px" },
                @canAccess(App\Models\Application::class)
                @foreach($applications as $application)
                @if ($application->icon_id !== null)
                { path: "{{ route('admin.documents.show', $application->icon_id) }}", width: "64px", height: "64px" },
                @endif
                @endforeach
                @endcan
                @canAccess(App\Models\Database::class)
                @foreach($databases as $database)
                @if ($database->icon_id !== null)
                { path: "{{ route('admin.documents.show', $database->icon_id) }}", width: "64px", height: "64px" },
                @endif
                @endforeach
                @endcan
            ]
        }
    );
});
</script>
@endsection