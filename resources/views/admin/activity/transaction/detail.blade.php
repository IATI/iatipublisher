@extends('admin.layouts.app')

@section('content')
    <transaction-detail :activity="{{ json_encode($activity) }}"
                        :transaction="{{ json_encode($transaction) }}"
                        :types="{{ json_encode($types) }}"
                        :toast="{{ json_encode($toast) }}"
                        :element= "{{ json_encode($element)}}"
                        :translated-data="{{ json_encode($translatedData) }}"
    ></transaction-detail>
@endsection
