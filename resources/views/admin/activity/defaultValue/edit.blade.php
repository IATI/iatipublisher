@extends('admin.layouts.app')

@section('content')
    <activity-default-values :currencies='{{ json_encode($currencies) }}'
                             :languages='{{ json_encode($languages) }}'
                             :activity-id='{{ $activityId }}'
                             :humanitarian='{{ json_encode($humanitarian) }}'
                             :budget-not-provided='{{ json_encode($budgetNotProvided) }}'
                             :translated-data='{{json_encode($translatedData)}}'
    >
    </activity-default-values>
@endsection
