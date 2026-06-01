<?php

use App\Http\Controllers\RpdProgramController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('rpd-programs.index');
});

Route::resource('rpd-programs', RpdProgramController::class);