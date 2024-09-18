@extends('admin.layouts.app')

@section('content')
    <import-list :translated-data='{{json_encode($translatedData)}}'/>
@endsection
