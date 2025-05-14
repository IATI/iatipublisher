@extends('admin.layouts.app')
@section('content')
    <activities-detail
        :elements="{{ json_encode($elements) }}"
        :groups="{{ json_encode($elementGroups) }}"
        :activity="{{ json_encode($activity) }}"
        :progress="{{ $progress }}"
        :lang="{{ json_encode('lang file') }}"
        :types="{{ json_encode($types) }}"
        :status="{{ json_encode($status) }}"
        :toast="{{ json_encode($toast) }}"
        :results="{{ json_encode($results) }}"
        :has_indicator="{{ $hasIndicatorPeriod['indicator'] }}"
        :has_period="{{ $hasIndicatorPeriod['period'] }}"
        :transactions="{{ json_encode($transactions) }}"
        :core-completed="{{ json_encode($coreCompleted) }}"
        :iati-validator-response="{{ json_encode($iatiValidatorResponse) }}"
        :import-activity-error="{{ json_encode($importActivityError) }}"
        :deprecation-status-map="{{json_encode($deprecationStatusMap)}}"
        :translated-data='{{json_encode($translatedData)}}'
        :current-language='{{json_encode($currentLanguage)}}'
    >
     </activities-detail>
@endsection
