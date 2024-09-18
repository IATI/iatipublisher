@extends('web.layouts.app')

@section('content')
  <reset-password
      :token='{{ json_encode($token) }}'
      :email='{{ json_encode($email) }}'
      :translated-data='{{json_encode($translatedData)}}'
  ></reset-password>
@endsection
