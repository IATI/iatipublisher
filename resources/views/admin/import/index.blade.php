@extends('admin.layouts.app')

@section('content')
    <activity-upload :translated-data='{{json_encode($translatedData)}}'>
    </activity-upload>
@endsection
