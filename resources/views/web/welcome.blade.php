@extends('web.layouts.app')

@section('content')
  <welcome-signin
      page="{{$page}}"
      message="{{$message}}"
      intent="{{$intent}}"
      :translated-data="{{ json_encode($translatedData) }}">
  </welcome-signin>
@endsection
