@extends('layouts.admin')

@section('title')
    Modifier un cartographe
@endsection

@section('content')
<form method="POST" action="{{ route('admin.cartographers.update', $cartographer->id) }}">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-header">
            Modifier un cartographe
        </div>
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{-- Utilisateur et rôle : modifiables --}}
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>{{ trans('cruds.cartographer.fields.user') }}</label>
                        <select name="user_id" id="user_id"
                                class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}">
                            <option value="">-- Aucun --</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}"
                                    {{ old('user_id', $cartographer->user_id) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('user_id'))
                            <div class="invalid-feedback">{{ $errors->first('user_id') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.cartographer.fields.user_helper') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="role_id">{{ trans('cruds.cartographer.fields.role') }}</label>
                        <select name="role_id" id="role_id"
                                class="form-control select2 {{ $errors->has('role_id') ? 'is-invalid' : '' }}">
                            <option value="">-- Aucun --</option>
                            @foreach($roles as $id => $title)
                                <option value="{{ $id }}"
                                    {{ old('role_id', $cartographer->role_id) == $id ? 'selected' : '' }}>
                                    {{ $title }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('role_id'))
                            <div class="invalid-feedback">{{ $errors->first('role_id') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.cartographer.fields.role_helper') }}</span>
                    </div>
                </div>
            </div>

            {{-- Type et objet : en lecture seule --}}
            <div class="row mb-3">
                <div class="col-4">
                    <div class="form-group">
                        <label>{{ trans('cruds.cartographer.fields.type') }}</label>
                        <input type="text" class="form-control"
                               value="{{ $models[$cartographer->cartographiable_type] ?? $cartographer->cartographiable_type }}"
                               readonly>
                        <span class="help-block">{{ trans('cruds.cartographer.fields.type_helper') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>{{ trans('cruds.cartographer.fields.object') }}</label>
                        <input type="text" class="form-control"
                               value="{{ $cartographer->cartographiable->name ?? '(id:'.$cartographer->cartographiable_id.')' }}"
                               readonly>
                        <span class="help-block">{{ trans('cruds.cartographer.fields.object_helper') }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" type="submit">{{ trans('global.save') }}</button>
        <a href="{{ route('admin.cartographers.index') }}" class="btn btn-default">{{ trans('global.cancel') }}</a>
    </div>
</form>
@endsection

@section('scripts')
@parent
<script>
document.addEventListener("DOMContentLoaded", function () {
(function () {
    // Exclusivité mutuelle user / rôle
    $('#user_id').on('change', function () {
        if ($(this).val()) {
            $('#role_id').val(null).trigger('change');
        }
    });
    $('#role_id').on('change', function () {
        if ($(this).val()) {
            $('#user_id').val(null).trigger('change');
        }
    });

    // Validation : exactement un de user ou rôle
    $('form').on('submit', function (e) {
        var user = $('#user_id').val();
        var role = $('#role_id').val();
        if (!user && !role) {
            e.preventDefault();
            alert('{{ trans('cruds.cartographer.errors.user_or_role_required') }}');
        }
    });

    $('.select2').select2({ width: '100%' });
})();
});
</script>
@endsection
