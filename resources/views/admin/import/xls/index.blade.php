@extends('admin.layouts.app')

@section('content')
    <activity-xls-upload
        :translated-data='{{json_encode($translatedData)}}'
    >
    </activity-xls-upload>
@endsection
