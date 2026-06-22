@props([
    'site',
    'withLink' => false
])
<table class="table table-bordered table-striped table-report" id="{{ $site->getUID() }}">
    <tbody>
        <tr>
            <th width="10%">
                {{ trans('cruds.site.fields.name') }}
            </th>
            <td width="20%">
            @if($withLink)
            @canShow($site)
            <a href="{{ route('admin.sites.show', $site->id) }}">{{ $site->name }}</a>
            @elsecanShow
            {{ $site->name }}
            @endcanShow
            @else
            {{ $site->name }}
            @endif
            </td>
            <th width="10%">
                {{ trans('cruds.site.fields.type') }}
            </th>
            <td width="20%">
                {{ $site->type }}
            </td>
            <th width="10%">
                {{ trans('cruds.site.fields.attributes') }}
            </th>
            <td colspan="2">
                @foreach(explode(" ", $site->attributes) as $attribute)
                    <span class="badge badge-info">{{ $attribute }}</span>
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.site.fields.description') }}
            </th>
            <td colspan="5">
                {!! $site->description !!}
            </td>
            <td width="10%" align="center">
                @if ($site->icon_id === null)
                <img src='/images/site.png' width='60' height='60'>
                @else
                <img src='{{ route('admin.documents.show', $site->icon_id) }}' width='60' height='60'>
                @endif
            </td>
        </tr>
        @canAccess(App\Models\Building::class)
        <tr>
            <th>
                {{ trans('cruds.site.fields.buildings') }}
            </th>
            <td colspan="6">
                @foreach($site->buildings as $building)
                    @canShow($building)
                        <a href="{{ route('admin.buildings.show', $building->id) }}">
                        {{ $building->name ?? '' }}
                        </a>
                        @if (!$loop->last)
                        ,
                        @endif
                    @elsecanShow
                        {{ $building->name ?? '' }}
                        @if (!$loop->last)
                        ,
                        @endif
                    @endcanShow
                @endforeach
            </td>
        </tr>
        @endcanAccess
    </tbody>
</table>
