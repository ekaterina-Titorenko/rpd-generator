<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RpdAssessmentItemController;
use App\Http\Controllers\RpdAuthorController;
use App\Http\Controllers\RpdContentSectionController;
use App\Http\Controllers\RpdCurriculumItemController;
use App\Http\Controllers\RpdProgramController;
use App\Http\Controllers\RpdResourceController;
use App\Http\Controllers\RpdScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('rpd-programs.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('rpd-programs.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('password/change', [ForcePasswordChangeController::class, 'edit'])
        ->name('password.force.edit');

    Route::patch('password/change', [ForcePasswordChangeController::class, 'update'])
        ->name('password.force.update');

    Route::middleware('password.changed')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::patch('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::delete('/profile', [ProfileController::class, 'destroy'])
            ->name('profile.destroy');

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

        Route::prefix('admin')
            ->name('admin.')
            ->middleware('role:admin')
            ->group(function () {
                Route::get('users', [AdminUserController::class, 'index'])
                    ->name('users.index');

                Route::get('users/create', [AdminUserController::class, 'create'])
                    ->name('users.create');

                Route::post('users', [AdminUserController::class, 'store'])
                    ->name('users.store');

                Route::patch('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])
                    ->name('users.reset-password');

                Route::delete('users/{user}', [AdminUserController::class, 'destroy'])
                    ->name('users.destroy');
            });

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

                Route::get('authors', [RpdAuthorController::class, 'index'])
                    ->name('authors.index');

                Route::post('authors', [RpdAuthorController::class, 'store'])
                    ->name('authors.store');

                Route::put('authors/{author}', [RpdAuthorController::class, 'update'])
                    ->name('authors.update');

                Route::delete('authors/{author}', [RpdAuthorController::class, 'destroy'])
                    ->name('authors.destroy');

                Route::get('schedule', [RpdScheduleController::class, 'index'])
                    ->name('schedule.index');

                Route::patch('schedule/weeks', [RpdScheduleController::class, 'updateWeeks'])
                    ->name('schedule.weeks.update');

                Route::post('schedule/generate', [RpdScheduleController::class, 'generate'])
                    ->name('schedule.generate');

                Route::put('schedule', [RpdScheduleController::class, 'update'])
                    ->name('schedule.update');

                Route::get('print', [RpdProgramController::class, 'print'])
                    ->name('print');

                Route::get('download-docx', [RpdProgramController::class, 'downloadDocx'])
                    ->name('download-docx');
            });
    });
});

require __DIR__ . '/auth.php';
