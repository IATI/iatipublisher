@extends('admin.layouts.app')

@section('content')
    <indicator-list :activity="{{ json_encode($activity) }}"
                    :parent-data="{{ json_encode($parentData) }}"
                    :indicators="{{ json_encode($indicators) }}"
                    :types="{{ json_encode($types) }}"
                    :toast="{{ json_encode($toast) }}"
                    :translated-data='{{json_encode($translatedData)}}'
                    :current-language='{{json_encode($currentLanguage)}}'

    ></indicator-list>
@endsection
