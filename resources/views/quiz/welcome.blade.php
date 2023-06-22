@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h1 class="mb-4">@lang('TB INTILISH - Викторина')</h1>

            <h3 class="mt-4 mb-3 text-danger">@lang('Воспользуйтесь своим умом и сделайте себе подарок к Новому Году!')</h3>

            <p class="lead">@lang('Участвуйте в следующем розыгрыше,') <a href="{{ route('register') }}">@lang('зарегистрировавшись')</a> @lang('или войдя через соцсеть.')</p>
            <p>
                @include('components.social_login')
            </p>

            @include('components.winners')

            <h2 class="mt-5 mb-4">@lang('Об акции <strong class="text-danger">до 24 декабря</strong>')</h2>

            @switch (App::getLocale())
                @case('uz')
                    <p>"INTILISH" NNT RATM TB INTILISH  ta’lim tizimini taqdim etadi. Tizim  sil kasali bo’yicha so’rov/ta’lim olish imkoniyatini beradi. So’rovnomadan  100% bilan o’tgan foydalanuvchiga  so’rovnomaning tegishli darajasida 100% to’plagan boshqa foydalanuvchilar bilan bir qatorda lotereya o’yinlarida ishtirok etish imkoniyati  beriladi.</p>
                    <p>Lotereyada g’olib bo’lganlar taqdirlanadilar (quyiga qarang).</p>
                    <p>So'rovnomdan o'tish va ta’lim olish bo'yicha <a href="{{ route('register') }}">ro'yxatdan o'tish</a> lozim.</p>
                    <p>Ro’yxatdan o’tish jarayonida telefon raqami yoki elektron pochta manzili ko’rsatiladi va ko’rsatilgan elektron pochta manziliga yoki telefon raqamiga jo’natilgan kodni kiritish bilan tasdiqlsh lozim. Shuningdek ijtimoiy tarmoqda mavjud bo’lgan akkaunt orqali ham ro’yxatdan o’tish mumkin.</p>
                    <p>Profilda idenfikatsion ma’lumotlar (pasport bo’yicha mahlumotlar) ko’rsatilishi shart emas va  so’rovnomalarning biridan o’tib,  100% to’plangandan so’ng, lotereya o’yinlarida ishtirok etish uchun shaxsini tasdiqlashi kerak.</p>
                    <p>Idenfikatsion ma’lumotlarsiz ishtirokchilar lotereya o’yinlarida ishtirok etishlariga ruxsat berilmaydi.  Ishtirokchilarning shaxsiy ma’lumotlari sir saqlanadi va boshqalarga berilmaydi.</p>
                    <p>Ro’yxatdan o’tkandan so’ng foydalanuvchi faqat bazoviy darajadagi so’rovnomadan o’tadi va 100% to’plaganda  chuqurlashtirilgan darajaga o’tishi mumkin.  Chuqurlashtirilgan darajada 100% to’plab ishtirokchi so’rovnomaning maxsus darajasiga o’tadi.</p>
                    <p>So’rovnomaning har bir darajasidan o’tib 100%  to’plagan foydalanuvchi, har bir daraja uchun alohida lotereya o’yinida ishtirok etadi.</p>
                    <p>O’yin aktsiyaning har bir kunida soat 9.00 dan keyin o’tkaziladi. So’rovnomaning har qanday darajasidan o’tib, 100% to’plagan foydalanuvchilar kunning 09:00:01 dan 09:00:00 gacha o’yinda ishtirok etadilar.</p>
                    <p>Sovrinli o’yin maxsus, chuqurlashtirilgan va bazoviy darajada 100% to’plagan foydalanuvchilar orasida o’tkaziladi.</p>
                    <p>Aktsiyaning biron bir kunida sovrinni qo’lga kiritish uchun nomzodlar soni o’yinga qo’yilgan sovrinlar sonidan oshsa, tasodifiy tanlov veb resursi   random.org.dan foydalaniladi.</p>
                    <p>Aktsiyaning biron bir kunida sovrinni olish uchun nomzodlar soni o’yinga qo’yilgan sovrinlar sonidan oshmasa, sovrinlar avtomatik tarzda  topshiriladi.</p>
                    <p>Aktsiyaning biron bir kunida sovrinni olish uchun nomzodlar bo’lmagan taqdirda, sovrinlar uchun o’yin bo’lmaydi va sovrin aktsiya tashkilotchilarida qoladi. Bu holatda aktsiya tashkilotchisi aktsiya  kunlarini  uzaytirishlari yoki  qo’shimcha o’yin tashkil etish uchun  o’yin shartlarni o’zgartirishi mumkin.</p>
                    <h4>O’yin</h4>
                    <p>So'rovnomadan o'tish qoidalari:</p>
                    <ul>
                        <li>Ҳar bir savol uchun 1 minut vaqt beriladi</li>
                        <li>Agar ishtirokchi ta’lim jarayonidan o'tmagan bo'lsa, avvalgi so'rovnomadan o'tib, 5 minutdan so'ng yana qayta  so'rovnomadan o'tishi mumkin (vaqt oralig'i aktsiya tashkilotchilari tomonidan o'zgartirilishi mumkin</li>
                        <li>Agar foydalanuvchi keyingi ta’lim jarayonidan o’tkan bo’lsa, avvalgi  so’rovnomani yakunlab, 1 soatdan so’ng  so’rovnomadan qayta o’tishi mumkin</li>
                        <li>Har bir savolni o’rganish uchun 10 minut vaqt beriladi</li>
                    </ul>
                    <p>TB INTILISH ning tao’lim tizimi uch xil til versiyasida amalga oshiriladi:</p>
                    <ul>
                        <li>O'zbek tili (kirill alifbosida)</li>
                        <li>O’zbek tili (lotin  alifbosida)</li>
                        <li>Rus tili</li>
                    </ul>
                    <p>Parallel ravishda  O'zbegim Taronasi 101.00 FM  radio stantsiyasida quyidagi jadval bo’yicha viktorina o’tkaziladi:</p>
                    <ul>
                        <li>14 dekabr – 10:20 va 19:20</li>
                        <li>17 dekabr – 10:20 va 19:20</li>
                        <li>19 dekabr – 10:20 va 19:20</li>
                        <li>21 dekabr – 10:20 va 19:20</li>
                    </ul>
                    <p>Sovrinlarr ro'yxati:</p>
                    <ul>
                        <li>bazoviy darajadan 100% bilano’o’tkan ishtirokchilar orasida o’tkaziladigan o’yin sovrini - 20 – Elektr choynik (1 kunda 2 ta)</li>
                        <li>chuqurlashtirilgan darajadan 100% bilan o'tkan ishtirokchilar orasida o'tkaziladigan o'yin sovrini - 20 – maishiy dazmol (1 kunda 2 ta)</li>
                        <li>maxsus darajadan 100% bilan o'tkan ishtirokchilar orasida o'tkaziladigan o'yin sovrini -  20 – USB 3.0 64 GB flesh  (1 kunda 2ta)</li>
                        <li>O'zbegim Taronasi 101.00 FM 8  orqali o'tkaziladagan viktorina ishtirokchilari g'oliblari uchun  – USB flesh  (1 ta radio viktorina uchun)</li>
                    </ul>
                    <p>Butun aktsiya davomida ishtirokchi faqat bitta sovrin oladi.</p>

                    @break

                @case('uz-cyr')
                    <p>"INTILISH" ННТ РАТМ TB INTILISH  таълим тизимини тақдим этади. Тизим  сил касали бўйича сўров/таълим олиш имкониятини беради. Сўровномадан  100% билан ўтган фойдаланувчига  сўровноманинг тегишли даражасида 100% тўплаган бошқа фойдаланувчилар билан бир қаторда лотерея ўйинларида иштирок этиш имконияти  берилади.</p>
                    <p>Лотереяда ғолиб бўлганлар тақдирланадилар (қуйига қаранг).</p>
                    <p>Сўровномдан ўтиш ва таълим олиш бўйича <a href="{{ route('register') }}">рўйхатдан ўтиш</a> лозим.</p>
                    <p>Рўйхатдан ўтиш жараёнида телефон рақами ёки электрон почта манзили кўрсатилади ва кўрсатилган электрон почта манзилига ёки телефон рақамига жўнатилган кодни киритиш билан тасдиқлш лозим.</p>
                    <p>Шунингдек ижтимоий тармоқда мавжуд бўлган аккаунт орқали ҳам рўйхатдан ўтиш мумкин.</p>
                    <p>Профилда иденфикацион маълумотлар (паспорт бўйича маълумотлар) кўрсатилиши шарт эмас ва  сўровномаларнинг биридан ўтиб,  100% тўплангандан сўнг, лотерея ўйинларида иштирок этиш учун шахсини тасдиқлаши керак.</p>
                    <p>Иденфикацион маълумотларсиз иштирокчилар лотерея ўйинларида иштирок этишларига рухсат берилмайди.  Иштирокчиларнинг шахсий маълумотлари сир сақланади ва бошқаларга берилмайди.</p>
                    <p>Рўйхатдан ўткандан сўнг фойдаланувчи фақат базовий даражадаги сўровномадан ўтади ва 100% тўплаганда  чуқурлаштирилган даража гаўтиши мумкин.  Чуқурлаштирилган даражада 100% тўплаб иштирокчи сўровноманинг махсус даражасига ўтади.</p>
                    <p>Сўровноманинг ҳар бир даражасидан ўтиб 100%  тўплаган фойдаланувчи, ҳар бир даража учун алоҳида лотерея ўйинида иштирок этади.</p>
                    <p>Ўйин акциянинг ҳар бир кунида соат 9.00 дан кейин ўтказилади. Сўровноманинг ҳар қандай даражасидан ўтиб, 100% тўплаган фойдаланувчилар куннинг 09:00:01 дан 09:00:00 гача ўйинда иштирок этадилар.</p>
                    <p>Совринли ўйин махсус, чуқурлаштирилган ва базовий даражада 100% тўплаган фойдаланувчилар орасида ўтказилади.</p>
                    <p>Акциянинг бирон бир кунида совринни қўлга киритиш учун номзодлар сони ўйинга қўйилган совринлар сонидан ошса, тасодифий танлов веб ресурси   random.org.дан фойдаланилади.</p>
                    <p>Акциянинг бирон бир кунида совринни олиш учун номзодлар сони ўйинга қўйилган совринлар сонидан ошмаса, совринлар автоматик тарзда  топширилади.</p>
                    <p>Акциянинг бирон бир кунида совринни олиш учун номзодлар бўлмаган тақдирда, совринлар учун ўйин бўлмайди ва соврин акция ташкилотчиларида қолади. Бу ҳолатда акция ташкилотчиси акция  кунларини  узайтиришлари ёки  қўшимча ўйин ташкил этиш учун  уйин шартларни ўзгартириши мумкин.</p>
                    <h4>Ўйин</h4>
                    <p>Сўровномадан ўтиш қоидалари:</p>
                    <ul>
                        <li>Ҳар бир савол учун 1 минут вақт берилади</li>
                        <li>Агар иштирокчи таълим жараёнидан ўтмаган бўлса, аввалги сўровномадан ўтиб, 5 минутдан сўнг яна қайта  сўровномадан ўтиш мумкин (вақт оралиғи акция ташкилотчилари томонидан ўзгартирилиши мумкин.</li>
                        <li>Агар фойдаланувчи кейинги таълим жараёнидан ўткан бўлса, аввалги  сўровномани якунлаб, 1 соатдан сўнг  сўровномадан қайта ўтиши мумкин.</li>
                        <li>Ҳар бир саволни ўрганиш учун 10 минут вақт берилади.</li>
                    </ul>
                    <p>TB INTILISH нинг таълим тизими уч хил тил версиясида амалга оширилади:</p>
                    <ul>
                        <li>Ўзбек тили (кирилл алифбосида)</li>
                        <li>Узбек тили (лотин  алифбосида)</li>
                        <li>Рус тили</li>
                    </ul>
                    <p>Параллел равишда  Ўзбегим Таронаси 101.00 FM  радио станциясида қуйидаги жадвал бўйича викторина ўтказилади:</p>
                    <ul>
                        <li>14 декабр – 10:20 ва 19:20</li>
                        <li>17 декабр – 10:20 ва 19:20</li>
                        <li>19 декабр – 10:20 ва 19:20</li>
                        <li>21 декабр – 10:20 ва 19:20</li>
                    </ul>
                    <p>Совринларр рўйхати:</p>
                    <ul>
                        <li>базовий даражадан 100% билан ўткан иштирокчилар орасида ўтказиладиган ўйин соврини - 20 – Электр чойник (1 кунда 2 та)</li>
                        <li>чуқурлаштирилган даражадан 100% билан ўткан иштирокчилар орасида ўтказиладиган ўйин соврини - 20 – маиший дазмол (1 кунда 2 та)</li>
                        <li>махсус даражадан 100% билан ўткан иштирокчилар орасида ўтказиладиган ўйин соврини -  20 – USB 3.0 64 GB флеш  (1 кунда 2та)</li>
                        <li>Ўзбегим Таронаси 101.00 FM 8  орқали ўтказиладаган викторина иштирокчилари ғолиблари учун  – USB флеш  (1 та радиовикторина учун)</li>
                    </ul>
                    <p>Бутун акция давомида иштирокчи фақат битта соврин олади.</p>

                    @break

                @default
                    <p>ННО РИОЦ “INTILISH” представляет образовательную систему TB INTILISH. Система предоставляет возможность пройти опрос/обучение по теме туберкулеза. В случае 100% прохождения опроса, пользователю предоставляется возможность принять участие в розыгрыше призов в числе всех участников, набравших 100% в этот день по опроснику данного уровня. Участники, победившие в розыгрыше, будут награждены призами (см. ниже).</p>
                    <p>Для прохождения опроса и обучения следует <a href="{{ route('register') }}">зарегистрироваться</a>.</p>
                    <p>При регистрации нужно указать номер телефона и/или адрес электронной почты, которые будет необходимо подтвердить, пройдя по ссылке в полученном на указанную почту письме или введя код из смс, полученной на указанный номер телефона. Также можно зарегистрироваться через имеющийся аккаунт в социальных сетях.</p>
                    <p>Идентификационные данные (паспортные данные) в профиле не обязательны для заполнения на этапе регистрации и потребуются в обязательном порядке лишь в случае 100% прохождения одного из уровней опросников. Это необходимо, для подтверждения личности пользователя перед приёмом для участия в розыгрыше приза. Участники без идентификационных данных не будут допущены к участию в розыгрыше призов. Личные данные участников конфиденциальны и могут быть предоставлены третьим лицам лишь в установленном законом порядке.</p>
                    <p>После регистрации участнику будет доступен только базовый уровень опросника. В случае 100% прохождения базового уровня опросника, пользователю становится доступен продвинутый уровень опросника. В случае 100% прохождения продвинутого уровня опросника, пользователю становится доступен специализированный уровень опросника.</p>
                    <p>Пользователь в один день, прошедший 100% опрос в каждом из трех уровней опросников, принимает участие в розыгрыше отдельно по каждому уровню опросников.</p>
                    <p>Розыгрыш проводится после 09:00 каждого дня акции. Участие в розыгрыше принимают пользователи, завершившие любой из уровней опросников с результатом 100% в период с 09:00:01 дня, предшествующего розыгрышу по 09:00:00 дня проведения розыгрыша.</p>
                    <p>Розыгрыш призов проводится последовательно среди пользователей, ответивших 100% по опросникам специализированного, продвинутого и базового уровней в перечисленном порядке.</p>
                    <p>В случае если количество кандидатов для розыгрыша приза в любой из дней акции по любому уровню опросников превышает количество разыгрываемых призов используется инструментарий рандомизации ресурса random.org.</p>
                    <p>В случае если количество кандидатов для розыгрыша приза в любой из дней акции по любому уровню опросников не превышает количество разыгрываемых призов, призы присуждаются автоматически.</p>
                    <p>В случае отсутствия кандидатов для розыгрыша приза в любой из дней акции по любому уровню опросников, приз остаётся неразыгранным и остаётся у организатора акции. В этом случае организатор акции оставляет за собой право увеличитть продолжительность акции или иным способом изменить её условия для дополнительного розыгрыша неразыгранных призов.</p>
                    <h4>Розыгрыш</h4>
                    <p>Правила прохождения опроса/обучения:</p>
                    <ul>
                        <li>Во время опроса для каждого вопроса выделяется 1 минута.</li>
                        <li>Возможность повторного прохождения опроса появляется через 5 минут после завершения предыдущего опроса, если участник не проходил обучение (интервал может быть изменён организатором акции).</li>
                        <li>Возможность повторного прохождения опроса появляется через 1 час после завершения предыдущего опроса, если участник прошел последующее обучение (интервал может быть изменён организатором акции).</li>
                        <li>Во время обучения для каждого вопроса выделяется 10 минут.</li>
                    </ul>
                    <p>Образовательная система TB INTILISH доступна в трех языковых версиях:</p>
                    <ul>
                        <li>Узбекский язык (кириллица)</li>
                        <li>Узбекский язык (латиница)</li>
                        <li>Русский язык</li>
                    </ul>
                    <p>Параллельно на радиостанции Узбегим Таронаси 101,00 FM проводится викторина по следующему расписанию:</p>
                    <ul>
                        <li>14 декабря – 10:20 и 19:20</li>
                        <li>17 декабря – 10:20 и 19:20</li>
                        <li>19 декабря – 10:20 и 19:20</li>
                        <li>21 декабря – 10:20 и 19:20</li>
                    </ul>
                    <p>Список призов:</p>
                    <ul>
                        <li>20 – Электрический чайник (2 в день) среди участников 100% прошедших базовый уровень опросника.
                        <li>20 – Бытовой утюг (2 в день) среди участников 100% прошедших продвинутый уровень опросника
                        <li>20 – USB 3.0 64 GB флеш накопитель (2 в день) среди участников 100% прошедших специализированный уровень опросника.
                        <li>8 – USB флеш накопитель (1 за одну радио викторину) за участие в радио викторине на радиостанции Узбегим Таронаси 101.00 FM
                    </ul>

                    <p>За весь период акции, один человек может получить только один приз!</p>
            @endswitch
        </div>
    </div>
</div>
@endsection
