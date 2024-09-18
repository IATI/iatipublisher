@extends('admin.layouts.app')

@section('content')
<activity-template
            :toast="{{ json_encode($toast) }}"
            :default-language="{{json_encode($defaultLanguage)}}"
            :organization-onboarding="{{json_encode($organizationOnboarding)}}"
            :currencies='{{ json_encode($currencies) }}'
            :languages='{{ json_encode($languages) }}'
            :humanitarian='{{ json_encode($humanitarian) }}'
            :organization='{{ $organization }}'
            :default-flow-type='{{ json_encode($defaultFlowType)  }}'
            :default-finance-type='{{ json_encode($defaultFinanceType)  }}'
            :default-aid-type='{{ json_encode($defaultAidType)  }}'
            :default-tied-status='{{ json_encode($defaultTiedStatus) }}'
            :organization-type='{{ json_encode($organizationType) }}'
            :is-first-time='{{ json_encode($isFirstTime) }}'
            :translated-data='{{json_encode($translatedData)}}'
            :current-language='{{json_encode($currentLanguage)}}'
></activity-template>
@endsection
