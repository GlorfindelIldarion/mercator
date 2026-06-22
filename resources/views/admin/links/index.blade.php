@extends('layouts.admin')

@section('title')
    {{ trans('cruds.physicalLink.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('physical_link_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.links.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.physicalLink.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.physicalLink.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th width='100'>
                            {{ trans('cruds.physicalLink.fields.type') }}
                        </th>
                        <th width='20'>
                            {{ trans('cruds.physicalLink.fields.color') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalLink.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalLink.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalLink.fields.src') }}
                        </th>
                        <th width='100'>
                            {{ trans('cruds.physicalLink.fields.src_port') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalLink.fields.dest') }}
                        </th>
                        <th width='100'>
                            {{ trans('cruds.physicalLink.fields.dest_port') }}
                        </th>
                        <th width='200'>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($physicalLinks as $key => $physicalLink)
                        <tr data-entry-id="{{ $physicalLink->id }}">
                            <td>

                            </td>
                            <td>
                                <x-show-link :model="$physicalLink" :label="$physicalLink->type ?? ''" />
                            </td>
                            <td>
                                <a href="{{ route('admin.links.show', $physicalLink->id) }}">
                                <div style="width: 40px; height: 40px; background-color: {{ $physicalLink->color }}; border: 1px solid #ccc; border-radius: 4px;"></div>
                                </a>
                            </td>
                            <td>
                                @foreach(explode(' ', $physicalLink->attributes ?? '') as $attribute)
                                    @if(strlen(trim($attribute)) > 0)
                                        <span class="badge badge-info">{{ $attribute }}</span>
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                {!! $physicalLink->description ?? '' !!}
                            </td>
                            <td>
                                @if ($physicalLink->peripheralSrc!=null)
                                    <x-show-link :model="$physicalLink->peripheralSrc" />
                                @elseif ($physicalLink->phoneSrc!=null)
                                    <x-show-link :model="$physicalLink->phoneSrc" />
                                @elseif ($physicalLink->physicalRouterSrc!=null)
                                    <x-show-link :model="$physicalLink->physicalRouterSrc" />
                                @elseif ($physicalLink->physicalSecurityDeviceSrc!=null)
                                    <x-show-link :model="$physicalLink->physicalSecurityDeviceSrc" />
                                @elseif ($physicalLink->physicalServerSrc!=null)
                                    <x-show-link :model="$physicalLink->physicalServerSrc" />
                                @elseif ($physicalLink->physicalSwitchSrc!=null)
                                    <x-show-link :model="$physicalLink->physicalSwitchSrc" />
                                @elseif ($physicalLink->storageDeviceSrc!=null)
                                    <x-show-link :model="$physicalLink->storageDeviceSrc" />
                                @elseif ($physicalLink->wifiTerminalSrc!=null)
                                    <x-show-link :model="$physicalLink->wifiTerminalSrc" />
                                @elseif ($physicalLink->workstationSrc!=null)
                                    <x-show-link :model="$physicalLink->workstationSrc" />
                                @elseif ($physicalLink->routerSrc!=null)
                                    <x-show-link :model="$physicalLink->routerSrc" />
                                @elseif ($physicalLink->networkSwitchSrc!=null)
                                    <x-show-link :model="$physicalLink->networkSwitchSrc" />
                                @elseif ($physicalLink->logicalServerSrc!=null)
                                    <x-show-link :model="$physicalLink->logicalServerSrc" />
                                @endif
                            </td>
                            <td>
                                {{ $physicalLink->src_port }}
                            </td>
                            <td>
                                @if ($physicalLink->peripheralDest!=null)
                                    <x-show-link :model="$physicalLink->peripheralDest" />
                                @elseif ($physicalLink->phoneDest!=null)
                                    <x-show-link :model="$physicalLink->phoneDest" />
                                @elseif ($physicalLink->physicalRouterDest!=null)
                                    <x-show-link :model="$physicalLink->physicalRouterDest" />
                                @elseif ($physicalLink->physicalSecurityDeviceDest!=null)
                                    <x-show-link :model="$physicalLink->physicalSecurityDeviceDest" />
                                @elseif ($physicalLink->physicalServerDest!=null)
                                    <x-show-link :model="$physicalLink->physicalServerDest" />
                                @elseif ($physicalLink->physicalSwitchDest!=null)
                                    <x-show-link :model="$physicalLink->physicalSwitchDest" />
                                @elseif ($physicalLink->storageDeviceDest!=null)
                                    <x-show-link :model="$physicalLink->storageDeviceDest" />
                                @elseif ($physicalLink->wifiTerminalDest!=null)
                                    <x-show-link :model="$physicalLink->wifiTerminalDest" />
                                @elseif ($physicalLink->workstationDest!=null)
                                    <x-show-link :model="$physicalLink->workstationDest" />
                                @elseif ($physicalLink->routerDest!=null)
                                    <x-show-link :model="$physicalLink->routerDest" />
                                @elseif ($physicalLink->networkSwitchDest!=null)
                                    <x-show-link :model="$physicalLink->networkSwitchDest" />
                                @elseif ($physicalLink->logicalServerDest!=null)
                                    <x-show-link :model="$physicalLink->logicalServerDest" />
                                @endif
                            </td>
                            <td>
                                {{ $physicalLink->dest_port }}
                            </td>
                            <td nowrap>
                                @can('physical_link_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.links.show', $physicalLink->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($physicalLink)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.links.edit', $physicalLink->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('physical_link_delete')
                                    <form action="{{ route('admin.links.destroy', $physicalLink->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    
    @include('partials.pagination-footer', ['paginator' => $physicalLinks])
</div>
</div>
@endsection

@section('scripts')
@parent
<script>
@include('partials.datatable', array(
    'id' => '#dataTable',
    'title' => trans("cruds.physicalLink.title_singular"),
    'URL' => route('admin.links.massDestroy'),
    'canDelete' => auth()->user()->can('physical_link_delete') ? true : false,
    'serverSidePagination' => true
));
</script>
@endsection
