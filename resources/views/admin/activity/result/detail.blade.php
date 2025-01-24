@extends('admin.layouts.app')

@section('content')
    <result-detail :activity="{{ json_encode($activity) }}" :result="{{ json_encode($result) }}"
                   :types="{{ json_encode($types) }}" :toast="{{ json_encode($toast) }}"
                   :element="{{ json_encode($element)}}"
                   :translated-data='{{json_encode($translatedData)}}'
                   :current-language='{{json_encode($currentLanguage)}}'
    ></result-detail>
@endsection
