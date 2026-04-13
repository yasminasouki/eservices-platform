<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeOfficeMail;
use App\Models\GovernmentOffice;
use App\Models\OfficeUserAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserManagementController extends Controller
{
    public function officeUsersIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $officeUsers = User::query()
            ->where('role', 'office_user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->with('governmentOffices:id,name')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $offices = GovernmentOffice::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.users.office-users.index', compact('officeUsers', 'offices', 'search'));
    }

    public function officeUsersStore(Request $request)
    {
        foreach (['phone', 'government_office_id', 'role_in_office'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
            'government_office_id' => ['nullable', 'exists:government_offices,id'],
            'role_in_office' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'office_user',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        // Admin-provisioned accounts bypass inbox verification; `verified` middleware requires this.
        $user->markEmailAsVerified();

        Mail::to($user->email)->send(new WelcomeOfficeMail(
            name: $user->name,
            email: $validated['email'],
            password: $validated['password'],
            officeName: ! empty($validated['government_office_id'])
                ? GovernmentOffice::find($validated['government_office_id'])->name
                : 'Not Assigned',
        ));

        if (! empty($validated['government_office_id'])) {
            OfficeUserAssignment::create([
                'government_office_id' => $validated['government_office_id'],
                'user_id' => $user->id,
                'role_in_office' => $validated['role_in_office'] ?? null,
            ]);
        }

        return redirect()
            ->route('admin.office-users.index')
            ->with('success', 'Municipality user created successfully.');
    }

    public function officeUsersEdit(User $user): View
    {
        abort_unless($user->role === 'office_user', 404);

        $user->load('governmentOffices:id,name');
        $offices = GovernmentOffice::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.users.office-users.edit', [
            'user' => $user,
            'offices' => $offices,
        ]);
    }

    public function officeUsersUpdate(Request $request, User $user)
    {
        abort_unless($user->role === 'office_user', 404);

        if ($request->input('password') === '') {
            $request->merge(['password' => null]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'assignments' => ['nullable', 'array'],
            'assignments.*' => ['integer', 'distinct', 'exists:government_offices,id'],
            'role' => ['nullable', 'array'],
            'role.*' => ['nullable', 'string', 'max:100'],
        ]);

        $updates = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $updates['password'] = Hash::make($validated['password']);
            $updates['must_change_password'] = false;
        }

        $user->update($updates);

        $pivot = [];
        foreach ($validated['assignments'] ?? [] as $officeId) {
            $role = $request->input('role.'.$officeId);
            $pivot[(int) $officeId] = [
                'role_in_office' => ($role !== null && $role !== '') ? $role : null,
            ];
        }

        $user->governmentOffices()->sync($pivot);

        return redirect()
            ->route('admin.office-users.edit', $user)
            ->with('success', 'User updated successfully.');
    }

    public function officeUsersSendPasswordReset(User $user)
    {
        abort_unless($user->role === 'office_user', 404);

        $status = Password::sendResetLink(['email' => $user->email]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Password reset link sent to '.$user->email.'.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function officeUsersToggleActive(User $user)
    {
        abort_unless($user->role === 'office_user', 404);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'User status updated successfully.');
    }

    public function citizensIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $citizens = User::query()
            ->where('role', 'citizen')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.citizens.index', compact('citizens', 'search'));
    }

    public function citizensToggleActive(User $user)
    {
        abort_unless($user->role === 'citizen', 404);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Citizen status updated successfully.');
    }
}
