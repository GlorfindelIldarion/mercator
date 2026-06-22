<table class="table table-bordered table-striped">
    <tbody>
    <tr>
        <th width='10%'>
            {{ trans('cruds.logicalFlow.fields.name') }}
        </th>
        <td>
            {{ $logicalFlow->name ?? "NONAME" }}
        </td>
        <th width='10%'>
            {{ trans('cruds.logicalFlow.fields.type') }}
        </th>
        <td>
            {{ $logicalFlow->type }}
        </td>
        <th width='10%'>
            {{ trans('cruds.logicalFlow.fields.attributes') }}
        </th>
        <td colspan="2">
            @foreach(explode(" ", $logicalFlow->attributes) as $attribute)
                <span class="badge badge-info">{{ $attribute }}</span>
            @endforeach
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.logicalFlow.fields.description') }}
        </th>
        <td colspan='6'>
            {!! $logicalFlow->description !!}
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.logicalFlow.fields.chain') }}
        </th>
        <th>
            {{ trans('cruds.logicalFlow.fields.interface') }}
        </th>
        <th>
            {{ trans('cruds.logicalFlow.fields.router') }}
        </th>
    </tr>
    @canAccess(App\Models\Router::class)
    <tr>
        <td>
            {{ $logicalFlow->chain }}
        </td>
        <td>
            {{ $logicalFlow->interface }}
        </td>
        <td>
            @if ($logicalFlow->router !== null)
                @canShow($logicalFlow->router)
                <a href="{{ route('admin.routers.show', $logicalFlow->router->id) }}">
                    {{ $logicalFlow->router->name }}
                </a>
                @elsecanShow
                    {{ $logicalFlow->router->name }}
                @endcanShow
            @endif
        </td>

    </tr>
    @endcanAccess
    <tr>
        <th width='10%'>
            {{ trans('cruds.logicalFlow.fields.priority') }}
        </th>
        <th width='20%'>
            {{ trans('cruds.logicalFlow.fields.action') }}
        </th>
        <th width='10%'>
            {{ trans('cruds.logicalFlow.fields.protocol') }}
        </th>
        <th width='20%'>
            {{ trans('cruds.logicalFlow.fields.source_ip_range') }}
        </th>
        <th width='10%'>
            {{ trans('cruds.logicalFlow.fields.source_port') }}
        </th>
        <th width='20%'>
            {{ trans('cruds.logicalFlow.fields.dest_ip_range') }}
        </th>
        <th width='10%'>
            {{ trans('cruds.logicalFlow.fields.dest_port') }}
        </th>
    </tr>

    <tr>
        <td>
            {{ $logicalFlow->priority }}
        </td>
        <td>
            {{ $logicalFlow->action }}
        </td>
        <td>
            {{ $logicalFlow->protocol }}
        </td>
        <td>
            @if ($logicalFlow->source_ip_range!==null)
                {{ $logicalFlow->source_ip_range }}
            @elseif ($logicalFlow->logicalServerSource!==null)
                {{ $logicalFlow->logicalServerSource->address_ip }}
                (@canShow($logicalFlow->logicalServerSource)<a href="{{ route('admin.logical-servers.show',$logicalFlow->logicalServerSource->id) }}">
                    {{ $logicalFlow->logicalServerSource->name }}
                </a>@elsecanShow{{ $logicalFlow->logicalServerSource->name }}@endcanShow)
            @elseif ($logicalFlow->peripheralSource!==null)
                {{ $logicalFlow->peripheralSource->address_ip }}
                (@canShow($logicalFlow->peripheralSource)<a href="{{ route('admin.peripherals.show',$logicalFlow->peripheralSource->id) }}">
                    {{ $logicalFlow->peripheralSource->name }}
                </a>@elsecanShow{{ $logicalFlow->peripheralSource->name }}@endcanShow)
            @elseif ($logicalFlow->physicalServerSource!==null)
                {{ $logicalFlow->physicalServerSource->address_ip }}
                (@canShow($logicalFlow->physicalServerSource)<a href="{{ route('admin.physical-servers.show',$logicalFlow->physicalServerSource->id) }}">
                    {{ $logicalFlow->physicalServerSource->name }}
                </a>@elsecanShow{{ $logicalFlow->physicalServerSource->name }}@endcanShow)
            @elseif ($logicalFlow->storageDeviceSource!==null)
                {{ $logicalFlow->storageDeviceSource->address_ip }}
                (@canShow($logicalFlow->storageDeviceSource)<a href="{{ route('admin.storage-devices.show',$logicalFlow->storageDeviceSource->id) }}">
                    {{ $logicalFlow->storageDeviceSource->name }}
                </a>@elsecanShow{{ $logicalFlow->storageDeviceSource->name }}@endcanShow)
            @elseif ($logicalFlow->workstationSource!==null)
                {{ $logicalFlow->workstationSource->address_ip }}
                (@canShow($logicalFlow->workstationSource)<a href="{{ route('admin.workstations.show',$logicalFlow->workstationSource->id) }}">
                    {{ $logicalFlow->workstationSource->name }}
                </a>@elsecanShow{{ $logicalFlow->workstationSource->name }}@endcanShow)
            @elseif ($logicalFlow->physicalSecurityDeviceSource!==null)
                {{ $logicalFlow->physicalSecurityDeviceSource->address_ip }}
                (@canShow($logicalFlow->physicalSecurityDeviceSource)<a href="{{ route('admin.physical-security-devices.show',$logicalFlow->physicalSecurityDeviceSource->id) }}">
                    {{ $logicalFlow->physicalSecurityDeviceSource->name }}
                </a>@elsecanShow{{ $logicalFlow->physicalSecurityDeviceSource->name }}@endcanShow)
            @elseif ($logicalFlow->securityDeviceSource!==null)
                {{ $logicalFlow->securityDeviceSource->address_ip }}
                (@canShow($logicalFlow->securityDeviceSource)<a href="{{ route('admin.security-devices.show',$logicalFlow->securityDeviceSource->id) }}">
                    {{ $logicalFlow->securityDeviceSource->name }}
                </a>@elsecanShow{{ $logicalFlow->securityDeviceSource->name }}@endcanShow)
            @elseif ($logicalFlow->subnetworkSource!==null)
                {{ $logicalFlow->subnetworkSource->address }}
                (@canShow($logicalFlow->subnetworkSource)<a href="{{ route('admin.subnetworks.show',$logicalFlow->subnetworkSource->id) }}">
                    {{ $logicalFlow->subnetworkSource->name }}
                </a>@elsecanShow{{ $logicalFlow->subnetworkSource->name }}@endcanShow)
            @elseif ($logicalFlow->clusterSource!==null)
                {{ $logicalFlow->clusterSource->address }}
                (@canShow($logicalFlow->clusterSource)<a href="{{ route('admin.clusters.show',$logicalFlow->clusterSource->id) }}">
                    {{ $logicalFlow->clusterSource->name }}
                </a>@elsecanShow{{ $logicalFlow->clusterSource->name }}@endcanShow)
            @endif
        </td>
        <td>
            {{ $logicalFlow->source_port ?? "ANY "}}
        </td>
        <td>
            @if ($logicalFlow->dest_ip_range!==null)
                {{ $logicalFlow->dest_ip_range }}
            @elseif ($logicalFlow->logicalServerDest!==null)
                {{ $logicalFlow->logicalServerDest->address_ip }}
                (@canShow($logicalFlow->logicalServerDest)<a href="{{ route('admin.logical-servers.show',$logicalFlow->logicalServerDest->id) }}">
                    {{ $logicalFlow->logicalServerDest->name }}
                </a>@elsecanShow{{ $logicalFlow->logicalServerDest->name }}@endcanShow)
            @elseif ($logicalFlow->peripheralDest!==null)
                {{ $logicalFlow->peripheralDest->address_ip }}
                (@canShow($logicalFlow->peripheralDest)<a href="{{ route('admin.peripherals.show',$logicalFlow->peripheralDest->id) }}">
                    {{ $logicalFlow->peripheralDest->name }}
                </a>@elsecanShow{{ $logicalFlow->peripheralDest->name }}@endcanShow)
            @elseif ($logicalFlow->physicalServerDest!==null)
                {{ $logicalFlow->physicalServerDest->address_ip }}
                (@canShow($logicalFlow->physicalServerDest)<a href="{{ route('admin.physical-servers.show',$logicalFlow->physicalServerDest->id) }}">
                    {{ $logicalFlow->physicalServerDest->name }}
                </a>@elsecanShow{{ $logicalFlow->physicalServerDest->name }}@endcanShow)
            @elseif ($logicalFlow->storageDeviceDest!==null)
                {{ $logicalFlow->storageDeviceDest->address_ip }}
                (@canShow($logicalFlow->storageDeviceDest)<a href="{{ route('admin.storage-devices.show',$logicalFlow->storageDeviceDest->id) }}">
                    {{ $logicalFlow->storageDeviceDest->name }}
                </a>@elsecanShow{{ $logicalFlow->storageDeviceDest->name }}@endcanShow)
            @elseif ($logicalFlow->workstationDest!==null)
                {{ $logicalFlow->workstationDest->address_ip }}
                (@canShow($logicalFlow->workstationDest)<a href="{{ route('admin.workstations.show',$logicalFlow->workstationDest->id) }}">
                    {{ $logicalFlow->workstationDest->name }}
                </a>@elsecanShow{{ $logicalFlow->workstationDest->name }}@endcanShow)
            @elseif ($logicalFlow->physicalSecurityDeviceDest!==null)
                {{ $logicalFlow->physicalSecurityDeviceDest->address_ip }}
                (@canShow($logicalFlow->physicalSecurityDeviceDest)<a href="{{ route('admin.physical-security-devices.show',$logicalFlow->physicalSecurityDeviceDest->id) }}">
                    {{ $logicalFlow->physicalSecurityDeviceDest->name }}
                </a>@elsecanShow{{ $logicalFlow->physicalSecurityDeviceDest->name }}@endcanShow)
            @elseif ($logicalFlow->securityDeviceDest!==null)
                {{ $logicalFlow->securityDeviceDest->address_ip }}
                (@canShow($logicalFlow->securityDeviceDest)<a href="{{ route('admin.security-devices.show',$logicalFlow->securityDeviceDest->id) }}">
                    {{ $logicalFlow->securityDeviceDest->name }}
                </a>@elsecanShow{{ $logicalFlow->securityDeviceDest->name }}@endcanShow)
            @elseif ($logicalFlow->subnetworkDest!==null)
                {{ $logicalFlow->subnetworkDest->address }}
                (@canShow($logicalFlow->subnetworkDest)<a href="{{ route('admin.subnetworks.show',$logicalFlow->subnetworkDest->id) }}">
                    {{ $logicalFlow->subnetworkDest->name }}
                </a>@elsecanShow{{ $logicalFlow->subnetworkDest->name }}@endcanShow)
            @elseif ($logicalFlow->clusterDest!==null)
                {{ $logicalFlow->clusterDest->address }}
                (@canShow($logicalFlow->clusterDest)<a href="{{ route('admin.clusters.show',$logicalFlow->clusterDest->id) }}">
                    {{ $logicalFlow->clusterDest->name }}
                </a>@elsecanShow{{ $logicalFlow->clusterDest->name }}@endcanShow)
            @endif
        </td>
        <td>
            {{ $logicalFlow->dest_port ?? "ANY" }}
        </td>
    </tr>
    <tr>
        <th>
            {{ trans('cruds.logicalFlow.fields.users') }}
        </th>
        <td colspan='2'>
            {{ $logicalFlow->users }}
        </td>
        <th>
            {{ trans('cruds.logicalFlow.fields.schedule') }}
        </th>
        <td colspan='3'>
            {{ $logicalFlow->schedule }}
        </td>
    </tr>
    </tbody>
</table>
