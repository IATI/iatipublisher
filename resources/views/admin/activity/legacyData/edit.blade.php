@extends('admin.layouts.layout')

@section('form')
    {!! form($form) !!}
    <div class="hidden collection-container" form_type="legacy_data_legacy_data"
         data-prototype="{{ form_row($form->legacy_data->prototype()) }}">
    </div>
@endsection
