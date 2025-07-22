<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Teacher Model for School Management System
 * 
 * Represents a teacher with professional information and assignments
 */
class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'teacher_id',
        'employee_id',
        'hire_date',
        'department',
        'employment_type',
        'teacher_status',
        'salary',
        'qualification',
        'experience_years',
        'specializations',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'contract_end_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'contract_end_date' => 'date',
            'salary' => 'decimal:2',
            'experience_years' => 'integer',
            'specializations' => 'array',
        ];
    }

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array<string>
     */
    protected $appends = [
        'full_name',
        'status_display',
        'years_of_service'
    ];

    /**
     * Get the user associated with the teacher.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all subjects assigned to this teacher.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject')
                    ->withPivot('academic_year_id', 'assigned_date', 'status', 'is_primary_teacher', 'weekly_periods', 'remarks')
                    ->withTimestamps();
    }

    /**
     * Get all classes assigned to this teacher.
     */
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_class')
                    ->withPivot('academic_year_id', 'assigned_date', 'status', 'is_class_teacher', 'responsibilities', 'remarks')
                    ->withTimestamps();
    }

    /**
     * Get currently assigned subjects.
     */
    public function currentSubjects()
    {
        $currentYear = AcademicYear::currentId();
        return $this->subjects()->wherePivot('academic_year_id', $currentYear)
                                ->wherePivot('status', 'assigned');
    }

    /**
     * Get currently assigned classes.
     */
    public function currentClasses()
    {
        $currentYear = AcademicYear::currentId();
        return $this->classes()->wherePivot('academic_year_id', $currentYear)
                               ->wherePivot('status', 'assigned');
    }

    /**
     * Get classes where this teacher is the class teacher (homeroom).
     */
    public function homeRoomClasses()
    {
        $currentYear = AcademicYear::currentId();
        return $this->classes()->wherePivot('academic_year_id', $currentYear)
                               ->wherePivot('is_class_teacher', true)
                               ->wherePivot('status', 'assigned');
    }

    /**
     * Get subjects where this teacher is the primary teacher.
     */
    public function primarySubjects()
    {
        $currentYear = AcademicYear::currentId();
        return $this->subjects()->wherePivot('academic_year_id', $currentYear)
                                ->wherePivot('is_primary_teacher', true)
                                ->wherePivot('status', 'assigned');
    }

    /**
     * Get all students taught by this teacher (through classes).
     */
    public function students()
    {
        $classIds = $this->currentClasses()->pluck('school_classes.id');
        return Student::whereIn('school_class_id', $classIds)
                     ->where('student_status', 'enrolled');
    }

    /**
     * Get the teacher's full name from user.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user ? $this->user->name : 'Unknown';
    }

    /**
     * Get the teacher's email from user.
     */
    public function getEmailAttribute(): string
    {
        return $this->user ? $this->user->email : '';
    }

    /**
     * Get the teacher's phone from user.
     */
    public function getPhoneAttribute(): string
    {
        return $this->user ? $this->user->phone : '';
    }

    /**
     * Get teacher status display.
     */
    public function getStatusDisplayAttribute(): string
    {
        return ucfirst(str_replace('-', ' ', $this->teacher_status));
    }

    /**
     * Get employment type display.
     */
    public function getEmploymentDisplayAttribute(): string
    {
        return ucfirst(str_replace('-', ' ', $this->employment_type));
    }

    /**
     * Calculate years of service.
     */
    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->hire_date) {
            return 0;
        }

        return round($this->hire_date->diffInYears(now(), true), 1);
    }

    /**
     * Get total number of subjects assigned.
     */
    public function getSubjectCountAttribute(): int
    {
        return $this->currentSubjects()->count();
    }

    /**
     * Get total number of classes assigned.
     */
    public function getClassCountAttribute(): int
    {
        return $this->currentClasses()->count();
    }

    /**
     * Get total number of students taught.
     */
    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }

    /**
     * Get total weekly periods.
     */
    public function getTotalWeeklyPeriodsAttribute(): int
    {
        return $this->currentSubjects()->sum('teacher_subject.weekly_periods') ?? 0;
    }

    /**
     * Check if teacher is a class teacher for any class.
     */
    public function isClassTeacher(): bool
    {
        return $this->homeRoomClasses()->count() > 0;
    }

    /**
     * Check if teacher is active.
     */
    public function isActive(): bool
    {
        return $this->teacher_status === 'active';
    }

    /**
     * Check if teacher is on contract.
     */
    public function isContractEmployee(): bool
    {
        return $this->employment_type === 'contract';
    }

    /**
     * Check if contract is expiring soon (within 30 days).
     */
    public function isContractExpiringSoon(): bool
    {
        if (!$this->isContractEmployee() || !$this->contract_end_date) {
            return false;
        }

        return $this->contract_end_date->diffInDays(now()) <= 30;
    }

    /**
     * Scope to get active teachers.
     */
    public function scopeActive($query)
    {
        return $query->where('teacher_status', 'active');
    }

    /**
     * Scope to get teachers by department.
     */
    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope to get teachers by employment type.
     */
    public function scopeByEmploymentType($query, string $employmentType)
    {
        return $query->where('employment_type', $employmentType);
    }

    /**
     * Scope to get full-time teachers.
     */
    public function scopeFullTime($query)
    {
        return $query->where('employment_type', 'full-time');
    }

    /**
     * Scope to get part-time teachers.
     */
    public function scopePartTime($query)
    {
        return $query->where('employment_type', 'part-time');
    }

    /**
     * Scope to get contract teachers.
     */
    public function scopeContract($query)
    {
        return $query->where('employment_type', 'contract');
    }

    /**
     * Scope to get teachers with expiring contracts.
     */
    public function scopeExpiringContracts($query, int $days = 30)
    {
        return $query->where('employment_type', 'contract')
                    ->whereNotNull('contract_end_date')
                    ->whereDate('contract_end_date', '<=', now()->addDays($days));
    }

    /**
     * Assign teacher to a subject.
     */
    public function assignToSubject(Subject $subject, bool $isPrimary = false, int $weeklyPeriods = 3): void
    {
        $currentYear = AcademicYear::currentId();
        
        $this->subjects()->attach($subject->id, [
            'academic_year_id' => $currentYear,
            'assigned_date' => now(),
            'status' => 'assigned',
            'is_primary_teacher' => $isPrimary,
            'weekly_periods' => $weeklyPeriods,
        ]);
    }

    /**
     * Assign teacher to a class.
     */
    public function assignToClass(SchoolClass $class, bool $isClassTeacher = false, array $responsibilities = []): void
    {
        $currentYear = AcademicYear::currentId();
        
        $this->classes()->attach($class->id, [
            'academic_year_id' => $currentYear,
            'assigned_date' => now(),
            'status' => 'assigned',
            'is_class_teacher' => $isClassTeacher,
            'responsibilities' => json_encode($responsibilities),
        ]);
    }

    /**
     * Remove teacher from a subject.
     */
    public function removeFromSubject(Subject $subject): void
    {
        $currentYear = AcademicYear::currentId();
        $this->subjects()->wherePivot('academic_year_id', $currentYear)
                        ->updateExistingPivot($subject->id, ['status' => 'unassigned']);
    }

    /**
     * Remove teacher from a class.
     */
    public function removeFromClass(SchoolClass $class): void
    {
        $currentYear = AcademicYear::currentId();
        $this->classes()->wherePivot('academic_year_id', $currentYear)
                       ->updateExistingPivot($class->id, ['status' => 'unassigned']);
    }

    /**
     * Update teacher status.
     */
    public function updateStatus(string $status): void
    {
        $validStatuses = ['active', 'inactive', 'on-leave', 'terminated'];
        
        if (in_array($status, $validStatuses)) {
            $this->update(['teacher_status' => $status]);
        }
    }

    /**
     * Calculate workload percentage (assuming 40 periods/week as 100%).
     */
    public function getWorkloadPercentageAttribute(): float
    {
        $maxPeriods = 40; // Assuming 40 periods per week as full load
        return min(100, round(($this->total_weekly_periods / $maxPeriods) * 100, 1));
    }

    /**
     * Get teacher summary for dashboard.
     */
    public function getDashboardSummary(): array
    {
        return [
            'name' => $this->full_name,
            'employee_id' => $this->employee_id,
            'department' => $this->department,
            'status' => $this->status_display,
            'subjects_count' => $this->subject_count,
            'classes_count' => $this->class_count,
            'students_count' => $this->student_count,
            'weekly_periods' => $this->total_weekly_periods,
            'workload_percentage' => $this->workload_percentage,
            'is_class_teacher' => $this->isClassTeacher(),
            'years_of_service' => $this->years_of_service,
        ];
    }
}