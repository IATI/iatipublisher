@extends('admin.layouts.app')

@section('content')
    <organisation-list
            :countries='{{json_encode($country)}}'
            :setup-completeness='{{json_encode($setupCompleteness)}}'
            :registration-types='{{json_encode($registrationType)}}'
            :publisher-types='{{json_encode($publisherType)}}'
            :data-licenses='{{json_encode($dataLicense)}}'
            :oldest-dates='{{json_encode($oldestDates)}}'
            :translated-data='{{json_encode($translatedData)}}'
    >
    </organisation-list>
@endsection
