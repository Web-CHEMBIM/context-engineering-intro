<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roles', 'permissions', 'student', 'teacher']);

        // Apply filters
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $users = $query->paginate($perPage);

        return UserResource::collection($users)->additional([
            'meta' => [
                'total_count' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'filters_applied' => $request->only(['role', 'is_active', 'search']),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'is_active' => $request->get('is_active', true),
        ]);

        // Assign roles if provided
        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        $user->load(['roles', 'permissions', 'student', 'teacher']);

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'permissions', 'student', 'teacher']);

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updateData = $request->only([
            'name', 'email', 'phone', 'date_of_birth', 
            'gender', 'is_active'
        ]);

        // Handle password update
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Update roles if provided
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        $user->load(['roles', 'permissions', 'student', 'teacher']);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Soft delete to maintain data integrity
        $user->update(['is_active' => false]);
        
        return response()->json([
            'message' => 'User deactivated successfully'
        ], Response::HTTP_OK);
    }

    /**
     * Restore a deactivated user.
     */
    public function restore(User $user)
    {
        $this->authorize('restore', $user);

        $user->update(['is_active' => true]);

        return response()->json([
            'message' => 'User restored successfully',
            'data' => new UserResource($user)
        ], Response::HTTP_OK);
    }

    /**
     * Get user roles and permissions.
     */
    public function permissions(User $user)
    {
        $this->authorize('view', $user);

        return response()->json([
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'direct_permissions' => $user->permissions->pluck('name'),
        ]);
    }
}