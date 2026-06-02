<?php

namespace App\Http\Controllers;

use App\Models\RpdProgram;
use Illuminate\Http\Request;
use App\Services\RpdDocxGenerator;

class RpdProgramController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $sort = (string) $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'title',
            'teacher',
            'direction',
            'year',
            'status',
            'created_at',
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $sortColumn = $sort === 'teacher' ? 'teacher_name' : $sort;

        if ($search !== '') {
            $builder = RpdProgram::search($search);

            if ($request->user()->role !== 'admin') {
                $builder->where('user_id', $request->user()->id);
            }

            $builder->orderBy($sortColumn, $direction);

            $ids = $builder
                ->take(1000)
                ->get()
                ->pluck('id')
                ->values();

            $query = RpdProgram::query()
                ->with('user')
                ->whereIn('id', $ids);

            if ($ids->isNotEmpty()) {
                $idsList = $ids->implode(',');

                $query->orderByRaw("CASE id {$ids->map(fn($id,$index) => "WHEN {$id} THEN {$index}")->implode(' ')} END");
            }
        } else {
            $query = RpdProgram::query()
                ->with('user');

            if ($request->user()->role !== 'admin') {
                $query->where('user_id', $request->user()->id);
            }

            if ($sort === 'teacher') {
                $query
                    ->leftJoin('users', 'users.id', '=', 'rpd_programs.user_id')
                    ->select('rpd_programs.*')
                    ->orderBy('users.name', $direction)
                    ->orderBy('rpd_programs.created_at', 'desc');
            } else {
                $query
                    ->orderBy("rpd_programs.{$sort}", $direction)
                    ->orderBy('rpd_programs.created_at', 'desc');
            }
        }

        $rpdPrograms = $query
            ->paginate(15)
            ->withQueryString();

        return view('rpd-programs.index', compact(
            'rpdPrograms',
            'sort',
            'direction'
        ));
    }

    private function directionLabelSearchSql(): string
    {
        return "
        LOWER(
            CASE rpd_programs.direction
                WHEN 'technical' THEN 'техническая technical'
                WHEN 'science' THEN 'естественно-научная естественно научная естественнонаучная science natural'
                WHEN 'social' THEN 'социально-гуманитарная социально гуманитарная социальногуманитарная social humanitarian'
                WHEN 'social_humanitarian' THEN 'социально-гуманитарная социально гуманитарная социальногуманитарная social humanitarian'
                ELSE rpd_programs.direction
            END
        ) LIKE ?
    ";
    }

    public function create()
    {
        return view('rpd-programs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'direction' => ['required', 'in:technical,science,social_humanitarian'],
            'complexity_level' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'smko_code' => ['nullable', 'string', 'max:255'],
            'total_hours' => ['required', 'integer', 'min:1', 'max:1000'],
            'study_period' => ['required', 'string', 'max:255'],
            'students_age' => ['required', 'string', 'max:255'],
            'education_format' => ['required', 'in:offline,online,mixed'],
            'lessons_per_week' => ['required', 'string', 'max:255'],
            'academic_hours_per_lesson' => ['required', 'integer', 'min:1', 'max:12'],
            'academic_hour_minutes' => ['required', 'integer', 'min:30', 'max:60'],
        ]);

        $standardTexts = $this->makeStandardTexts($validated);

        $directionTexts = $this->makeDirectionTexts($validated);

        $rpdProgram = RpdProgram::create(array_merge($validated, $directionTexts, [
            'user_id' => $request->user()->id,

            'education_form' => $standardTexts['education_form'],
            'study_mode' => $standardTexts['study_mode'],

            'students_category' => 'Программа предназначена для учащихся, имеющих интерес к содержанию программы и учащихся, мотивированных на участие в региональных и всероссийских соревнованиях и конкурсах.',

            'preparation_requirements' => "К освоению дополнительной общеобразовательной программы допускаются слушатели, обладающие следующими компетенциями:\n· базовыми навыками работы с персональным компьютером и современными информационными технологиями;\n· развитыми логическими способностями и аналитическим мышлением;\n· фундаментальными знаниями по общеобразовательным предметам в соответствии с программой основного общего образования;\n· умением самостоятельно работать с учебной информацией и осваивать новые знания.\nОсобые требования к предварительной подготовке отсутствуют. Программа построена таким образом, чтобы быть доступной для слушателей с различным уровнем начальной подготовки при условии наличия вышеуказанных базовых компетенций.",
        ]));

        return redirect()
            ->route('rpd-programs.show', $rpdProgram)
            ->with('success', 'РПД создана.');
    }

    public function show(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $rpdProgram->load([
            'curriculumItems.children',
            'curriculumItems.controlForm',
            'authors',
            'resources',
            'contentSections' => fn($query) => $query
                ->whereNotNull('rpd_curriculum_item_id')
                ->orderBy('sort_order'),
        ]);

        $curriculumItems = $rpdProgram->curriculumItems()
            ->with(['children', 'controlForm'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $sections = $rpdProgram->curriculumItems->where('type', 'section');

        $sectionTotalHours = (int) $sections->sum('total_hours');

        $readiness = [
            [
                'title' => 'Учебный план',
                'is_ready' => $sections->isNotEmpty()
                    && $sectionTotalHours === (int) $rpdProgram->total_hours,
                'message' => $sections->isEmpty()
                    ? 'Не добавлены разделы учебного плана.'
                    : (
                        $sectionTotalHours === (int) $rpdProgram->total_hours
                        ? 'Заполнен.'
                        : "Сумма часов по разделам: {$sectionTotalHours} из {$rpdProgram->total_hours}."
                    ),
                'url' => route('rpd-programs.curriculum.index', $rpdProgram),
            ],
            [
                'title' => 'Содержание учебного плана',
                'is_ready' => $sections->isNotEmpty()
                    && $sections->every(function ($section) use ($rpdProgram) {
                        $contentSection = $rpdProgram->contentSections
                            ->firstWhere('rpd_curriculum_item_id', $section->id);

                        return $contentSection && filled($contentSection->content);
                    }),
                'message' => 'Для каждого раздела должно быть заполнено содержание.',
                'url' => route('rpd-programs.content.index', $rpdProgram),
            ],
            [
                'title' => 'Календарный учебный график',
                'is_ready' => $rpdProgram->scheduleItems->isNotEmpty(),
                'message' => 'Нужно сформировать и проверить календарный учебный график.',
                'url' => route('rpd-programs.schedule.index', $rpdProgram),
            ],
            [
                'title' => 'Оценочные материалы',
                'is_ready' => filled($rpdProgram->control_survey_materials)
                    && filled($rpdProgram->final_practical_work_materials)
                    && filled($rpdProgram->project_topics),
                'message' => 'Нужно заполнить три блока оценочных материалов.',
                'url' => route('rpd-programs.assessment.index', $rpdProgram),
            ],
            [
                'title' => 'Литература и интернет-ресурсы',
                'is_ready' => $rpdProgram->resources->where('type', 'main_recommended')->isNotEmpty()
                    && $rpdProgram->resources->where('type', 'additional')->isNotEmpty()
                    && $rpdProgram->resources->where('type', 'internet')->isNotEmpty(),
                'message' => 'Нужен хотя бы один источник в каждом разделе.',
                'url' => route('rpd-programs.resources.index', $rpdProgram),
            ],
            [
                'title' => 'Разработчики',
                'is_ready' => $rpdProgram->authors->isNotEmpty(),
                'message' => 'Нужно указать хотя бы одного разработчика.',
                'url' => route('rpd-programs.authors.index', $rpdProgram),
            ],
        ];

        $isReadyForReview = collect($readiness)->every(fn($item) => $item['is_ready']);

        return view('rpd-programs.show', compact('rpdProgram', 'curriculumItems', 'readiness', 'isReadyForReview'));
    }

    public function edit(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        return view('rpd-programs.edit', compact('rpdProgram'));
    }

    public function update(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'direction' => ['required', 'in:technical,science,social_humanitarian'],
            'complexity_level' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'smko_code' => ['nullable', 'string', 'max:255'],
            'total_hours' => ['required', 'integer', 'min:1', 'max:1000'],
            'study_period' => ['required', 'string', 'max:255'],
            'students_age' => ['required', 'string', 'max:255'],
            'education_format' => ['required', 'in:offline,online,mixed'],
            'lessons_per_week' => ['required', 'string', 'max:255'],
            'academic_hours_per_lesson' => ['required', 'integer', 'min:1', 'max:12'],
            'academic_hour_minutes' => ['required', 'integer', 'min:30', 'max:60'],

        ]);
        $standardTexts = $this->makeStandardTexts($validated);

        $rpdProgram->update(array_merge($validated, $standardTexts));

        return redirect()
            ->route('rpd-programs.show', $rpdProgram)
            ->with('success', 'Общие сведения РПД обновлены.');
    }

    public function destroy(RpdProgram $rpdProgram)
    {
        $rpdProgram->delete();

        return redirect()
            ->route('rpd-programs.index')
            ->with('success', 'РПД удалена.');
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }

    public function submit(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless(
            $request->user()->role === 'teacher'
                && in_array($rpdProgram->status, ['draft', 'revision'], true),
            403
        );


        $errors = $this->validateBeforeSubmit($rpdProgram);

        if (! empty($errors)) {
            return back()
                ->withErrors($errors);
        }


        $rpdProgram->update([
            'status' => 'submitted',
            'review_comment' => null,
        ]);

        return redirect()
            ->route('rpd-programs.show', $rpdProgram)
            ->with('success', 'РПД отправлена на проверку.');
    }

    public function returnForRevision(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($request->user()->role === 'admin', 403);

        $errors = $this->validateBeforeSubmit($rpdProgram);

        if (! empty($errors)) {
            return back()->withErrors($errors);
        }

        $validated = $request->validate([
            'review_comment' => ['required', 'string'],
        ]);

        $rpdProgram->update([
            'status' => 'revision',
            'review_comment' => $validated['review_comment'],
        ]);

        return redirect()
            ->route('rpd-programs.show', $rpdProgram)
            ->with('success', 'РПД возвращена на доработку.');
    }

    public function approve(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($request->user()->role === 'admin', 403);

        $validated = $request->validate([
            'review_comment' => ['nullable', 'string'],
        ]);

        $rpdProgram->update([
            'status' => 'approved',
            'review_comment' => $validated['review_comment'] ?? null,
        ]);

        return redirect()
            ->route('rpd-programs.show', $rpdProgram)
            ->with('success', 'РПД утверждена.');
    }

    public function reject(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($request->user()->role === 'admin', 403);

        $validated = $request->validate([
            'review_comment' => ['required', 'string'],
        ]);

        $rpdProgram->update([
            'status' => 'rejected',
            'review_comment' => $validated['review_comment'],
        ]);

        return redirect()
            ->route('rpd-programs.show', $rpdProgram)
            ->with('success', 'РПД отклонена.');
    }

    private function makeStandardTexts(array $data): array
    {
        $formatText = match ($data['education_format']) {
            'offline' => 'очном формате',
            'online' => 'дистанционном формате',
            'mixed' => 'очном и дистанционном формате',
            default => 'очном и дистанционном формате',
        };

        return [
            'education_form' => "Занятия проводятся в {$formatText} с использованием Личного кабинета школьника/абитуриента на сайте Приёмной комиссии РТУ МИРЭА.",
            'study_mode' => "Данная Программа рассчитана на освоение в течение {$data['study_period']} учащимися в возрасте {$data['students_age']}, не зависимо от пола, {$data['lessons_per_week']} в неделю по {$data['academic_hours_per_lesson']} академических часа",
        ];
    }

    private function validateBeforeSubmit(RpdProgram $rpdProgram): array
    {
        $rpdProgram->load(['curriculumItems.children', 'contentSections', 'scheduleItems',]);

        $errors = [];

        $curriculumItems = $rpdProgram->curriculumItems;

        if ($curriculumItems->isEmpty()) {
            $errors[] = 'Учебный план не заполнен.';
        }

        $sections = $curriculumItems->where('type', 'section');

        if ($sections->isEmpty()) {
            $errors[] = 'В учебном плане должен быть хотя бы один раздел.';
        }

        $sectionTotalHours = (int) $sections->sum('total_hours');

        if ($sectionTotalHours !== (int) $rpdProgram->total_hours) {
            $errors[] = "Сумма часов по разделам ({$sectionTotalHours}) не совпадает с объемом программы ({$rpdProgram->total_hours}).";
        }

        foreach ($curriculumItems as $item) {
            $calculatedTotal = (int) $item->theory_hours + (int) $item->practice_hours;

            if ((int) $item->total_hours !== $calculatedTotal) {
                $errors[] = "В строке «{$item->title}» часы не сходятся: всего {$item->total_hours}, теория + практика = {$calculatedTotal}.";
            }
        }

        foreach ($sections as $section) {
            $children = $section->children;

            $childrenTotal = (int) $children->sum('total_hours');
            $childrenTheory = (int) $children->sum('theory_hours');
            $childrenPractice = (int) $children->sum('practice_hours');
        }

        foreach ($sections as $section) {
            $contentSection = $rpdProgram->contentSections
                ->firstWhere('rpd_curriculum_item_id', $section->id);

            if (! $contentSection) {
                $errors[] = "Для раздела «{$section->title}» не создано содержание. Синхронизируйте содержание с учебным планом.";
                continue;
            }

            if (blank($contentSection->content)) {
                $errors[] = "Не заполнено содержание раздела «{$section->title}».";
            }
        }

        if (blank($rpdProgram->control_survey_materials)) {
            $errors[] = 'Не заполнены материалы для проведения контрольных опросов.';
        }

        if (blank($rpdProgram->final_practical_work_materials)) {
            $errors[] = 'Не заполнены материалы для проведения итоговой практической работы.';
        }

        if (blank($rpdProgram->project_topics)) {
            $errors[] = 'Не заполнены типовые темы проектных работ.';
        }

        $rpdProgram->loadMissing('resources');

        if ($rpdProgram->resources->where('type', 'main_recommended')->isEmpty()) {
            $errors[] = 'Не заполнен список основной рекомендуемой литературы.';
        }

        if ($rpdProgram->resources->where('type', 'additional')->isEmpty()) {
            $errors[] = 'Не заполнена дополнительная литература.';
        }

        if ($rpdProgram->resources->where('type', 'internet')->isEmpty()) {
            $errors[] = 'Не заполнены ресурсы информационно-телекоммуникационной сети Интернет.';
        }

        $rpdProgram->loadMissing('authors');

        if ($rpdProgram->authors->isEmpty()) {
            $errors[] = 'Не указаны разработчики программы.';
        }

        return $errors;
    }

    private function makeDirectionTexts(array $data): array
    {
        $directionName = match ($data['direction']) {
            'technical' => 'технической',
            'science' => 'естественно-научной',
            'social_humanitarian' => 'социально-гуманитарной',
            default => 'технической',
        };

        $directionArea = match ($data['direction']) {
            'technical' => 'технической области',
            'science' => 'естественно-научной области',
            'social_humanitarian' => 'социально-гуманитарной области',
            default => 'технической области',
        };

        $creativity = match ($data['direction']) {
            'technical' => 'техническим творчеством',
            'science' => 'естественно-научным творчеством',
            'social_humanitarian' => 'творчеством',
            default => 'техническим творчеством',
        };

        $firstLearningTask = match ($data['direction']) {
            'technical' => 'Дать представление о различных направлениях развития технологий, а также смежных отраслей IT-направления;',
            'science' => 'Сформировать целостное понимание фундаментальных принципов и закономерностей развития естественных наук в контексте современных информационных технологий и междисциплинарных исследований;',
            'social_humanitarian' => 'Сформировать понимание роли информационных технологий в развитии современного общества и гуманитарных наук;',
            default => 'Дать представление о различных направлениях развития технологий, а также смежных отраслей IT-направления;',
        };

        return [
            'program_description' => "Дополнительная общеобразовательная (общеразвивающая) программа (далее – ДООП, Программа) «{$data['title']}» {$directionName} направленности разработана и утверждена с учетом тенденций развития общества, требующего создания условий для повышения информационно-коммуникативной компетентности ребёнка.",

            'legal_basis' => "Федеральный закон от 29.12.2012 № 273–ФЗ «Об образовании в Российской Федерации»;\nПриказ Министерства просвещения Российской Федерации от 27.07.2022 № 629 «Об утверждении Порядка организации и осуществления образовательной деятельности по дополнительным общеобразовательным программам»;\nКонцепцией развития дополнительного образования детей до 2030 года (Распоряжение Правительства от 31 марта 2022 года № 678-р);\nМетодических рекомендаций по проектированию дополнительных общеразвивающих программ (включая разноуровневые программы) (письмо Минобрнауки России от 18.11.2015 № 09–3242);\nПисьмом Минобрнауки России от 11.12.2006 № 06-1844 «О примерных требованиях к программам дополнительного образования детей»;\nПостановлением Главного государственного санитарного врача Российской Федерации от 28.09.2020 №28 «Об утверждении санитарных правил СП 2.4.2.3648-20» Санитарно-эпидемиологические требования к организациям воспитания и обучения, отдыха и оздоровления детей и молодежи»;\nПорядок организации и осуществления образовательной деятельности по дополнительным образовательным программам РТУ МИРЭА;\nУстав и другие локальные нормативные акты.",

            'relevance' => "Актуальность программы определяется социальной значимостью и направленностью на развитие способностей обучающихся в {$directionArea}, расширение знаний о современных профессиях, высокотехнологичном оборудовании и информационных технологиях.\n\nПрограмма предполагает овладение основами деятельности в {$directionArea}, формирование ценностных ориентиров, даёт возможность выбрать приоритетное направление и максимально реализовать свои способности и интересы, тем самым помогая утвердиться в социуме, что способствует профориентации и гармоничному развитию личности.",

            'goal' => "Развить способности и мотивацию к занятиям {$creativity} обучающихся; расширить их возможности информационно-технической адаптации посредством формирования базы знаний и навыков в области основ современных технологий; достижение обучающимися планируемых метапредметных и предметных результатов; создание комплекса психолого-педагогических мер, направленных на профессиональное самоопределение школьника.",

            'learning_tasks' => [
                'Обеспечение преемственности образовательных программ среднего общего и высшего образования;',
                $firstLearningTask,
                'Сформировать навыки работы с информацией;',
                'Познакомить со способами проектной, исследовательской, научной деятельности;',
                'Познакомить со способами планирования и выполнения учебного процесса;',
                'Научить использовать алгоритмы, применяемые в профессиональной деятельности.',
            ],

            'development_tasks' => [
                "Развивать мотивацию к обучению и познанию в {$directionArea};",
                'Создание условий для развития и самореализации обучающихся;',
                'Развивать память, внимание, логическое, пространственное и аналитическое мышление;',
                'Развивать творческую активность и интерес к наукам.',
            ],

            'planned_results' => [
                'Формирование и развитие творческих способностей обучающихся;',
                'Формирование готовности обучающихся к саморазвитию и непрерывному образованию;',
                'Построение образовательной деятельности с учётом индивидуальных, возрастных, психологических, физиологических особенностей и здоровья обучающихся;',
                'Формирование основ учебной деятельности;',
                "Знакомство с современными технологиями и профессиями {$directionName} направленности.",
            ],

            'personal_competencies' => [
                'Формирование навыков коллективной деятельности;',
                'Формирование установок на постоянное саморазвитие, самовоспитание, профессиональную ориентацию;',
                'Обучающиеся научатся познавать, действовать и ориентироваться в разных жизненных ситуациях, задавать вопросы и/или находить на них ответы, решать практические задачи;',
                'Ознакомятся с многообразием современных информационных технологий, высокотехнологичным оборудованием, видами используемых в работе материалов;',
            ],

            'metasubject_competencies' => [
                'Умение анализировать, оценивать, сравнивать, строить рассуждения;',
                'Формирование умения планировать и оценивать результаты своей деятельности;',
                'Развитие коммуникативных навыков;',
                'Отслеживать и принимать во внимание тренды и тенденции развития различных видов деятельности, учитывать их при постановке собственных целей;',
                'Выдвигать версии решения проблемы, формулировать гипотезы, предвосхищать конечный результат.',
            ],

            'subject_competencies' => [
                'Определять совместно с педагогом цель деятельности на основе определённой проблемы и существующих возможностей;',
                'Формулировать учебные задачи как шаги достижения поставленной цели деятельности;',
                'Определять необходимые действия в соответствии с учебной и познавательной задачей и составлять алгоритм их выполнения;',
                'Обосновывать и осуществлять выбор наиболее эффективных способов решения учебных и познавательных задач;',
                'Составлять план решения проблемы.',
            ],
        ];
    }

    public function print(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless(
            $rpdProgram->status === 'approved' || $request->user()->role === 'admin',
            403
        );

        $rpdProgram->load([
            'curriculumItems.children',
            'contentSections' => fn($query) => $query
                ->whereNotNull('rpd_curriculum_item_id')
                ->orderBy('sort_order'),
            'scheduleItems',
            'resources',
            'authors',
        ]);

        $curriculumItems = $rpdProgram->curriculumItems
            ->whereNull('parent_id')
            ->sortBy('sort_order');

        return view('rpd-programs.print', compact('rpdProgram', 'curriculumItems'));
    }

    public function downloadDocx(Request $request, RpdProgram $rpdProgram, RpdDocxGenerator $generator)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless(
            $rpdProgram->status === 'approved' || $request->user()->role === 'admin',
            403
        );

        $path = $generator->generate($rpdProgram);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
