                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        @if ($topQuestionnaire)
                            <strong>@lang('Ваш лучший результат: :result%', ['result' => $topQuestionnaire->result])</strong>
                        @else
                            @lang('Вы ещё не проходили этот опросник')
                        @endif
                    </li>
                    @if ($questionnaire && !$questionnaire->closed)
                    <li class="list-group-item">
                        {{ $questionnaire->question_index + 1 }}
                        @lang('из')
                        {{ $questionnaire->questions_count }}
                    </li>
                    @endif
                </ul>

                <div class="card-body">
                    @if (!$questionnaire || $questionnaire->closed)
                        <a href="/questionnaire/{{ $level }}" class="btn btn-primary">@lang('Пройти опрос')</a>
                    @else
                        <a href="/questionnaire/{{ $level }}" class="btn btn-primary">@lang('Продолжить прохождение')</a>
                    @endif

                    @if ($questionnaire && $questionnaire->closed && !$questionnaire->training_finished)
                        <a href="/questionnaire/{{ $level }}" class="btn btn-primary">@lang('Пройти обучение')</a>
                    @endif
                </div>
