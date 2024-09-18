@extends('admin.layouts.app')

@section('content')
    <setting-page :currencies='{{ json_encode($currencies) }}' :languages='{{ json_encode($languages) }}'
        :humanitarian='{{ json_encode($humanitarian) }}' :organization='{{ Auth::user()->organization }}'
        :default-collaboration-type='{{ json_encode($defaultCollaborationType)  }}'
        :default-flow-type='{{ json_encode($defaultFlowType)  }}'
        :default-finance-type='{{ json_encode($defaultFinanceType)  }}'
        :default-aid-type='{{ json_encode($defaultAidType)  }}'
        :default-tied-status='{{ json_encode($defaultTiedStatus) }}'
        :budget-not-provided="{{ json_encode($budgetNotProvided) }}" user-role="{{ $userRole }}"
        :is-superadmin="{{json_encode(isSuperAdmin())}}"
        :translated-data="{{json_encode($translatedData)}}"
    ></setting-page>
@endsection
