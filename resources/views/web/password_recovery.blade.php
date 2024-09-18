@extends('web.layouts.app')

@section('content')
  <password-recovery :translated-data='{{json_encode($translatedData)}}'></password-recovery>
@endsection
