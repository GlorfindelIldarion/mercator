@extends('layouts.admin')

@section('title')
    {{ trans('panel.menu.info') }}
@endsection

@section('content')

<div class="row g-3">

    {{-- ── Mercator ──────────────────────────────────────────────────────── --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-bold">
                <i class="bi bi-app me-1"></i>Mercator
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:45%">{{ trans('panel.info.version') }}</td>
                        <td><strong id="info-version">{{ $mercatorVersion }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.environment') }}</td>
                        <td id="info-env">{{ $appEnv }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.timezone') }}</td>
                        <td id="info-tz">{{ $appTimezone }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.locale') }}</td>
                        <td id="info-locale">{{ $appLocale }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.date') }}</td>
                        <td id="info-date">{{ now()->format(config('panel.date_format', 'd/m/Y') . ' ' . config('panel.time_format', 'H:i')) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.db_driver') }}</td>
                        <td id="info-db">{{ $dbDriver }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.memory') }}</td>
                        <td id="info-mem">
                            {{ $memUsedMb }}&thinsp;Mo / {{ $memLimit }}
                            @if ($memFreeMb !== null)
                                <span class="text-muted small">({{ $memFreeMb }}&thinsp;Mo {{ trans('panel.info.memory_free') }})</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Utilisateur ──────────────────────────────────────────────────── --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-bold">
                <i class="bi bi-person-circle me-1"></i>{{ trans('panel.info.user') }}
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:45%">{{ trans('panel.info.login') }}</td>
                        <td id="info-login"><code>{{ $userLogin }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.name') }}</td>
                        <td id="info-name">{{ $userName }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.email') }}</td>
                        <td id="info-email">{{ $userEmail }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.roles') }}</td>
                        <td id="info-roles">
                            @forelse ($roles as $role)
                                <span class="badge bg-secondary me-1">{{ $role }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.cartographer') }}</td>
                        <td id="info-carto">
                            @if ($isCartographer)
                                <span class="badge bg-success">{{ trans('global.yes') }}</span>
                                @if (! empty($cartographerTypes))
                                    <span class="text-muted small ms-1">({{ implode(', ', $cartographerTypes) }})</span>
                                @endif
                            @else
                                <span class="badge bg-light text-dark border">{{ trans('global.no') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── Bouton copier pour helpdesk ──────────────────────────────────────── --}}
<div class="mt-3">
    <button class="btn btn-outline-secondary btn-sm" id="btn-copy-info">
        <i class="bi bi-clipboard me-1"></i>{{ trans('panel.info.copy_for_helpdesk') }}
    </button>
    <span id="copy-feedback" class="text-success ms-2 small d-none">
        <i class="bi bi-check-circle"></i> {{ trans('panel.info.copied') }}
    </span>
</div>

@endsection

@section('scripts')
@parent
<script>
document.getElementById('btn-copy-info').addEventListener('click', function () {
    function val(id) {
        var el = document.getElementById(id);
        return el ? el.innerText.trim() : '';
    }
    var lines = [
        'Mercator : '      + val('info-version'),
        'Env : '           + val('info-env'),
        'Fuseau : '        + val('info-tz'),
        'Langue : '        + val('info-locale'),
        'Date : '          + val('info-date'),
        'DB : '            + val('info-db'),
        'Mémoire : '       + val('info-mem'),
        '---',
        'Login : '         + val('info-login'),
        'Nom : '           + val('info-name'),
        'Email : '         + val('info-email'),
        'Rôles : '         + val('info-roles'),
        'Cartographe : '   + val('info-carto'),
    ];
    navigator.clipboard.writeText(lines.join('\n')).then(function () {
        var fb = document.getElementById('copy-feedback');
        fb.classList.remove('d-none');
        setTimeout(function () { fb.classList.add('d-none'); }, 2500);
    });
});
</script>
@endsection
