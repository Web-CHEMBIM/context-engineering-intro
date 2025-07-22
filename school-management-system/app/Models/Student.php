<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Student Model for School Management System
 * 
 * Represents a student with academic information and relationships
 */
class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'student_id',
        'roll_number',
        'admission_date',
        'admission_number',
        'school_class_id',
        'academic_year_id',
        'student_status',
        'blood_group',
        'medical_conditions',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'total_fees',
        'fees_paid',
        'fees_pending',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
            'total_fees' => 'decimal:2',
            'fees_paid' => 'decimal:2',
            'fees_pending' => 'decimal:2',
        ];
    }

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array<string>
     */
    protected $appends = [
        'full_name',
        'fees_status',
        'enrollment_status'
    ];

    /**
     * Get the user associated with the student.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the school class this student belongs to.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * Get the academic year for this student.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get all subjects this student is enrolled in.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject')
                    ->withPivot('academic_year_id', 'enrollment_date', 'status', 'grade', 'remarks')
                    ->withTimestamps();
    }

    /**
     * Get currently enrolled subjects.
     */
    public function currentSubjects()
    {
        return $this->subjects()->wherePivot('status', 'enrolled')
                                ->wherePivot('academic_year_id', $this->academic_year_id);
    }

    /**
     * Get completed subjects with grades.
     */
    public function completedSubjects()
    {
        return $this->subjects()->wherePivot('status', 'completed')
                                ->wherePivot('academic_year_id', $this->academic_year_id);
    }

    /**
     * Get the student's full name from user.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user ? $this->user->name : 'Unknown';
    }

    /**
     * Get the student's email from user.
     */
    public function getEmailAttribute(): string
    {
        return $this->user ? $this->user->email : '';
    }

    /**
     * Get the student's phone from user.
     */
    public function getPhoneAttribute(): string
    {
        return $this->user ? $this->user->phone : '';
    }

    /**
     * Get fees payment status.
     */
    public function getFeesStatusAttribute(): string
    {
        if ($this->fees_pending <= 0) {
            return 'Paid';
        } elseif ($this->fees_paid <= 0) {
            return 'Unpaid';
        } else {
            return 'Partial';
        }
    }

    /**
     * Get enrollment status display.
     */
    public function getEnrollmentStatusAttribute(): string
    {
        return ucfirst($this->student_status);
    }

    /**
     * Calculate average grade for current academic year.
     */
    public function calculateAverageGrade(): float
    {
        $grades = $this->completedSubjects()->pluck('pivot.grade')->filter();
        
        if ($grades->isEmpty()) {
            return 0.0;
        }

        return round($grades->average(), 2);
    }

    /**
     * Get performance status based on average grade.
     */
    public function getPerformanceStatus(): string
    {
        $average = $this->calculateAverageGrade();
        
        if ($average >= 90) {
            return 'Excellent';
        } elseif ($average >= 80) {
            return 'Very Good';
        } elseif ($average >= 70) {
            return 'Good';
        } elseif ($average >= 60) {
            return 'Satisfactory';
        } elseif ($average >= 40) {
            return 'Needs Improvement';
        } else {
            return 'Poor';
        }
    }

    /**
     * Check if fees are fully paid.
     */
    public function hasPaidFees(): bool
    {
        return $this->fees_pending <= 0;
    }

    /**
     * Calculate fees percentage paid.
     */
    public function getFeesPercentagePaidAttribute(): float
    {
        if ($this->total_fees <= 0) {
            return 0.0;
        }

        return round(($this->fees_paid / $this->total_fees) * 100, 2);
    }

    /**
     * Get class name with section.
     */
    public function getClassNameAttribute(): string
    {
        return $this->schoolClass ? $this->schoolClass->name : 'No Class';
    }

    /**
     * Get academic year name.
     */
    public function getAcademicYearNameAttribute(): string
    {
        return $this->academicYear ? $this->academicYear->name : 'Unknown';
    }

    /**
     * Scope to get students for current academic year.
     */
    public function scopeCurrentYear($query)
    {
        $currentYear = AcademicYear::currentId();
        return $query->where('academic_year_id', $currentYear);
    }

    /**
     * Scope to get enrolled students.
     */
    public function scopeEnrolled($query)
    {
        return $query->where('student_status', 'enrolled');
    }

    /**
     * Scope to get students by class.
     */
    public function scopeByClass($query, int $classId)
    {
        return $query->where('school_class_id', $classId);
    }

    /**
     * Scope to get students by grade level.
     */
    public function scopeByGrade($query, string $gradeLevel)
    {
        return $query->whereHas('schoolClass', function ($q) use ($gradeLevel) {
            $q->where('grade_level', $gradeLevel);
        });
    }

    /**
     * Scope to get students with pending fees.
     */
    public function scopeWithPendingFees($query)
    {
        return $query->where('fees_pending', '>', 0);
    }

    /**
     * Scope to get top performing students.
     */
    public function scopeTopPerformers($query, int $limit = 10)
    {
        // This would need to be implemented with proper grade calculation
        // For now, we'll return the query as is
        return $query->enrolled()->limit($limit);
    }

    /**
     * Enroll student in a subject.
     */
    public function enrollInSubject(Subject $subject): void
    {
        $existing = $this->subjects()->wherePivot('subject_id', $subject->id)
                                   ->wherePivot('academic_year_id', $this->academic_year_id)
                                   ->first();

        if (!$existing) {
            $this->subjects()->attach($subject->id, [
                'academic_year_id' => $this->academic_year_id,
                'enrollment_date' => now(),
                'status' => 'enrolled',
            ]);
        }
    }

    /**
     * Drop a subject.
     */
    public function dropSubject(Subject $subject): void
    {
        $this->subjects()->wherePivot('subject_id', $subject->id)
                        ->wherePivot('academic_year_id', $this->academic_year_id)
                        ->updateExistingPivot($subject->id, ['status' => 'dropped']);
    }

    /**
     * Update fees payment.
     */
    public function updateFeesPayment(float $paidAmount): void
    {
        $newPaid = $this->fees_paid + $paidAmount;
        $newPending = max(0, $this->total_fees - $newPaid);

        $this->update([
            'fees_paid' => $newPaid,
            'fees_pending' => $newPending,
        ]);
    }

    /**
     * Transfer student to another class.
     */
    public function transferToClass(SchoolClass $newClass): void
    {
        $this->update([
            'school_class_id' => $newClass->id,
            'academic_year_id' => $newClass->academic_year_id,
        ]);
    }

    /**
     * Boot the model to handle automatic calculations.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($student) {
            // Auto-calculate pending fees
            if ($student->isDirty(['total_fees', 'fees_paid'])) {
                $student->fees_pending = max(0, $student->total_fees - $student->fees_paid);
            }
        });
    }
}