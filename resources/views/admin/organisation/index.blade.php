@extends('admin.layouts.app')

@section('content')
    <organisation-data
        v-bind:mandatory-completed="{{ $mandatoryCompleted?1:0 }}"
        :elements="{{ json_encode($elements) }}"
        :groups="{{ json_encode($elementGroups) }}"
        :organization="{{ json_encode($organization) }}"
        :progress="{{ $progress }}"
        :toast="{{ json_encode($toast) }}"
        :types="{{ json_encode($types) }}"
        :status="{{ json_encode($status) }}"
        :user-role="{{ json_encode($userRole) }}"
        :translated-data='{{json_encode($translatedData)}}'
    />
@endsection



