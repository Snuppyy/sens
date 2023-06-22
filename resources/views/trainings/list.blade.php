@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>@lang('Обучение')</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            @foreach($trainings as $training)
                <div class="card mb-3"{!! $training->id == 4 ? ' style="background-color: #f0f0f0!important; border-color: #999"' : '' !!}">
                    <div class="card-body">
                        <h4 class="card-title">
                            <a href="{{ route('trainings.view', ['training' => $training->id]) }}">{{ $training->title }}</a>
                        </h4>

                        <p>{{ $training->short }}</p>

                        <a href="{{ route('trainings.view', ['training' => $training->id]) }}" class="btn btn-primary">
                            @lang('Подробности')
                        </a>

                        <div class="collapse mt-4" id="details{{ $training->id }}">
                            {!! $training->text !!}

                            @if(!$user || !$user->trainingApplication($training->id) || $user->trainingApplication($training->id)->status == 'draft')
                                <a href="#before-application" data-href="{{ route('training.application', ['training' => $training->id]) }}" data-toggle="modal" class="btn btn-success">
                                    @lang('Подача заявки')
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-md-4">
            @if($user)
            <div class="card mb-4">
                <div class="card-header">
                    @lang('Ваши заявки')
                </div>
                @if(count($user->trainingApplications))
                <ul class="list-group list-group-flush">
                    @foreach($user->trainingApplications as $application)
                        <li class="list-group-item">
                            {{ $application->training->title }}
                            <small>приём завершается {{ $application->training->id == 5 ? '7 ноября 2019 в 11:00' : '11/02/2023 12:00' }}</small>
                            @if(in_array($application->status, ['draft', 'applied']))
                                <a href="{{ route('training.application', ['training' => $application->training->id]) }}">
                                    <span class="badge badge-{{ ['draft' => 'secondary', 'applied' => 'primary'][$application->status] }}">
                                        <i class="fas fa-edit"></i> {{ $application->readableStatus }}
                                    </span>
                                </a>
                            @else
                                <span class="badge badge-{{ ['consideration' => 'warning', 'accepted' => 'success', 'rejected' => 'danger'][$application->status] }}">
                                    {{ $application->readableStatus }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @else
                    <div class="card-body">
                        <em>@lang('Заявок нет.')</em>
                    </div>
                @endif
            </div>
            @endif

            {{-- @if(!$user || $user->trainingApplications->count() < $trainings->count())
                <a href="#before-application" data-href="{{ route('training.application') }}" data-toggle="modal" class="btn btn-success">
                    @lang('Подать заявки на все тренинги')
                </a>
            @endif --}}
        </div>
    </div>

</div>

<div class="modal fade" id="before-application" tabindex="-1" role="dialog" aria-labelledby="before-application-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="before-application-label">@lang('Подтвердите ознакомление с информацией')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="@lang('Закрыть')">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="lead">
                    @lang('Для подачи заявки на участие в данном тренинге необходимо подтвердить ознакомление с информацией о тренинге и ответить на вопросы.')
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary my-1 my-sm-0" data-dismiss="modal">@lang('Закрыть')</button>
                <a href="" class="btn btn-primary my-1 my-sm-0 confirm">@lang('Подтверждаю')</a>
            </div>
        </div>
    </div>
</div>
@endsection
