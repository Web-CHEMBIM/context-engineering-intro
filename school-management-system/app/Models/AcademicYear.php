<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Academic Year Model for School Management System
 * 
 * Manages academic years and ensures proper data scoping
 */
class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all school classes for this academic year.
     */
    public function schoolClasses()
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Get all students for this academic year.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get student-subject enrollments for this academic year.
     */
    public function studentSubjects()
    {
        return $this->hasMany(StudentSubject::class);
    }

    /**
     * Get teacher-subject assignments for this academic year.
     */
    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }

    /**
     * Get teacher-class assignments for this academic year.
     */
    public function teacherClasses()
    {
        return $this->hasMany(TeacherClass::class);
    }

    /**
     * Get class-subject relationships for this academic year.
     */
    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class);
    }

    /**
     * Get the current academic year.
     */
    public static function current()
    {
        return self::where('is_current', true)->where('is_active', true)->first();
    }

    /**
     * Get all active academic years.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the current academic year ID.
     */
    public static function currentId(): ?int
    {
        $current = self::current();
        return $current ? $current->id : null;
    }

    /**
     * Set this academic year as current (and unset others).
     */
    public function setCurrent(): void
    {
        // First, set all other years as not current
        self::where('id', '!=', $this->id)->update(['is_current' => false]);
        
        // Then set this year as current
        $this->update(['is_current' => true]);
    }

    /**
     * Check if this academic year is currently active (within date range).
     */
    public function isInSession(): bool
    {
        $today = Carbon::today();
        return $today->between($this->start_date, $this->end_date);
    }

    /**
     * Get the duration of the academic year in days.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Get formatted academic year display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->start_date->format('Y') . '-' . $this->end_date->format('Y') . ')';
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one academic year is marked as current
        static::saving(function ($academicYear) {
            if ($academicYear->is_current) {
                self::where('id', '!=', $academicYear->id)
                    ->update(['is_current' => false]);
            }
        });
    }
}