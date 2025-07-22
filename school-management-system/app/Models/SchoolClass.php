<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * School Class Model for School Management System
 * 
 * Represents a class/grade with section (e.g., Grade 10A, Class 5B)
 */
class SchoolClass extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'school_classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'grade_level',
        'section',
        'capacity',
        'description',
        'is_active',
        'academic_year_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'capacity' => 'integer',
        ];
    }

    /**
     * Get the academic year this class belongs to.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get all students in this class.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get all teachers assigned to this class.
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_class')
                    ->withPivot('assigned_date', 'status', 'is_class_teacher', 'responsibilities', 'remarks')
                    ->withTimestamps();
    }

    /**
     * Get all subjects for this class.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject')
                    ->withPivot('is_mandatory', 'weekly_periods', 'passing_marks', 'full_marks', 'syllabus', 'remarks')
                    ->withTimestamps();
    }

    /**
     * Get the class teacher (homeroom teacher).
     */
    public function classTeacher()
    {
        return $this->teachers()->wherePivot('is_class_teacher', true)->first();
    }

    /**
     * Get active students in this class.
     */
    public function activeStudents()
    {
        return $this->students()->where('student_status', 'enrolled');
    }

    /**
     * Get mandatory subjects for this class.
     */
    public function mandatorySubjects()
    {
        return $this->subjects()->wherePivot('is_mandatory', true);
    }

    /**
     * Get elective subjects for this class.
     */
    public function electiveSubjects()
    {
        return $this->subjects()->wherePivot('is_mandatory', false);
    }

    /**
     * Get the current enrollment count.
     */
    public function getEnrollmentCountAttribute(): int
    {
        return $this->activeStudents()->count();
    }

    /**
     * Get available capacity.
     */
    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->enrollment_count);
    }

    /**
     * Check if class is full.
     */
    public function isFull(): bool
    {
        return $this->enrollment_count >= $this->capacity;
    }

    /**
     * Get class display name with academic year.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name . ' (' . $this->academicYear->name . ')';
    }

    /**
     * Scope to get classes for current academic year.
     */
    public function scopeCurrentYear($query)
    {
        $currentAcademicYear = AcademicYear::current();
        if ($currentAcademicYear) {
            return $query->where('academic_year_id', $currentAcademicYear->id);
        }
        return $query;
    }

    /**
     * Scope to get active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get classes by grade level.
     */
    public function scopeByGrade($query, string $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Scope to get classes by section.
     */
    public function scopeBySection($query, string $section)
    {
        return $query->where('section', $section);
    }

    /**
     * Assign a teacher to this class.
     */
    public function assignTeacher(Teacher $teacher, bool $isClassTeacher = false, array $responsibilities = []): void
    {
        $this->teachers()->attach($teacher->id, [
            'assigned_date' => now(),
            'status' => 'assigned',
            'is_class_teacher' => $isClassTeacher,
            'responsibilities' => json_encode($responsibilities),
            'academic_year_id' => $this->academic_year_id,
        ]);
    }

    /**
     * Remove a teacher from this class.
     */
    public function removeTeacher(Teacher $teacher): void
    {
        $this->teachers()->updateExistingPivot($teacher->id, [
            'status' => 'unassigned'
        ]);
    }

    /**
     * Assign a subject to this class.
     */
    public function assignSubject(Subject $subject, array $details = []): void
    {
        $defaultDetails = [
            'is_mandatory' => true,
            'weekly_periods' => 3,
            'passing_marks' => 40,
            'full_marks' => 100,
            'academic_year_id' => $this->academic_year_id,
        ];

        $this->subjects()->attach($subject->id, array_merge($defaultDetails, $details));
    }

    /**
     * Remove a subject from this class.
     */
    public function removeSubject(Subject $subject): void
    {
        $this->subjects()->detach($subject->id);
    }
}