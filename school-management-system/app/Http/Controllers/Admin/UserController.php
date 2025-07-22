<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * User Management Controller - SuperAdmin Only
 * 
 * Handles CRUD operations for users with role management
 */
class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'student', 'teacher'])
            ->orderBy('created_at', 'desc');

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);
        $roles = Role::all();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'User Management', 'url' => route('users.index')]
        ];

        return view('admin.users.index', compact('users', 'roles', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'User Management', 'url' => route('users.index')],
            ['title' => 'Create User', 'url' => route('users.create')]
        ];

        return view('admin.users.create', compact('roles', 'breadcrumbs'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
            
            // Student specific fields
            'student_id' => ['required_if:role,Student', 'nullable', 'string', 'max:20', 'unique:students,student_id'],
            'admission_date' => ['required_if:role,Student', 'nullable', 'date'],
            
            // Teacher specific fields
            'teacher_id' => ['required_if:role,Teacher', 'nullable', 'string', 'max:20', 'unique:teachers,teacher_id'],
            'employee_id' => ['required_if:role,Teacher', 'nullable', 'string', 'max:20', 'unique:teachers,employee_id'],
            'department' => ['required_if:role,Teacher', 'nullable', 'string', 'max:100'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'hire_date' => ['required_if:role,Teacher', 'nullable', 'date'],
        ]);

        DB::transaction(function () use ($request) {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'is_active' => $request->boolean('is_active', true),
                'email_verified_at' => now(),
            ]);

            // Assign role
            $user->assignRole($request->role);

            // Create role-specific profile
            if ($request->role === 'Student') {
                Student::create([
                    'user_id' => $user->id,
                    'student_id' => $request->student_id,
                    'admission_date' => $request->admission_date,
                    'academic_year_id' => \App\Models\AcademicYear::current()->first()?->id,
                    'status' => 'active'
                ]);
            } elseif ($request->role === 'Teacher') {
                Teacher::create([
                    'user_id' => $user->id,
                    'teacher_id' => $request->teacher_id,
                    'employee_id' => $request->employee_id,
                    'department' => $request->department,
                    'salary' => $request->salary,
                    'hire_date' => $request->hire_date,
                    'status' => 'active'
                ]);
            }
        });

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'student.schoolClasses', 'teacher.schoolClasses', 'teacher.subjects']);

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'User Management', 'url' => route('users.index')],
            ['title' => $user->name, 'url' => route('users.show', $user)]
        ];

        return view('admin.users.show', compact('user', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load(['roles', 'student', 'teacher']);
        $roles = Role::all();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'User Management', 'url' => route('users.index')],
            ['title' => $user->name, 'url' => route('users.show', $user)],
            ['title' => 'Edit', 'url' => route('users.edit', $user)]
        ];

        return view('admin.users.edit', compact('user', 'roles', 'breadcrumbs'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($request, $user) {
            // Update user
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'is_active' => $request->boolean('is_active', true),
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Update role if changed
            if (!$user->hasRole($request->role)) {
                $user->syncRoles([$request->role]);
            }
        });

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of own account
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Soft delete instead of hard delete to maintain data integrity
        $user->update(['is_active' => false]);

        return redirect()->route('users.index')
            ->with('success', 'User deactivated successfully.');
    }

    /**
     * Restore a deactivated user.
     */
    public function restore(User $user)
    {
        $user->update(['is_active' => true]);

        return redirect()->route('users.index')
            ->with('success', 'User reactivated successfully.');
    }
}