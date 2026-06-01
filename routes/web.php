<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RpdCurriculumItemController;
use App\Http\Controllers\RpdProgramController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RpdContentSectionController;
use App\Http\Controllers\RpdAssessmentItemController;
use App\Http\Controllers\RpdResourceController;

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
    Route::patch('rpd-programs/{rpdProgram}/submit', [RpdProgramController::class, 'submit'])
        ->name('rpd-programs.submit');

    Route::patch('rpd-programs/{rpdProgram}/return-for-revision', [RpdProgramController::class, 'returnForRevision'])
        ->middleware('role:admin')
        ->name('rpd-programs.return-for-revision');

    Route::patch('rpd-programs/{rpdProgram}/approve', [RpdProgramController::class, 'approve'])
        ->middleware('role:admin')
        ->name('rpd-programs.approve');

    Route::patch('rpd-programs/{rpdProgram}/reject', [RpdProgramController::class, 'reject'])
        ->middleware('role:admin')
        ->name('rpd-programs.reject');

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

            Route::get('content', [RpdContentSectionController::class, 'index'])
                ->name('content.index');

            Route::post('content/sync', [RpdContentSectionController::class, 'sync'])
                ->name('content.sync');

            Route::put('content/{contentSection}', [RpdContentSectionController::class, 'update'])
                ->name('content.update');

            Route::get('assessment', [RpdAssessmentItemController::class, 'index'])
                ->name('assessment.index');

            Route::put('assessment', [RpdAssessmentItemController::class, 'update'])
                ->name('assessment.update');

            Route::get('resources', [RpdResourceController::class, 'index'])
                ->name('resources.index');

            Route::post('resources', [RpdResourceController::class, 'store'])
                ->name('resources.store');

            Route::put('resources/{resource}', [RpdResourceController::class, 'update'])
                ->name('resources.update');

            Route::delete('resources/{resource}', [RpdResourceController::class, 'destroy'])
                ->name('resources.destroy');

            Route::post('resources/bulk', [RpdResourceController::class, 'bulkStore'])
                ->middleware('role:admin')
                ->name('resources.bulk-store');
        });
});

require __DIR__ . '/auth.php';
