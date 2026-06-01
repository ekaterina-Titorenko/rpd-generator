<?php

namespace App\Http\Controllers;

use App\Models\RpdProgram;
use Illuminate\Http\Request;

class RpdProgramController extends Controller
{
    public function index(Request $request)
    {
        $programs = RpdProgram::query()
            ->when($request->user()->role === 'teacher', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest()
            ->get();

        return view('rpd-programs.index', compact('programs'));
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
        ]);

        $program = RpdProgram::create(array_merge($validated, [
            'user_id' => $request->user()->id,
            'education_form' => 'Занятия проводятся в очном и дистанционном формате с использованием Личного кабинета школьника/абитуриента на сайте Приёмной комиссии РТУ МИРЭА.',

            'study_mode' => "Данная Программа рассчитана на освоение в течение 1-го года учащимися в возрасте от 14 до 18 лет, не зависимо от пола, 1-2 раза в неделю по 2 академических часа. Академический час для обучающихся равен 45 минутам.\n\nТиповой режим занятий:\nПо будням с 16:00 до 17:30, с 17:45 до 19:15. При необходимости могут назначаться дополнительные временные интервалы занятий при соблюдении общего режима обучения.",

            'students_category' => 'Программа предназначена для учащихся, имеющих интерес к содержанию программы и учащихся, мотивированных на участие в региональных и всероссийских соревнованиях и конкурсах.',

            'preparation_requirements' => "К освоению дополнительной общеобразовательной программы допускаются слушатели, обладающие следующими компетенциями:\n· базовыми навыками работы с персональным компьютером и современными информационными технологиями;\n· развитыми логическими способностями и аналитическим мышлением;\n· фундаментальными знаниями по общеобразовательным предметам в соответствии с программой основного общего образования;\n· умением самостоятельно работать с учебной информацией и осваивать новые знания.\nОсобые требования к предварительной подготовке отсутствуют. Программа построена таким образом, чтобы быть доступной для слушателей с различным уровнем начальной подготовки при условии наличия вышеуказанных базовых компетенций.",
        ]));

        return redirect()
            ->route('rpd-programs.show', $program)
            ->with('success', 'РПД создана.');
    }

    public function show(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        return view('rpd-programs.show', compact('rpdProgram'));
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
        ]);

        $rpdProgram->update($validated);

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
}
