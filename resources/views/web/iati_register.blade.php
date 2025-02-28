@extends('web.layouts.app')

@section('content')
  <iati-register-form :types='{{json_encode($types)}}' :translated-data='{{json_encode($translatedData)}}'></iati-register-form>
@endsection
