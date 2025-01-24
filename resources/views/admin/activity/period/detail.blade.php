@extends('admin.layouts.app')

@section('content')
    <periods-detail :activity="{{ json_encode($activity) }}"
                    :parent-data="{{ json_encode($parentData) }}"
                    :period="{{ json_encode($period) }}"
                    :types="{{ json_encode($types) }}"
                    :toast="{{ json_encode($toast) }}"
                    :element="{{ json_encode($element) }}"
                    :translated-data='{{json_encode($translatedData)}}'
                    :current-language='{{json_encode($currentLanguage)}}'
    >
    </periods-detail>
@endsection
