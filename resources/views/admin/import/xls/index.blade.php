@extends('admin.layouts.app')

@section('content')
    <activity-xls-upload
        :translated-data='{{json_encode($translatedData)}}'
        :current-language='{{json_encode($currentLanguage)}}'
    >
    </activity-xls-upload>
@endsection
