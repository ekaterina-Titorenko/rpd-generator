<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RpdCurriculumItemController;
use App\Http\Controllers\RpdProgramController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('rpd-programs.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('rpd-programs.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
});

require __DIR__.'/auth.php';