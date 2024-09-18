@extends('admin.layouts.app')

@section('content')
<div >
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <dashboard-page
                        :oldest-dates="{{ json_encode($oldestDates) }}"
                        :translated-data='{{json_encode($translatedData)}}'
                    >
                    </dashboard-page>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
