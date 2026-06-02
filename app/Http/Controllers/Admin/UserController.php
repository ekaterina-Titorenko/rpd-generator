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
            ->orderByDesc('can_manage_admins')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        return view('admin.users.create', [
            'defaultPassword' => self::DEFAULT_TEMPORARY_PASSWORD,
            'canManageAdmins' => $this->canManageAdmins($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $canManageAdmins = $this->canManageAdmins($request->user());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in($canManageAdmins ? ['teacher', 'admin'] : ['teacher'])],
            'can_manage_admins' => ['nullable', 'boolean'],
        ]);

        $role = $validated['role'];
        $canManageAdminsFlag = $canManageAdmins
            && $role === 'admin'
            && $request->boolean('can_manage_admins');

        User::create([
            'name' => $validated['name'],
            'email' => mb_strtolower($validated['email']),
            'role' => $role,
            'can_manage_admins' => $canManageAdminsFlag,
            'password' => Hash::make(self::DEFAULT_TEMPORARY_PASSWORD),
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Пользователь создан. Временный пароль: ' . self::DEFAULT_TEMPORARY_PASSWORD);
    }

    public function resetPassword(Request $request, User $user)
    {
        abort_unless($this->canManageAdmins($request->user()), 403);

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

        if ($user->role === 'admin') {
            abort_unless($this->canManageAdmins($request->user()), 403);
        }

        if ($user->can_manage_admins) {
            abort_unless($request->user()->email === 'admin@example.com', 403);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Пользователь удалён.');
    }

    private function canManageAdmins(?User $user): bool
    {
        return $user?->role === 'admin' && (bool) $user->can_manage_admins;
    }
}