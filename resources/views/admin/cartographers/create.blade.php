@extends('layouts.admin')

@section('title')
    Ajouter un cartographe
@endsection

@section('content')
<form method="POST" action="{{ route('admin.cartographers.store') }}">
    @csrf
    <div class="card">
        <div class="card-header">
            Ajouter un cartographe
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


            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="user_id">{{ trans('cruds.cartographer.fields.user') }}</label>
                        <select name="user_id" id="user_id"
                                class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}">
                            <option value="">-- Aucun --</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>
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
                        <label for="role_id">Rôle</label>
                        <select name="role_id" id="role_id"
                                class="form-control select2 {{ $errors->has('role_id') ? 'is-invalid' : '' }}">
                            <option value="">-- Aucun --</option>
                            @foreach($roles as $id => $title)
                                <option value="{{ $id }}" {{ old('role_id') == $id ? 'selected' : '' }}>
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

            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label class="label-required" for="type-select">{{ trans('cruds.cartographer.fields.type') }}</label>
                        <select id="type-select" name="cartographiable_type"
                                class="form-control select2 {{ $errors->has('cartographiable_type') ? 'is-invalid' : '' }}" required>
                            <option value="">-- Choisir un type --</option>
                            @foreach($models as $class => $label)
                                <option value="{{ $class }}" {{ old('cartographiable_type') === $class ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('cartographiable_type'))
                            <div class="invalid-feedback">{{ $errors->first('cartographiable_type') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.cartographer.fields.type_helper') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="label-required" for="object-select">{{ trans('cruds.cartographer.fields.object') }}</label>
                        <select id="object-select" name="cartographiable_id"
                                class="form-control select2 {{ $errors->has('cartographiable_id') ? 'is-invalid' : '' }}" required>
                            <option value="">-- Choisir un objet --</option>
                        </select>
                        @if($errors->has('cartographiable_id'))
                            <div class="invalid-feedback">{{ $errors->first('cartographiable_id') }}</div>
                        @endif
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
    var objectsUrl = '{{ route('admin.cartographers.objects') }}';

    function loadObjects(type) {
        var $select = $('#object-select');
        $select.empty().append('<option value="">-- Chargement... --</option>');

        if (!type) {
            $select.empty().append('<option value="">-- Choisir un objet --</option>');
            return;
        }

        $.getJSON(objectsUrl, { type: type }, function (data) {
            $select.empty().append('<option value="">-- Choisir un objet --</option>');
            $.each(data, function (i, obj) {
                $select.append($('<option>', { value: obj.id, text: obj.name || '(id:' + obj.id + ')' }));
            });
            $select.trigger('change');
        }).fail(function () {
            $select.empty().append('<option value="">-- Erreur de chargement --</option>');
        });
    }

    $('#type-select').on('change', function () {
        loadObjects($(this).val());
    });

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

    // Init Select2
    $('.select2').select2({ width: '100%' });
})();
});
</script>
@endsection
