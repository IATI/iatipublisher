@extends('admin.layouts.app')

@section('content')
    <xls-list :status="{{ json_encode($status) }}"
              :import-data="{{ json_encode($importData) }}"
              :global-error="{{ json_encode($errors) }}"
              :error-count="{{ json_encode($errorCount) }}"
              :translated-data='{{json_encode($translatedData)}}'
    ></xls-list>
@endsection
