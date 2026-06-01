<?php

use App\Http\Controllers\RpdCurriculumItemController;
use App\Http\Controllers\RpdProgramController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('rpd-programs.index');
});

Route::resource('rpd-programs', RpdProgramController::class);

Route::prefix('rpd-programs/{rpdProgram}')
    ->name('rpd-programs.')
    ->group(function () {
        Route::get('curriculum', [RpdCurriculumItemController::class, 'index'])
            ->name('curriculum.index');

        Route::post('curriculum', [RpdCurriculumItemController::class, 'store'])
            ->name('curriculum.store');

        Route::put('curriculum/{curriculumItem}', [RpdCurriculumItemController::class, 'update'])
            ->name('curriculum.update');

        Route::delete('curriculum/{curriculumItem}', [RpdCurriculumItemController::class, 'destroy'])
            ->name('curriculum.destroy');
    });