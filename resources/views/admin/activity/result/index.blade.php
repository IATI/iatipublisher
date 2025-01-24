@extends('admin.layouts.app')

@section('content')
    <result-list :activity="{{ json_encode($activity, JSON_THROW_ON_ERROR) }}"
                 :results="{{ json_encode($results, JSON_THROW_ON_ERROR) }}"
                 :types="{{ json_encode($types, JSON_THROW_ON_ERROR) }}"
                 :toast="{{ json_encode($toast, JSON_THROW_ON_ERROR) }}"
                 :translated-data='{{json_encode($translatedData)}}'
                 :current-language='{{json_encode($currentLanguage)}}'
    ></result-list>
@endsection
