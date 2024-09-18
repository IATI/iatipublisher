@extends('admin.layouts.app')

@section('content')
    <user-listing :organizations='{{ json_encode($organizations) }}'
                  :status='{{ json_encode($status) }}'
                  :roles='{{ json_encode($roles) }}'
                  :current-user-id="{{json_encode(auth()->user()->id)}}"
                  :user-role='{{ json_encode($userRole) }}'
                  :oldest-dates="{{json_encode($oldestDates)}}"
                  :translated-data='{{json_encode($translatedData)}}'
    ></user-listing>
@endsection
