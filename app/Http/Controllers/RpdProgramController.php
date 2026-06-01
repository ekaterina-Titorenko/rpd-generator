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
        //
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
        //
    }

    public function destroy(RpdProgram $rpdProgram)
    {
        //
    }
}