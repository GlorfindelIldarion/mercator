<table class="table table-bordered table-striped">
    <tbody>
        <tr>
            <th width="10%">
            {{ trans('cruds.physicalLink.fields.type') }}
            </th>
            <td valign="middle" width="20%">
            {{ $link->type }}
            </td>
            <td>
                <div style="width: 40px; height: 40px; background-color: {{ $link->color }}; border: 1px solid #ccc; border-radius: 4px;"></div>
            </td>
            <th width="10%">
                {{ trans('cruds.physicalLink.fields.attributes') }}
            </th>
            <td colspan="4">
                @foreach(explode(' ', $link->attributes ?? '') as $attribute)
                    @if(strlen(trim($attribute)) > 0)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
        @canAccessAny(App\Models\Peripheral::class, App\Models\Phone::class, App\Models\PhysicalRouter::class, App\Models\PhysicalSecurityDevice::class, App\Models\PhysicalServer::class, App\Models\PhysicalSwitch::class, App\Models\StorageDevice::class, App\Models\WifiTerminal::class, App\Models\Workstation::class, App\Models\Router::class, App\Models\NetworkSwitch::class)
        <tr>
            <th width="10%">
                {{ trans('cruds.physicalLink.fields.src') }}
            </th>
            <td width="20%">
                @if ($link->peripheralSrc!=null)
                @canShow($link->peripheralSrc)
                <a href="{{ route('admin.peripherals.show', $link->peripheral_src_id) }}">
                    {{ $link->peripheralSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->peripheralSrc->name }}
                @endcanShow
                @elseif ($link->phoneSrc!=null)
                @canShow($link->phoneSrc)
                <a href="{{ route('admin.phones.show', $link->phone_src_id) }}">
                    {{ $link->phoneSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->phoneSrc->name }}
                @endcanShow
                @elseif ($link->physicalRouterSrc!=null)
                @canShow($link->physicalRouterSrc)
                <a href="{{ route('admin.physical-routers.show', $link->physical_router_src_id) }}">
                    {{ $link->physicalRouterSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->physicalRouterSrc->name }}
                @endcanShow
                @elseif ($link->physicalSecurityDeviceSrc!=null)
                @canShow($link->physicalSecurityDeviceSrc)
                <a href="{{ route('admin.physical-security-devices.show', $link->physical_security_device_src_id) }}">
                    {{ $link->physicalSecurityDeviceSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->physicalSecurityDeviceSrc->name }}
                @endcanShow
                @elseif ($link->physicalServerSrc!=null)
                @canShow($link->physicalServerSrc)
                <a href="{{ route('admin.physical-servers.show', $link->physical_server_src_id) }}">
                    {{ $link->physicalServerSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->physicalServerSrc->name }}
                @endcanShow
                @elseif ($link->physicalSwitchSrc!=null)
                @canShow($link->physicalSwitchSrc)
                <a href="{{ route('admin.physical-switches.show', $link->physical_switch_src_id) }}">
                    {{ $link->physicalSwitchSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->physicalSwitchSrc->name }}
                @endcanShow
                @elseif ($link->storageDeviceSrc!=null)
                @canShow($link->storageDeviceSrc)
                <a href="{{ route('admin.storage-devices.show', $link->storage_device_src_id) }}">
                    {{ $link->storageDeviceSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->storageDeviceSrc->name }}
                @endcanShow
                @elseif ($link->wifiTerminalSrc!=null)
                @canShow($link->wifiTerminalSrc)
                <a href="{{ route('admin.wifi-terminals.show', $link->wifi_terminal_src_id) }}">
                    {{ $link->wifiTerminalSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->wifiTerminalSrc->name }}
                @endcanShow
                @elseif ($link->workstationSrc!=null)
                @canShow($link->workstationSrc)
                <a href="{{ route('admin.workstations.show', $link->workstation_src_id) }}">
                    {{ $link->workstationSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->workstationSrc->name }}
                @endcanShow
                @elseif ($link->routerSrc!=null)
                @canShow($link->routerSrc)
                <a href="{{ route('admin.routers.show', $link->router_src_id) }}">
                    {{ $link->routerSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->routerSrc->name }}
                @endcanShow
                @elseif ($link->networkSwitchSrc!=null)
                @canShow($link->networkSwitchSrc)
                <a href="{{ route('admin.network-switches.show', $link->network_switch_src_id) }}">
                    {{ $link->networkSwitchSrc->name }}
                </a>
                @elsecanShow
                    {{ $link->networkSwitchSrc->name }}
                @endcanShow
                @endif
            </td>
            <th width="10%">
                {{ trans('cruds.physicalLink.fields.src_port') }}
            </th>
            <td width="10%">
                {{ $link->src_port }}
            </td>
            <th width="10%">
                {{ trans('cruds.physicalLink.fields.dest') }}
            </th>
            <td width="20%">
                @if ($link->peripheralDest!=null)
                @canShow($link->peripheralDest)
                <a href="{{ route('admin.peripherals.show', $link->peripheral_dest_id) }}">
                    {{ $link->peripheralDest->name }}
                </a>
                @elsecanShow
                    {{ $link->peripheralDest->name }}
                @endcanShow
                @elseif ($link->phoneDest!=null)
                @canShow($link->phoneDest)
                <a href="{{ route('admin.phones.show', $link->phone_dest_id) }}">
                    {{ $link->phoneDest->name }}
                </a>
                @elsecanShow
                    {{ $link->phoneDest->name }}
                @endcanShow
                @elseif ($link->physicalRouterDest!=null)
                @canShow($link->physicalRouterDest)
                <a href="{{ route('admin.physical-routers.show', $link->physical_router_dest_id) }}">
                    {{ $link->physicalRouterDest->name }}
                </a>
                @elsecanShow
                    {{ $link->physicalRouterDest->name }}
                @endcanShow
                @elseif ($link->physicalSecurityDeviceDest!=null)
                @canShow($link->physicalSecurityDeviceDest)
                <a href="{{ route('admin.physical-security-devices.show', $link->physical_security_device_dest_id) }}">
                    {{ $link->physicalSecurityDeviceDest->name }}
                </a>
                @elsecanShow
                    {{ $link->physicalSecurityDeviceDest->name }}
                @endcanShow
                @elseif ($link->physicalServerDest!=null)
                @canShow($link->physicalServerDest)
                <a href="{{ route('admin.physical-servers.show', $link->physical_server_dest_id) }}">
                    {{ $link->physicalServerDest->name }}
                </a>
                @elsecanShow
                    {{ $link->physicalServerDest->name }}
                @endcanShow
                @elseif ($link->physicalSwitchDest!=null)
                @canShow($link->physicalSwitchDest)
                <a href="{{ route('admin.physical-switches.show', $link->physical_switch_dest_id) }}">
                    {{ $link->physicalSwitchDest->name }}
                </a>
                @elsecanShow
                    {{ $link->physicalSwitchDest->name }}
                @endcanShow
                @elseif ($link->storageDeviceDest!=null)
                @canShow($link->storageDeviceDest)
                <a href="{{ route('admin.storage-devices.show', $link->storage_device_dest_id) }}">
                    {{ $link->storageDeviceDest->name }}
                </a>
                @elsecanShow
                    {{ $link->storageDeviceDest->name }}
                @endcanShow
                @elseif ($link->wifiTerminalDest!=null)
                @canShow($link->wifiTerminalDest)
                <a href="{{ route('admin.wifi-terminals.show', $link->wifi_terminal_dest_id) }}">
                    {{ $link->wifiTerminalDest->name }}
                </a>
                @elsecanShow
                    {{ $link->wifiTerminalDest->name }}
                @endcanShow
                @elseif ($link->workstationDest!=null)
                @canShow($link->workstationDest)
                <a href="{{ route('admin.workstations.show', $link->workstation_dest_id) }}">
                    {{ $link->workstationDest->name }}
                </a>
                @elsecanShow
                    {{ $link->workstationDest->name }}
                @endcanShow
                @elseif ($link->routerDest!=null)
                @canShow($link->routerDest)
                <a href="{{ route('admin.routers.show', $link->router_dest_id) }}">
                    {{ $link->routerDest->name }}
                </a>
                @elsecanShow
                    {{ $link->routerDest->name }}
                @endcanShow
                @elseif ($link->networkSwitchDest!=null)
                @canShow($link->networkSwitchDest)
                <a href="{{ route('admin.network-switches.show', $link->network_switch_dest_id) }}">
                    {{ $link->networkSwitchDest->name }}
                </a>
                @elsecanShow
                    {{ $link->networkSwitchDest->name }}
                @endcanShow
                @endif
            </td>
        @endcanAccessAny
            <th width="10%">
                {{ trans('cruds.physicalLink.fields.dest_port') }}
            </th>
            <td width="10%">
                {{ $link->dest_port }}
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.physicalLink.fields.description') }}
            </th>
            <td colspan="7">
                {!! $link->description !!}
            </td>
        </tr>
    </tbody>
</table>
