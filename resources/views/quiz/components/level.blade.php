                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        @if ($user->level < $level)
                            {{ $slot }}
                        @else
                            @if ($topQuestionnaire)
                                @if ($topQuestionnaire->result == 100)
                                    @if ($user->winner)
                                        @lang('Ваш лучший результат сегодня: 100%<br>Вы не можете участвовать в розыгрыше поскольку уже выиграли приз.')
                                    @elseif ($topQuestionnaire->drawing)
                                        @lang('Сегодня вы уже набрали 100% и участвуете в розыгрыше.<br>Результаты розыгрыша узнайте на сайте.')
                                    @elseif ($topQuestionnaire->participate)
                                        @lang('Сегодня вы уже набрали 100% и можете участвовать в розыгрыше, :link.', [
                                            'link' => '<a href="' . route('profile', ['required' => 'required']) . '">' . __('заполнив свой профиль') . '</a>'
                                        ])
                                    @else
                                        @lang('Ваш лучший результат сегодня: 100%')
                                    @endif
                                @else
                                    <strong>@lang('Ваш лучший результат сегодня: :result%', ['result' => $topQuestionnaire->result])</strong>
                                    <br>
                                    @lang('Чтобы участвовать в розыгрыше вы должны набрать 100%.')
                                @endif
                            @else
                                @lang('Пройдите опрос, чтобы участвовать в розыгрыше.')
                            @endif
                        @endif
                    </li>
                    @if (count($questionnaires))
                    <li class="list-group-item">
                        <h5>@lang('Участие в розыгрышах')</h5>
                        <small>@lang('В списке вы можете видеть ваш код участника и время розыгрыша.')</small>
                    </li>
                    @foreach ($questionnaires as $complete)
                    <li class="list-group-item">
                        <small class="float-right">{{ $complete->finished_at->addHours(24)->sub(CarbonInterval::fromString(config('app.begin_time')))->format('d.m.Y') }} 09:00</small>
                        {{ $complete->code }}
                    </li>
                    @endforeach
                    @endif
                </ul>

                <div class="card-body">
                    @if ($user->level < $level || ($topQuestionnaire && $topQuestionnaire->result == 100))
                        <button class="btn btn-primary" disabled>@lang('Пройти опрос')</button>
                    @elseif ($questionnaire)
                        @if ($questionnaire->closed && !$questionnaire->training_finished)
                            <a href="/questionnaire/{{ $level }}" class="btn btn-primary">@lang('Пройти обучение')</a>
                        @elseif (!$questionnaire->closed || $seconds <= 0)
                            <a href="/questionnaire/{{ $level }}" class="btn btn-primary">@lang('Пройти опрос')</a>
                        @else
                            <button disabled class="btn btn-primary big-countdown" data-seconds="{{ $seconds }}">@lang('Пройти опрос можно через <span>--:--:--</span>')</button>
                        @endif
                    @else
                        <a href="/questionnaire/{{ $level }}" class="btn btn-primary">@lang('Пройти опрос')</a>
                    @endif
                </div>
