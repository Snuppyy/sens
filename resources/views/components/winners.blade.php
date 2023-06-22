            <h2 class="mt-5 mb-4">@lang('Победители предыдущих розыгрышей')</h2>
            <div class="table-responsive">
                <table class="table table-striped winners">
                    <thead>
                        <tr>
                            <th scope="col">
                            </th>
                            <th scope="col">
                                @lang('Базовый уровень')<br>
                                <span class="lead">@lang('Приз: электрочайник')</span>
                            </th>
                            <th scope="col">
                                @lang('Продвинутый уровень')<br>
                                <span class="lead">@lang('Приз: утюг')</span>
                            </th>
                            <th scope="col">
                                @lang('Специализированный уровень')<br>
                                <span class="lead">@lang('Приз: флешка 64Гб')</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($winners as $date => $levels)
                            <tr>
                                <th scope="row">{{ $date }}</th>
                                @for ($i = 1; $i <= 3; $i++)
                                <td>
                                    @if (empty($levels[$i][0]))
                                        <em>@lang('Нет победителя')</em>
                                    @else
                                        {!! $levels[$i][0]['link'] !!} {{ $levels[$i][0]['winner'] }}
                                    @endif
                                    <br>
                                    @if (empty($levels[$i][1]))
                                        <em>@lang('Нет победителя')</em>
                                    @else
                                        {!! $levels[$i][1]['link'] !!} {{ $levels[$i][1]['winner'] }}
                                    @endif
                                </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
