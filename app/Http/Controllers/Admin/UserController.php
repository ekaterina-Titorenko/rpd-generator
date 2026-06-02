<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const DEFAULT_TEMPORARY_PASSWORD = '12345678';

    public function index(Request $request)
    {
        $query = User::query();

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', [
            'defaultPassword' => self::DEFAULT_TEMPORARY_PASSWORD,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['teacher', 'admin'])],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => mb_strtolower($validated['email']),
            'role' => $validated['role'],
            'password' => Hash::make(self::DEFAULT_TEMPORARY_PASSWORD),
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Пользователь создан. Временный пароль: ' . self::DEFAULT_TEMPORARY_PASSWORD);
    }

    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make(self::DEFAULT_TEMPORARY_PASSWORD),
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Пароль пользователя сброшен. Временный пароль: ' . self::DEFAULT_TEMPORARY_PASSWORD);
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($request->user()->id === $user->id, 422, 'Нельзя удалить собственный аккаунт.');

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Пользователь удалён.');
    }
}