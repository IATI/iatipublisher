@extends('web.layouts.app')

@section('content')
  <reset-page :translated-data='{{json_encode($translatedData)}}'></reset-page>
@endsection
