<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Main Dashboard Controller
 * 
 * Handles role-based dashboard redirection
 */
class DashboardController extends Controller
{
    /**
     * Display the appropriate dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();

        // Redirect to role-specific dashboard
        if ($user->hasRole('SuperAdmin') || $user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Teacher')) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->hasRole('Student')) {
            return redirect()->route('student.dashboard');
        }

        // Fallback - should not reach here in normal circumstances
        abort(403, 'Access denied. Please contact administrator.');
    }
}