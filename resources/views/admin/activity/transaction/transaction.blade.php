@extends('admin.layouts.app')

@section('content')
    <transaction-list
        :activity="{{ json_encode($activity) }}"
        :transactions="{{ json_encode($transactions) }}"
        :types="{{ json_encode($types) }}"
        :toast="{{ json_encode($toast)}}"
        :translated-data="{{ json_encode($translatedData) }}"
    >
    </transaction-list>
@endsection
