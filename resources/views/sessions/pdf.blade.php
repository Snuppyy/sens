<html>
    <head>
        <style>
            @if($print) {}
                @page :odd {
                    margin-left: {{ 1.2 + $margin }}cm;
                }

                @page :even {
                    margin-right: {{ 1.2 + $margin }}cm;
                }
            @endif {}

            body {
                @if($print)
                    font-family: 'DejaVu Serif', sans-serif;
                @else
                    font-family: 'DejaVu Sans', sans-serif;
                @endif
                max-width: 800px;
                margin: auto;
                font-size: 14px;
            }

            h3 {
                margin-bottom: 2em;
            }

            img {
                max-width: 100%;
            }

            table {
                border-collapse: collapse;
            }

            td, th {
                border: 1px solid #000;
                padding: 0 10px 3px;
            }

            td {
                vertical-align: top;
            }

            td p {
                margin: 10px 0 0;
            }

            td p:first-child {
                margin-top: 0;
            }

            th {
                padding: 0 10px 3px;
            }

            tr.alt {
                background: #f0f0f0;
            }

            tr.action, th {
                font-weight: bold;
                background: #e0e0e0;
            }

            tr.skill {
                background: #fffff0;
            }

            tr.skill.alt {
                background: #f0f0e8;
            }

            /* dt {
                font-weight: bold;
            } */

            dd {
                margin: 0;
            }

            ol ol {
                list-style: lower-alpha;
            }

            h1 small {
                display: block;
                font-size: 50%;
                color: #777;
            }

            .correct {
                font-style: italic;
            }

            .correct:before {
                content: '(+)';
                position: absolute;
                margin-left: -50px;
            }

            .no-page-break {
                page-break-inside: avoid;
            }

            /* .index {
                display: table;
                width: 100%;
            } */

            .index > div {
                position: relative;
                margin-top: 5px;
                padding-bottom: 21px;
            }

            /* .index dt,
            .index dd {
                display: table-cell;
            } */

            .index dd {
                display: block;
                position: absolute;
                width: 30px;
                right: 0;
                margin-top: -21px;
                background: #fff;
                padding-left: 2px;
            }

            .index dt a {
                background: #fff;
                position: relative;
                color: #000;
                text-decoration: none;
                display: inline-block;
                padding-right: 2px;
            }

            .index dt:after {
                display: inline-block;
                width: 100%;
                border-bottom: 1px dotted #000;
                position: relative;
                margin-top: -4px;
                z-index: -1;
            }

            .index dt {
                width: 100%;
            }

            .index dd {
                vertical-align: bottom;
            }

            .checkbox {
                position: relative;
                padding-left: 50px;
            }

            .checkbox:before {
                display: inline-block;
                border: 1px solid #000;
                width: 20px;
                height: 20px;
                position: absolute;
                top: 5px;
                left: 0;
            }

            .strong {
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <script type="text/php">
            if ( isset($pdf) ) {
                $pdf->page_script('
                    if ($PAGE_NUM > 1) {
                        $font = $fontMetrics->get_font("DejaVu {{$print ? 'Serif' : 'Sans'}}", "bold");
                        $size = 10;

                        $text = "@lang('$PAGE_NUM из $PAGE_COUNT')";

                        $y = $pdf->get_height() - 20 - $fontMetrics->get_font_height($font, $size);
                        $x = $pdf->get_width() / 2 - $fontMetrics->get_text_width($text, $font, $size) / 2;

                        $pdf->text($x, $y, $text, $font, $size, array(0,0,0), 0, 0, 0);

                        @if($print)
                            for($i = 0; $i < count($GLOBALS["page_colors"]); $i++) {
                                $page_color = $GLOBALS["page_colors"][$i];
                                if($page_color[0] <= $PAGE_NUM &&
                                    (!isset($GLOBALS["page_colors"][$i + 1]) ||
                                    $GLOBALS["page_colors"][$i + 1][0] > $PAGE_NUM)
                                ) {
                                    $pdf->filled_rectangle(0, 0, 595, 20, $page_color[1]);
                                    break;
                                }
                            }
                        @endif
                    }
                ');
                $GLOBALS['chapters'] = array();
                $GLOBALS['colors'] = [
                    array(255, 255, 255),
                    array(255, 0, 0),
                    array(255, 255, 0),
                    array(0, 255, 255),
                    array(0, 0, 255),
                    array(255, 0, 255)
                ];
                $GLOBALS['page_colors'] = [
                    [2, $GLOBALS['colors'][0]]
                ];
            }
        </script>

        <div style="height: 26.6cm; border: 1px solid #000;">
            <h1 style="height: 1cm; margin: 11cm 0; text-align: center">
                {{ $session->name }}
            </h1>

            <div style="font-size: 10px; text-align: center;">@lang('Версия от :date', ['date' => Carbon::now()])</div>
        </div>

        @if($print)
            <div style="clear: both; page-break-before: always;"></div>
        @endif

        <div style="clear: both; page-break-before: always;"></div>
        <h2>@lang('Содержание')</h2>

        <dl class="index">
            <div>
                <dt><a href="#ch1" class="strong">1. @lang('Общие сведения')</a></dt>
                <dd>CH1</dd>
            </div>

            <div>
                <dt><a href="#ch2" class="strong">2. @lang('Программа тренинга')</a></dt>
                <dd>CH2</dd>
            </div>

            @php
            $chapters = 3;
            $offset = $chapters;
            $counter = 0;
            $any_questions = false;
            @endphp

            @foreach($dataset->actions as $index => $action)
                @php
                if(empty($action->text) || empty($action->text->ru)) {
                    continue;
                }

                $items = collect($dataset->items)
                    ->filter(function($item) use ($action) {
                        return isset($item->action) &&
                            $item->action == $action->id;
                    });

                $has_knowledes = !!$items
                    ->first(function($item) use ($action) {
                        return $item->key &&
                            strpos($item->key, 'У') === false &&
                            isset($item->knowledge) &&
                            $item->knowledge;
                    });

                $has_skills = !!$items
                    ->first(function($item) use ($action) {
                        return !$item->key ||
                            strpos($item->key, 'У') !== false;
                    });

                $has_questions = !!$items
                    ->first(function($item) use ($action) {
                        return $item->questions &&
                            collect($item->questions)
                                ->first(function($question) {
                                    return isset($question->text->ru);
                                });
                    });

                if($has_questions) {
                    $any_questions = true;
                }

                $counter = 0;
                @endphp

                <div>
                    <dt><a href="#ch{{ $offset + $counter }}" class="strong">{{ $index + $chapters }}. {{ $action->text->ru }}</a></dt>
                    <dd>CH{{ $offset + $counter }}</dd>
                </div>

                @if($has_knowledes)
                    <div>
                        <dt><a href="#ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Теоретическая часть')</a></dt>
                        <dd>CH{{ $offset + $counter }}</dd>
                    </div>

                    @php $counter++; @endphp
                @endif

                @if($has_skills)
                    <div>
                        <dt><a href="#ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Практические навыки')</a></dt>
                        <dd>CH{{ $offset + $counter }}</dd>
                    </div>

                    @php $counter++; @endphp
                @endif

                @if(count($action->steps))
                    <div>
                        <dt><a href="#ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Сценарий')</a></dt>
                        <dd>CH{{ $offset + $counter }}</dd>
                    </div>

                    @php $counter++; @endphp
                @endif

                @if(count($action->materials))
                    <div>
                        <dt><a href="#ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Вспомогательные материалы')</a></dt>
                        <dd>CH{{ $offset + $counter }}</dd>
                    </div>

                    @php $counter++; @endphp
                @endif

                @if($has_questions)
                    <div>
                        <dt><a href="#ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Вопросы для оценки знаний')</a></dt>
                        <dd>CH{{ $offset + $counter }}</dd>
                    </div>

                    @php $counter++; @endphp
                @endif

                @if($notes)
                    <div>
                        <dt><a href="#ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Заметки')</a></dt>
                        <dd>CH{{ $offset + $counter }}</dd>
                    </div>

                    @php $counter++; @endphp
                @endif

                @php $offset += $counter; @endphp
            @endforeach

            @php
            $chapter = $chapters + count($dataset->actions);
            $offset2 = $offset;
            @endphp

            @if($any_questions)
                <div>
                    <dt><a href="#ch{{ $offset }}" class="strong">{{ $chapter }}. @lang('Раздаточный материал для проведения тестирования')</a></dt>
                    <dd>CH{{ $offset }}</dd>
                </div>
            @endif

            @foreach($dataset->actions as $index => $action)
                @php
                if(empty($action->text) || empty($action->text->ru)) {
                    continue;
                }

                $counter = 0;

                $items = collect($dataset->items)
                    ->filter(function($item) use ($action) {
                        return isset($item->action) &&
                            $item->action == $action->id;
                    });

                $has_questions = !!$items
                    ->first(function($item) use ($action) {
                        return $item->questions &&
                            count($item->questions);
                    });

                if(!$has_questions) {
                    continue;
                }
                @endphp

                <div>
                    <dt><a href="#ch{{ $offset2 + $counter }}" class="strong">{{ $chapter }}.{{ $index + 1 }}. {{ $action->text->ru }}</a></dt>
                    <dd>CH{{ $offset2 + $counter }}</dd>
                </div>

                <div>
                    <dt><a href="#ch{{ $offset2 + $counter }}">{{ $chapter }}.{{ $index + 1 }}.1. @lang('Вариант А')</a></dt>
                    <dd>CH{{ $offset2 + $counter }}</dd>
                </div>

                @php $counter++; @endphp

                <div>
                    <dt><a href="#ch{{ $offset2 + $counter }}">{{ $chapter }}.{{ $index + 1 }}.1.1. @lang('Ответы')</a></dt>
                    <dd>CH{{ $offset2 + $counter }}</dd>
                </div>

                @php $counter++; @endphp

                <div>
                    <dt><a href="#ch{{ $offset2 + $counter }}">{{ $chapter }}.{{ $index + 1 }}.2. @lang('Вариант Б')</a></dt>
                    <dd>CH{{ $offset2 + $counter }}</dd>
                </div>

                @php $counter++; @endphp

                <div>
                    <dt><a href="#ch{{ $offset2 + $counter }}">{{ $chapter }}.{{ $index + 1 }}.2.1 @lang('Ответы')</a></dt>
                    <dd>CH{{ $offset2 + $counter }}</dd>
                </div>

                @php $offset2 += $counter + 1; @endphp
            @endforeach
        </dl>

        @if(0)
            <script type="text/php">
                $GLOBALS['chapters'] = range(0, 100);
            </script>
        @else

        <div style="page-break-before: always;"></div>
        <script type="text/php">
            $GLOBALS['chapters'][] = $pdf->get_page_number();
        </script>
        <h2 id="ch1">1. @lang('Общие сведения')</h2>

        @if(!empty($session->info['description']))
            <p>{!! nl2br(e($session->info['description'])) !!}</p>
        @endif

        @php
        $sources = collect($dataset->sources)->whereIn(
            'id',
            collect($dataset->knowledges)
                ->pluck('selections')
                ->flatten()
                ->pluck('ru')
                ->flatten()
                ->pluck('source')
                ->unique()
        );

        $skills = collect($dataset->items)->filter(function($item) {
            return !$item->key || strpos($item->key, 'У') === 0;
        })->count();
        @endphp

        <h4>@lang('Объём информации')</h4>
        <p>
            @lang('Тренинг позволяет обучить и оценить усвоение :knowledges знаний', [
                'knowledges' => collect($dataset->items)->filter(function($item) {
                    return !$item->key || strpos($item->key, 'У') !== 0;
                })->count()
            ])

            @if(count($sources))
                @lang('из :sources источников', ['sources' => count($sources)])
            @endif

            @if($skills)
                @lang('и :skills практических навыков', ['skills' => $skills])
            @endif
        </p>

        @if(!empty($session->info['audience']))
            <h4>@lang('Целевая аудитория')</h4>
            <p>{!! nl2br(e($session->info['audience'])) !!}</p>
        @endif

        @if(!empty($session->info['threshold']))
            <h4>@lang('Порог вхождения')</h4>
            <p>{!! nl2br(e($session->info['threshold'])) !!}</p>
        @endif

        @if(!empty($session->info['requirements']))
            <h4>@lang('Технические требования')</h4>
            <p>{!! nl2br(e($session->info['requirements'])) !!}</p>
        @endif

        @php $users = $session->users('author')->get(); @endphp
        @if($users->count() || !empty($session->info['authors']))
            <h4>@lang('Авторы')</h4>
            <p>{{ !empty($session->info['authors']) ? nl2br($session->info['authors']) : $users->pluck('fio')->implode(', ') }}</p>
        @endif

        @php $users = $session->users('reviewer')->get(); @endphp
        @if($users->count() || !empty($session->info['reviewers']))
            <h4>@lang('Рецензенты')</h4>
            <p>{{ !empty($session->info['reviewers']) ? nl2br($session->info['reviewers']) :  $users->pluck('fio')->implode(', ') }}</p>
        @endif

        @if(count($sources))
            <h4>@lang('Список источников')</h4>
            <ul>
                @foreach($sources as $source)
                    <li>{{ $source->name->ru }}</li>
                @endforeach
            </ul>
        @endif

        <div style="page-break-before: always;"></div>
        <script type="text/php">
            $GLOBALS['chapters'][] = $pdf->get_page_number();
        </script>
        <h2 id="ch2">2. @lang('Программа')</h2>

        <table width="100%">
            <tr>
                <th width="100%">@lang('Тема')</th>
                <th>@lang('Форма проведения')</th>
                <th>@lang('Время')</th>
            </tr>
            @foreach($dataset->actions as $index => $action)
                @php
                if(empty($action->text) || empty($action->text->ru)) {
                    continue;
                }
                @endphp
                <tr>
                    {{-- <td align="center">{{ $index + 1 }}</td> --}}
                    <td>{{ $action->text->ru }}</td>
                    <td>
                        @if(isset($action->format) && $action->format)
                            {{ [
                                    __('Мозговой штурм'),
                                    __('Малые группы'),
                                    __('Аудиовизульная презентация'),
                                    __('Ситуационные задачи'),
                                    __('Устная презентация'),
                                    __('Ролевые игры')
                            ][$action->format - 1] }}
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ gmdate('H:i:s', $action->time) }}</td>
                </tr>
            @endforeach
        </table>

        @php
        $offset = $chapters;
        @endphp

        @foreach($dataset->actions as $index => $action)
            @php
            if(empty($action->text) || empty($action->text->ru)) {
                continue;
            }

            if($GLOBALS['pdf_first_pass']) {
                $GLOBALS['pdf_insert_pages'][$index] = [];
            }

            $counter = 0;
            @endphp

            @php
            $items = collect($dataset->items)
                ->filter(function($item) use ($action) {
                    return isset($item->action) &&
                        $item->action == $action->id &&
                        strpos($item->key, 'У') === false &&
                        isset($item->knowledge) &&
                        $item->knowledge;
                });
            @endphp

            @if(count($items))
                <div style="page-break-before: always;"></div>

                @if(!$GLOBALS['pdf_first_pass'] && !empty($GLOBALS['pdf_insert_pages'][$index][0]))
                    <div style="page-break-before: always;"></div>
                @endif

                <script type="text/php">
                    $page = $pdf->get_page_number();

                    if($GLOBALS['pdf_first_pass'] && !(($page + $GLOBALS['pdf_page_offset']) % 2)) {
                        $GLOBALS['pdf_insert_pages'][{{ $index }}][0] = true;
                        $GLOBALS['pdf_page_offset']++;
                    }

                    $GLOBALS['page_colors'][] = [$page, $GLOBALS['colors'][1]];
                    $GLOBALS['chapters'][] = $page;
                </script>

                <h2 id="ch{{ $offset + $counter }}">{{ $action->text->ru }}</h2>
                <h3>{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Теоретическая часть')</h3>

                @php $counter++; @endphp

                <table>
                    <tr>
                        <th>@lang('Знание')</th>
                        <th>@lang('Содержание (знания)')</th>
                    </tr>

                    @php $alt = true; @endphp

                    @foreach($items as $item)
                        @php $knowledge = collect($dataset->knowledges)->where('id', $item->knowledge)->first(); @endphp

                        <tr {!! $alt = !$alt ? 'class="alt"' : '' !!}>
                            <td>{{ $knowledge->name->ru }}</td>
                            <td>{{ $knowledge->adapted->ru }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            @php
            $items = collect($dataset->items)
                ->filter(function($item) use ($action) {
                    return isset($item->action) &&
                        $item->action == $action->id &&
                        (!$item->key || strpos($item->key, 'У') === 0) &&
                        !empty($item->text->ru);
                });
            @endphp

            @if($items->count())
                <div style="page-break-before: always;"></div>

                @if(!$GLOBALS['pdf_first_pass'] && !empty($GLOBALS['pdf_insert_pages'][$index][1]))
                    <div style="page-break-before: always;"></div>
                @endif

                <script type="text/php">
                    $page = $pdf->get_page_number();

                    if($GLOBALS['pdf_first_pass'] && !(($page + $GLOBALS['pdf_page_offset']) % 2)) {
                        $GLOBALS['pdf_insert_pages'][{{ $index }}][1] = true;
                        $GLOBALS['pdf_page_offset']++;
                    }

                    $GLOBALS['page_colors'][] = [$page, $GLOBALS['colors'][2]];
                    $GLOBALS['chapters'][] = $page;
                </script>

                <h2 id="ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Практические навыки')</h2>
                <h3>{{ $action->text->ru }}</h3>

                @php $counter++; @endphp

                <table>
                    <tr>
                        <th>@lang('Навык')</th>
                        <th>@lang('Описание')</th>
                    </tr>

                    @php $alt = true; @endphp
                    @foreach($dataset->items as $item)
                        @if(
                            isset($item->action) &&
                            $item->action == $action->id &&
                            (!$item->key || strpos($item->key, 'У') === 0) &&
                            !empty($item->text->ru)
                        )
                            <tr class="skill{{ $alt = !$alt ? 'alt' : '' }}">
                                <td>{{ $item->name->ru }}</td>
                                <td>{{ $item->text->ru }}</td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            @endif

            @if(count($action->steps))
                <div style="page-break-before: always;"></div>

                @if(!$GLOBALS['pdf_first_pass'] && !empty($GLOBALS['pdf_insert_pages'][$index][2]))
                    <div style="page-break-before: always;"></div>
                @endif

                <script type="text/php">
                    $page = $pdf->get_page_number();

                    if($GLOBALS['pdf_first_pass'] && !(($page + $GLOBALS['pdf_page_offset']) % 2)) {
                        $GLOBALS['pdf_insert_pages'][{{ $index }}][2] = true;
                        $GLOBALS['pdf_page_offset']++;
                    }

                    $GLOBALS['page_colors'][] = [$page, $GLOBALS['colors'][3]];
                    $GLOBALS['chapters'][] = $page;
                </script>

                <h2 id="ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Сценарий')</h2>
                <h3>{{ $action->text->ru }}</h3>

                @php $counter++; @endphp

                <table width="100%">
                    <tr>
                        <th>№</th>
                        <th width="100%">@lang('Действия')</th>
                        <th>@lang('Время')</th>
                    </tr>

                    @php $alt = true; @endphp

                    @foreach($action->steps as $step)
                        <tr {!! $alt = !$alt ? 'class="alt"' : '' !!}>
                            <td>
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                @foreach($step->text as $text)
                                    <p>{{ $text->ru }}</p>
                                @endforeach
                            </td>
                            <td>{{ gmdate('H:i:s', (!empty($step->time) ? $step->time : 0) * 60 + (!empty($step->sec) ? $step->sec : 0)) }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            @if(count($action->materials))
                <div style="page-break-before: always;"></div>

                @if(!$GLOBALS['pdf_first_pass'] && !empty($GLOBALS['pdf_insert_pages'][$index][3]))
                    <div style="page-break-before: always;"></div>
                @endif

                <script type="text/php">
                    $page = $pdf->get_page_number();

                    if($GLOBALS['pdf_first_pass'] && !(($page + $GLOBALS['pdf_page_offset']) % 2)) {
                        $GLOBALS['pdf_insert_pages'][{{ $index }}][3] = true;
                        $GLOBALS['pdf_page_offset']++;
                    }

                    $GLOBALS['page_colors'][] = [$page, $GLOBALS['colors'][4]];
                    $GLOBALS['chapters'][] = $page;
                </script>

                <h2 id="ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Вспомогательные материалы')</h2>
                <h3>{{ $action->text->ru }}</h3>

                @php $counter++; @endphp

                <ol>
                    @foreach($action->materials as $material)
                        @if(isset($material->ru) && !empty($material->ru->text))
                            <li>
                                {{ $material->ru->text }}

                                @if(!empty($material->ru->file))
                                    @php
                                    \Log::debug($material->ru->file->url);
                                    \Log::debug(basename($material->ru->file->url));
                                    @endphp

                                    (<a href="{{ basename($material->ru->file->url) }}">{{ $material->ru->file->name }}</a>)

                                    @php
                                    $path = Storage::path(substr($material->ru->file->url, 9));
                                    $parts = pathinfo($path);
                                    $type = $parts['extension'];
                                    $doc = in_array($type, ['doc', 'docx']);

                                    if(!file_exists($path)) {
                                        \Log::debug($material->ru->file->name);
                                        \Log::debug($path);
                                    }

                                    if($doc && file_exists($path)) {
                                        shell_exec("libreoffice --convert-to pdf --outdir {$parts['dirname']} '$path'");
                                        shell_exec("convert -density 300 '{$parts['dirname']}/{$parts['filename']}.pdf[0]' '{$parts['dirname']}/{$parts['filename']}.jpg'");
                                        $type = 'jpg';
                                        $path = "{$parts['dirname']}/{$parts['filename']}.jpg";
                                    }
                                    @endphp

                                    @if(in_array($type, ['jpeg', 'jpg', 'png']))
                                        @php
                                        $data = 'data:image/' . $type . ';base64,' .
                                            base64_encode(file_get_contents($path));
                                        @endphp

                                        <p><img src="{{ $data }}" /></p>
                                    @endif

                                    @php
                                    if($doc && file_exists($path)) {
                                        unlink($path);
                                        unlink("{$parts['dirname']}/{$parts['filename']}.pdf");
                                    }
                                    @endphp
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ol>
            @endif

            @php
            $has_questions = !!collect($dataset->items)
                ->first(function($item) use ($action) {
                    return isset($item->action) &&
                        $item->action == $action->id &&
                        $item->questions &&
                        collect($item->questions)
                            ->first(function($question) {
                                return isset($question->text->ru);
                            });
                });
            @endphp

            @if($has_questions)
                <div style="page-break-before: always;"></div>

                @if(!$GLOBALS['pdf_first_pass'] && !empty($GLOBALS['pdf_insert_pages'][$index][4]))
                    <div style="page-break-before: always;"></div>
                @endif

                <script type="text/php">
                    $page = $pdf->get_page_number();

                    if($GLOBALS['pdf_first_pass'] && !(($page + $GLOBALS['pdf_page_offset']) % 2)) {
                        $GLOBALS['pdf_insert_pages'][{{ $index }}][4] = true;
                        $GLOBALS['pdf_page_offset']++;
                    }

                    $GLOBALS['page_colors'][] = [$page, $GLOBALS['colors'][5]];
                    $GLOBALS['chapters'][] = $page;
                </script>

                <h2 id="ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Вопросы для оценки знаний')</h2>
                <h3>{{ $action->text->ru }}</h3>

                @php $counter++; @endphp

                <ol>
                    @foreach($dataset->items as $item)
                        @if(isset($item->action) && $item->action == $action->id)
                            @foreach($item->questions as $question)
                                @php
                                $correct_count = 0;

                                foreach($question->options as $option) {
                                    if(isset($option->correct) && $option->correct) {
                                        $correct_count++;
                                    }
                                }
                                @endphp

                                @if(isset($question->text->ru))
                                    <li class="no-page-break">
                                        <strong>{{ $question->text->ru }}</strong> ({{ $correct_count > 1 ? __('несколько ответов') : __('один ответ') }})
                                        <ol>
                                            @foreach($question->options as $option)
                                                @if(isset($option->text->ru))
                                                    <li {!! isset($option->correct) && $option->correct ? 'class="correct"' : '' !!}>{{ $option->text->ru }}</li>
                                                @endif
                                            @endforeach
                                        </ol>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </ol>
            @endif

            @if($notes)
                <div style="page-break-before: always;"></div>

                @if(!$GLOBALS['pdf_first_pass'] && !empty($GLOBALS['pdf_insert_pages'][$index][5]))
                    <div style="page-break-before: always;"></div>
                @endif

                <script type="text/php">
                    $page = $pdf->get_page_number();

                    if($GLOBALS['pdf_first_pass'] && !(($page + $GLOBALS['pdf_page_offset']) % 2)) {
                        $GLOBALS['pdf_insert_pages'][{{ $index }}][5] = true;
                        $GLOBALS['pdf_page_offset']++;
                    }

                    $GLOBALS['page_colors'][] = [$page, $GLOBALS['colors'][0]];
                    $GLOBALS['chapters'][] = $page;
                </script>

                <h2 id="ch{{ $offset + $counter }}">{{ $index + $chapters }}.{{ $counter + 1 }}. @lang('Заметки')</h2>
                <h3>{{ $action->text->ru }}</h3>

                @php $counter++; @endphp
            @endif

            @php $offset += $counter; @endphp
        @endforeach

        @php
        $alpha = range('a', 'z'); //'АБВГДЕЖЗИКЛМНОПРСТУФХЦЧШЩЭЮЯ';
        $offset2 = $offset;
        $chapter = $chapters + count($dataset->actions);
        @endphp

        @foreach($dataset->actions as $index => $action)
            @php
            if(empty($action->text) || empty($action->text->ru)) {
                continue;
            }

            $counter = 0;

            $questions = collect($dataset->items)
                ->where('action', $action->id)
                ->pluck('questions')
                ->flatten()
                ->filter(function($question) {
                    return !empty($question->text->ru);
                });

            if(!count($questions)) {
                continue;
            }
            @endphp

            <div style="page-break-before: always;"></div>

            @if(!$GLOBALS['pdf_first_pass'] && !empty($GLOBALS['pdf_insert_pages']['last']))
                <div style="page-break-before: always;"></div>
            @endif

            <script type="text/php">
                $page = $pdf->get_page_number();

                @if(!$index)
                    if($GLOBALS['pdf_first_pass'] && !(($page + $GLOBALS['pdf_page_offset']) % 2)) {
                        $GLOBALS['pdf_insert_pages']['last'] = true;
                        $GLOBALS['pdf_page_offset']++;
                    }
                @endif

                $GLOBALS['page_colors'][] = [$page, $GLOBALS['colors'][0]];
                $GLOBALS['chapters'][] = $page;
            </script>

            @if(!$index)
                <h2 id="ch{{ $offset2 }}">{{ $chapter }}. @lang('Раздаточный материал для проведения тестирования')</h2>
            @endif

            <h2 @if($index) id="ch{{ $offset2 }}" @endif>{{ $chapter }}.{{ $index + 1 }}.1. @lang('Вариант А')</h2>
            <h3>{{ $action->text->ru }}</h3>

            @php $counter++; @endphp

            @php $answers = []; @endphp

            <ol>
                @foreach($questions->shuffle()->all() as $question)
                    @php
                    $answer = [];
                    $correct_count = 0;

                    shuffle($question->options);

                    foreach($question->options as $option_index => $option) {
                        if(isset($option->correct) && $option->correct) {
                            $correct_count++;
                            $answer[] = $alpha[$option_index]; //mb_substr($alpha, $index, 1);
                        }
                    }

                    $answers[] = $answer;
                    @endphp

                    @if(isset($question->text->ru))
                        <li class="no-page-break">
                            <strong>{{ $question->text->ru }}</strong> ({{ $correct_count > 1 ? __('несколько ответов') : __('один ответ') }})
                            <ol>
                                @foreach($question->options as $option)
                                    @if(isset($option->text->ru))
                                        <li>{{ $option->text->ru }}</li>
                                    @endif
                                @endforeach
                            </ol>
                        </li>
                    @endif
                @endforeach
            </ol>

            <div style="page-break-before: always;"></div>
            <script type="text/php">
                $GLOBALS['chapters'][] = $pdf->get_page_number();
            </script>

            <h2 id="ch{{ $offset2 + $counter++ }}">{{ $chapter }}.{{ $index + 1 }}.1.1. @lang('Ответы (вариант А)')</h2>
            <h3>{{ $action->text->ru }}</h3>

            <ol>
                @foreach($answers as $answer)
                    <li>{{ implode(', ', $answer) }}</li>
                @endforeach
            </ol>

            <div style="page-break-before: always;"></div>
            <script type="text/php">
                $GLOBALS['chapters'][] = $pdf->get_page_number();
            </script>

            <h2 id="ch{{ $offset2 + $counter++ }}">{{ $chapter }}.{{ $index + 1 }}.2. @lang('Вариант Б')</h2>
            <h3>{{ $action->text->ru }}</h3>

            @php $answers = []; @endphp

            <ol>
                @foreach($questions->shuffle()->all() as $question)
                    @php
                    $answer = [];
                    $correct_count = 0;

                    shuffle($question->options);

                    foreach($question->options as $option_index => $option) {
                        if(isset($option->correct) && $option->correct) {
                            $correct_count++;
                            $answer[] = $alpha[$option_index]; //mb_substr($alpha, $index, 1);
                        }
                    }

                    $answers[] = $answer;
                    @endphp

                    @if(isset($question->text->ru))
                        <li class="no-page-break">
                            <strong>{{ $question->text->ru }}</strong> ({{ $correct_count > 1 ? __('несколько ответов') : __('один ответ') }})
                            <ol>
                                @foreach($question->options as $option)
                                    @if(isset($option->text->ru))
                                        <li>{{ $option->text->ru }}</li>
                                    @endif
                                @endforeach
                            </ol>
                        </li>
                    @endif
                @endforeach
            </ol>

            <div style="page-break-before: always;"></div>
            <script type="text/php">
                $GLOBALS['chapters'][] = $pdf->get_page_number();
            </script>

            <h2 id="ch{{ $offset2 + $counter++ }}">{{ $chapter }}.{{ $index + 1 }}.2.1 @lang('Ответы (вариант Б)')</h2>
            <h3>{{ $action->text->ru }}</h3>

            <ol>
                @foreach($answers as $answer)
                    <li>{{ implode(', ', $answer) }}</li>
                @endforeach
            </ol>

            @php $offset2 += $counter; @endphp
        @endforeach

        @endif

        <script type="text/php">
            $cpdf = $pdf->get_cpdf();

            $pages = [];
            foreach($cpdf->objects as $object) {
                if($object['t'] == 'page' && $object['info']['pageNum'] < 10) {
                    $pages[] = $object['info']['contents'][0];
                }
            }

            foreach (array_reverse($GLOBALS['chapters'], true) as $chapter => $page) {
                $template = 'CH'.($chapter + 1);
                $page = (string)$page;
    
                foreach($pages as $object) {
                    $cpdf->objects[$object]['c'] = str_replace(
                        $cpdf->utf8toUtf16BE($template, false),
                        $cpdf->utf8toUtf16BE($page, false),
                        $cpdf->objects[$object]['c']
                    );
                }
            }
        </script>
    </body>
</html>