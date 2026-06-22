@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $bay->name }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            {{ trans('global.edit') }} {{ trans('cruds.bay.title_singular') }}
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route("admin.bays.update", [$bay->id]) }}" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <div class="form-group">
                    <label class="label-required" for="name">{{ trans('cruds.bay.fields.name') }}</label>
                    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                           id="name" value="{{ old('name', $bay->name) }}" required autofocus/>
                    @if($errors->has('name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.bay.fields.name_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1" for="description">{{ trans('cruds.bay.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description"
                              id="description">{!! old('description', $bay->description) !!}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.bay.fields.description_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1" for="site_id">{{ trans('cruds.bay.fields.site') }}</label>
                    <select class="form-control select2 {{ $errors->has('site') ? 'is-invalid' : '' }}" name="site_id"
                            id="site_id">
                        <option></option>
                        @foreach($sites as $id => $site)
                            <option value="{{ $id }}" {{ ($bay->site ? $bay->site->id : old('site_id')) == $id ? 'selected' : '' }}>{{ $site }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('site'))
                        <div class="invalid-feedback">
                            {{ $errors->first('site') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.bay.fields.site_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1" for="building_id">{{ trans('cruds.bay.fields.building') }}</label>
                    <select class="form-control select2 {{ $errors->has('building') ? 'is-invalid' : '' }}" name="building_id"
                            id="building_id">
                        <option></option>
                        @foreach($buildings as $id => $building)
                            <option value="{{ $id }}" {{ ($bay->building ? $bay->building->id : old('building_id')) == $id ? 'selected' : '' }}>{{ $building }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('building'))
                        <div class="invalid-feedback">
                            {{ $errors->first('building') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.bay.fields.building_helper') }}</span>
                </div>
                <div class="form-group">
                    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.bays.index') }}">
                        {{ trans('global.back_to_list') }}
                    </a>
                    <button id="btn-save" class="btn btn-success" type="submit">
                        {{ trans('global.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
