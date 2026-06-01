<?php

namespace Database\Seeders;

use App\Models\RpdControlForm;
use Illuminate\Database\Seeder;

class RpdControlFormSeeder extends Seeder
{
    public function run(): void
    {
        $forms = [
            'Тест',
            'Опрос',
            'Контрольная работа',
            'Самостоятельная работа',
            'Практическая работа',
            'Итоговая практическая работа',
            'Защита проекта',
            'Защита практической работы',
            'Демонстрация результата',
            'Собеседование',
        ];

        foreach ($forms as $form) {
            RpdControlForm::query()->updateOrCreate(
                ['name' => $form],
                [
                    'is_default' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}