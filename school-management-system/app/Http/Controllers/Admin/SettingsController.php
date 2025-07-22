<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Settings Controller
 * 
 * Handles system settings and configuration management
 */
class SettingsController extends Controller
{
    /**
     * Display the system settings.
     */
    public function index()
    {
        // Get current settings from cache or default values
        $settings = [
            // School Information
            'school_name' => Cache::get('settings.school_name', 'School Management System'),
            'school_address' => Cache::get('settings.school_address', ''),
            'school_phone' => Cache::get('settings.school_phone', ''),
            'school_email' => Cache::get('settings.school_email', ''),
            'school_website' => Cache::get('settings.school_website', ''),
            'school_logo' => Cache::get('settings.school_logo', ''),
            
            // Academic Settings
            'academic_year_start_month' => Cache::get('settings.academic_year_start_month', 9), // September
            'grading_system' => Cache::get('settings.grading_system', 'letter'), // letter, percentage, gpa
            'passing_grade' => Cache::get('settings.passing_grade', 60),
            'max_absences_allowed' => Cache::get('settings.max_absences_allowed', 20),
            
            // System Settings
            'timezone' => Cache::get('settings.timezone', 'UTC'),
            'date_format' => Cache::get('settings.date_format', 'Y-m-d'),
            'time_format' => Cache::get('settings.time_format', 'H:i'),
            'pagination_limit' => Cache::get('settings.pagination_limit', 20),
            
            // Email Settings
            'smtp_host' => Cache::get('settings.smtp_host', ''),
            'smtp_port' => Cache::get('settings.smtp_port', 587),
            'smtp_username' => Cache::get('settings.smtp_username', ''),
            'smtp_password' => Cache::get('settings.smtp_password', ''),
            'smtp_encryption' => Cache::get('settings.smtp_encryption', 'tls'),
            'mail_from_address' => Cache::get('settings.mail_from_address', ''),
            'mail_from_name' => Cache::get('settings.mail_from_name', ''),
            
            // Security Settings
            'session_lifetime' => Cache::get('settings.session_lifetime', 120),
            'password_min_length' => Cache::get('settings.password_min_length', 8),
            'login_attempts' => Cache::get('settings.login_attempts', 5),
            'account_lockout_time' => Cache::get('settings.account_lockout_time', 15),
            
            // Notification Settings
            'enable_email_notifications' => Cache::get('settings.enable_email_notifications', true),
            'enable_sms_notifications' => Cache::get('settings.enable_sms_notifications', false),
            'notify_new_enrollments' => Cache::get('settings.notify_new_enrollments', true),
            'notify_grade_changes' => Cache::get('settings.notify_grade_changes', true),
            
            // Feature Flags
            'enable_parent_portal' => Cache::get('settings.enable_parent_portal', false),
            'enable_online_payments' => Cache::get('settings.enable_online_payments', false),
            'enable_attendance_tracking' => Cache::get('settings.enable_attendance_tracking', true),
            'enable_grade_book' => Cache::get('settings.enable_grade_book', true),
        ];

        // System information
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_type' => Config::get('database.default'),
            'storage_path' => storage_path(),
            'cache_driver' => Config::get('cache.default'),
            'queue_driver' => Config::get('queue.default'),
        ];

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Settings', 'url' => route('settings')]
        ];

        return view('admin.settings.index', compact('settings', 'systemInfo', 'breadcrumbs'));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            // School Information
            'school_name' => ['required', 'string', 'max:255'],
            'school_address' => ['nullable', 'string', 'max:500'],
            'school_phone' => ['nullable', 'string', 'max:20'],
            'school_email' => ['nullable', 'email', 'max:255'],
            'school_website' => ['nullable', 'url', 'max:255'],
            
            // Academic Settings
            'academic_year_start_month' => ['required', 'integer', 'min:1', 'max:12'],
            'grading_system' => ['required', 'string', 'in:letter,percentage,gpa'],
            'passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_absences_allowed' => ['required', 'integer', 'min:0', 'max:365'],
            
            // System Settings
            'timezone' => ['required', 'string'],
            'date_format' => ['required', 'string'],
            'time_format' => ['required', 'string'],
            'pagination_limit' => ['required', 'integer', 'min:5', 'max:100'],
            
            // Email Settings
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            
            // Security Settings
            'session_lifetime' => ['required', 'integer', 'min:15', 'max:1440'],
            'password_min_length' => ['required', 'integer', 'min:6', 'max:32'],
            'login_attempts' => ['required', 'integer', 'min:3', 'max:10'],
            'account_lockout_time' => ['required', 'integer', 'min:5', 'max:60'],
            
            // Notification Settings
            'enable_email_notifications' => ['boolean'],
            'enable_sms_notifications' => ['boolean'],
            'notify_new_enrollments' => ['boolean'],
            'notify_grade_changes' => ['boolean'],
            
            // Feature Flags
            'enable_parent_portal' => ['boolean'],
            'enable_online_payments' => ['boolean'],
            'enable_attendance_tracking' => ['boolean'],
            'enable_grade_book' => ['boolean'],
        ]);

        // Save all settings to cache
        $settingsToSave = [
            // School Information
            'school_name',
            'school_address',
            'school_phone',
            'school_email',
            'school_website',
            
            // Academic Settings
            'academic_year_start_month',
            'grading_system',
            'passing_grade',
            'max_absences_allowed',
            
            // System Settings
            'timezone',
            'date_format',
            'time_format',
            'pagination_limit',
            
            // Email Settings
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
            'mail_from_address',
            'mail_from_name',
            
            // Security Settings
            'session_lifetime',
            'password_min_length',
            'login_attempts',
            'account_lockout_time',
            
            // Notification Settings
            'enable_email_notifications',
            'enable_sms_notifications',
            'notify_new_enrollments',
            'notify_grade_changes',
            
            // Feature Flags
            'enable_parent_portal',
            'enable_online_payments',
            'enable_attendance_tracking',
            'enable_grade_book',
        ];

        foreach ($settingsToSave as $setting) {
            $value = $request->input($setting);
            
            // Handle boolean values
            if (in_array($setting, [
                'enable_email_notifications',
                'enable_sms_notifications', 
                'notify_new_enrollments',
                'notify_grade_changes',
                'enable_parent_portal',
                'enable_online_payments',
                'enable_attendance_tracking',
                'enable_grade_book'
            ])) {
                $value = $request->boolean($setting);
            }
            
            Cache::put("settings.{$setting}", $value, now()->addYear());
        }

        // Update environment file for critical settings
        $this->updateEnvironmentFile([
            'APP_TIMEZONE' => $request->timezone,
            'MAIL_HOST' => $request->smtp_host,
            'MAIL_PORT' => $request->smtp_port,
            'MAIL_USERNAME' => $request->smtp_username,
            'MAIL_PASSWORD' => $request->smtp_password,
            'MAIL_ENCRYPTION' => $request->smtp_encryption,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            'MAIL_FROM_NAME' => $request->mail_from_name,
        ]);

        return redirect()->route('settings')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Clear system cache.
     */
    public function clearCache()
    {
        try {
            // Clear application cache
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            return redirect()->route('settings')
                ->with('success', 'System cache cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->route('settings')
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Run database maintenance.
     */
    public function runMaintenance()
    {
        try {
            // Optimize database
            \Artisan::call('db:seed', ['--class' => 'DatabaseOptimizationSeeder']);
            
            return redirect()->route('settings')
                ->with('success', 'Database maintenance completed successfully.');
        } catch (\Exception $e) {
            return redirect()->route('settings')
                ->with('error', 'Maintenance failed: ' . $e->getMessage());
        }
    }

    /**
     * Export system settings.
     */
    public function exportSettings()
    {
        $settings = Cache::get('settings', []);
        
        $filename = 'school_settings_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()
            ->json($settings, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    /**
     * Import system settings.
     */
    public function importSettings(Request $request)
    {
        $request->validate([
            'settings_file' => ['required', 'file', 'mimes:json', 'max:1024'] // Max 1MB
        ]);

        try {
            $content = file_get_contents($request->file('settings_file')->getRealPath());
            $settings = json_decode($content, true);

            if (!$settings) {
                throw new \Exception('Invalid JSON format');
            }

            // Import settings to cache
            foreach ($settings as $key => $value) {
                Cache::put("settings.{$key}", $value, now()->addYear());
            }

            return redirect()->route('settings')
                ->with('success', 'Settings imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('settings')
                ->with('error', 'Failed to import settings: ' . $e->getMessage());
        }
    }

    /**
     * Update environment file with new values.
     */
    private function updateEnvironmentFile(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        foreach ($values as $key => $value) {
            if ($value === null) {
                continue;
            }

            $value = '"' . str_replace('"', '\"', $value) . '"';
            
            if (preg_match("/^{$key}=.*$/m", $str)) {
                $str = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $str);
            } else {
                $str .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envFile, $str);
    }

    /**
     * Test email configuration.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => ['required', 'email']
        ]);

        try {
            // Update mail configuration temporarily
            Config::set('mail.mailers.smtp.host', Cache::get('settings.smtp_host'));
            Config::set('mail.mailers.smtp.port', Cache::get('settings.smtp_port'));
            Config::set('mail.mailers.smtp.username', Cache::get('settings.smtp_username'));
            Config::set('mail.mailers.smtp.password', Cache::get('settings.smtp_password'));
            Config::set('mail.mailers.smtp.encryption', Cache::get('settings.smtp_encryption'));
            Config::set('mail.from.address', Cache::get('settings.mail_from_address'));
            Config::set('mail.from.name', Cache::get('settings.mail_from_name'));

            // Send test email
            \Mail::raw('This is a test email from your School Management System.', function ($message) use ($request) {
                $message->to($request->test_email)
                       ->subject('Test Email - School Management System');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ]);
        }
    }
}