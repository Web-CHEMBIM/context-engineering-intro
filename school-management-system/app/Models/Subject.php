<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Subject Model for School Management System
 * 
 * Represents academic subjects (Math, English, Science, etc.)
 */
class Subject extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'credit_hours',
        'department',
        'is_core_subject',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_core_subject' => 'boolean',
            'is_active' => 'boolean',
            'credit_hours' => 'integer',
        ];
    }

    /**
     * Get all students enrolled in this subject.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject')
                    ->withPivot('academic_year_id', 'enrollment_date', 'status', 'grade', 'remarks')
                    ->withTimestamps();
    }

    /**
     * Get all teachers assigned to this subject.
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject')
                    ->withPivot('academic_year_id', 'assigned_date', 'status', 'is_primary_teacher', 'weekly_periods', 'remarks')
                    ->withTimestamps();
    }

    /**
     * Get all classes that have this subject.
     */
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject')
                    ->withPivot('academic_year_id', 'is_mandatory', 'weekly_periods', 'passing_marks', 'full_marks', 'syllabus', 'remarks')
                    ->withTimestamps();
    }

    /**
     * Get students enrolled in this subject for current academic year.
     */
    public function currentStudents()
    {
        $currentYear = AcademicYear::currentId();
        return $this->students()->wherePivot('academic_year_id', $currentYear)
                                ->wherePivot('status', 'enrolled');
    }

    /**
     * Get teachers assigned to this subject for current academic year.
     */
    public function currentTeachers()
    {
        $currentYear = AcademicYear::currentId();
        return $this->teachers()->wherePivot('academic_year_id', $currentYear)
                                ->wherePivot('status', 'assigned');
    }

    /**
     * Get the primary teacher for this subject.
     */
    public function primaryTeacher()
    {
        $currentYear = AcademicYear::currentId();
        return $this->teachers()->wherePivot('academic_year_id', $currentYear)
                                ->wherePivot('is_primary_teacher', true)
                                ->wherePivot('status', 'assigned')
                                ->first();
    }

    /**
     * Get classes that have this subject for current academic year.
     */
    public function currentClasses()
    {
        $currentYear = AcademicYear::currentId();
        return $this->classes()->wherePivot('academic_year_id', $currentYear);
    }

    /**
     * Get total enrollment count for current academic year.
     */
    public function getCurrentEnrollmentCountAttribute(): int
    {
        return $this->currentStudents()->count();
    }

    /**
     * Get total number of teachers assigned.
     */
    public function getTeacherCountAttribute(): int
    {
        return $this->currentTeachers()->count();
    }

    /**
     * Get total number of classes having this subject.
     */
    public function getClassCountAttribute(): int
    {
        return $this->currentClasses()->count();
    }

    /**
     * Check if subject has a primary teacher assigned.
     */
    public function hasPrimaryTeacher(): bool
    {
        return $this->primaryTeacher() !== null;
    }

    /**
     * Scope to get active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get core subjects.
     */
    public function scopeCore($query)
    {
        return $query->where('is_core_subject', true);
    }

    /**
     * Scope to get elective subjects.
     */
    public function scopeElective($query)
    {
        return $query->where('is_core_subject', false);
    }

    /**
     * Scope to get subjects by department.
     */
    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Assign a teacher to this subject.
     */
    public function assignTeacher(Teacher $teacher, bool $isPrimary = false, int $weeklyPeriods = 3): void
    {
        $currentYear = AcademicYear::currentId();
        
        // If assigning as primary teacher, unset other primary teachers
        if ($isPrimary) {
            $this->teachers()->wherePivot('academic_year_id', $currentYear)
                            ->updateExistingPivot($teacher->id, ['is_primary_teacher' => false]);
        }

        $this->teachers()->attach($teacher->id, [
            'academic_year_id' => $currentYear,
            'assigned_date' => now(),
            'status' => 'assigned',
            'is_primary_teacher' => $isPrimary,
            'weekly_periods' => $weeklyPeriods,
        ]);
    }

    /**
     * Remove a teacher from this subject.
     */
    public function removeTeacher(Teacher $teacher): void
    {
        $currentYear = AcademicYear::currentId();
        $this->teachers()->wherePivot('academic_year_id', $currentYear)
                        ->updateExistingPivot($teacher->id, ['status' => 'unassigned']);
    }

    /**
     * Enroll a student in this subject.
     */
    public function enrollStudent(Student $student): void
    {
        $currentYear = AcademicYear::currentId();
        
        // Check if already enrolled
        $existing = $this->students()->wherePivot('academic_year_id', $currentYear)
                                   ->wherePivot('student_id', $student->id)
                                   ->first();

        if (!$existing) {
            $this->students()->attach($student->id, [
                'academic_year_id' => $currentYear,
                'enrollment_date' => now(),
                'status' => 'enrolled',
            ]);
        }
    }

    /**
     * Remove a student from this subject.
     */
    public function removeStudent(Student $student): void
    {
        $currentYear = AcademicYear::currentId();
        $this->students()->wherePivot('academic_year_id', $currentYear)
                        ->updateExistingPivot($student->id, ['status' => 'dropped']);
    }

    /**
     * Get subject display name with code.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->code . ')';
    }

    /**
     * Get subject type (Core/Elective).
     */
    public function getTypeAttribute(): string
    {
        return $this->is_core_subject ? 'Core' : 'Elective';
    }
}