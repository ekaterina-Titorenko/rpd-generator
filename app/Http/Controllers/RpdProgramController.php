<?php

namespace App\Http\Controllers;

use App\Models\RpdProgram;
use Illuminate\Http\Request;

class RpdProgramController extends Controller
{
    public function index()
    {
        $programs = RpdProgram::query()
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
            'education_form' => 'Занятия проводятся в очном и дистанционном формате с использованием Личного кабинета школьника/абитуриента на сайте Приёмной комиссии РТУ МИРЭА.',

            'study_mode' => "Данная Программа рассчитана на освоение в течение 1-го года учащимися в возрасте от 14 до 18 лет, не зависимо от пола, 1-2 раза в неделю по 2 академических часа. Академический час для обучающихся равен 45 минутам.\n\nТиповой режим занятий:\nПо будням с 16:00 до 17:30, с 17:45 до 19:15. При необходимости могут назначаться дополнительные временные интервалы занятий при соблюдении общего режима обучения.",

            'students_category' => 'Программа предназначена для учащихся, имеющих интерес к содержанию программы и учащихся, мотивированных на участие в региональных и всероссийских соревнованиях и конкурсах.',

            'preparation_requirements' => "К освоению дополнительной общеобразовательной программы допускаются слушатели, обладающие следующими компетенциями:\n· базовыми навыками работы с персональным компьютером и современными информационными технологиями;\n· развитыми логическими способностями и аналитическим мышлением;\n· фундаментальными знаниями по общеобразовательным предметам в соответствии с программой основного общего образования;\n· умением самостоятельно работать с учебной информацией и осваивать новые знания.\nОсобые требования к предварительной подготовке отсутствуют. Программа построена таким образом, чтобы быть доступной для слушателей с различным уровнем начальной подготовки при условии наличия вышеуказанных базовых компетенций.",
        ]));

        return redirect()
            ->route('rpd-programs.show', $program)
            ->with('success', 'РПД создана.');
    }

    public function show(RpdProgram $rpdProgram)
    {
        return view('rpd-programs.show', compact('rpdProgram'));
    }

    public function edit(RpdProgram $rpdProgram)
    {
        return view('rpd-programs.edit', compact('rpdProgram'));
    }

    public function update(Request $request, RpdProgram $rpdProgram)
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
}
