@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <h4 class="card-header">@yield('title')</h4>

                <div class="card-body">
                    <p>@yield('message')</p>

                    <a href="javascript: window.close()" class="btn btn-primary">
                        @lang('Закрыть')
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
