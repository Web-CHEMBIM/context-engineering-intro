# Laravel School Management System with Cuba Admin Theme

## Goal
Build a comprehensive role-based school management system using Laravel 10+, implementing the Cuba Admin Theme design system with full Bootstrap 5.3 integration. The system must support SuperAdmin, Admin, Teacher, and Student roles with hierarchical permissions, academic management (classes, subjects, teacher assignments), and 15 specific school management dashboard widgets following the exact design-system.json specifications.

## Why
- **Business Value**: Streamline educational institution management with modern web technology
- **User Impact**: Provide intuitive dashboards for different user roles (4 distinct user types)
- **Integration**: Leverage Cuba Admin Theme's 13 dashboard variations, specifically the school management dashboard
- **Scalability**: Modular architecture designed for future expansion (attendance, timetable, grades modules)

## What
A full-stack Laravel application featuring:
- Role-based authentication (SuperAdmin > Admin > Teacher > Student hierarchy)
- Academic management (classes, subjects, teacher-subject-class assignments)
- Modern dashboard with 15 school-specific widgets
- Cuba Admin Theme integration with exact design-system.json implementation
- RESTful API with Laravel Sanctum authentication
- Responsive Blade templates with Bootstrap 5.3

### Success Criteria
- [ ] SuperAdmin can create users with different roles
- [ ] Admin can manage classes, subjects, and teacher assignments  
- [ ] Teachers can view assigned classes and students (limited access)
- [ ] Students can view class schedules and assigned teachers
- [ ] All 15 school management widgets functional and displaying real data
- [ ] Complete Cuba Admin Theme implementation following design-system.json
- [ ] Role-based permissions working with Spatie Laravel Permission
- [ ] Responsive design working across all Bootstrap 5.3 breakpoints

## All Needed Context

### Documentation & References
```yaml
# CRITICAL LARAVEL DOCUMENTATION
- url: https://laravel.com/docs/10.x/eloquent-relationships
  why: Essential for Student-Teacher-Class-Subject relationships
  critical: Many-to-many relationships, pivot tables, has-many-through patterns

- url: https://spatie.be/docs/laravel-permission/v6/introduction
  why: Role-based access control implementation
  critical: HasRoles trait, role hierarchy, middleware patterns

- url: https://spatie.be/docs/laravel-permission/v6/basic-usage/role-permissions
  why: Permission assignment and checking patterns
  critical: Gate::before() for SuperAdmin bypass, role middleware setup

- url: https://laravel.com/docs/10.x/sanctum
  why: API authentication for future mobile/API access
  critical: SPA authentication, token management

- url: https://laravel.com/docs/10.x/blade
  why: Templating engine for Cuba theme integration
  critical: Component-based architecture, slot patterns, layouts

# DESIGN SYSTEM INTEGRATION
- file: design-system.json
  why: Complete Cuba Admin Theme specifications that MUST be followed exactly
  critical: Color palette (50-950 shades), typography (Inter/Poppins), 15 school widgets, Bootstrap 5.3 breakpoints

- url: https://admin.pixelstrap.com/cuba/template/dashboard-07.html
  why: School Management dashboard reference implementation
  critical: Widget layouts, color usage, component styling

- url: https://getbootstrap.com/docs/5.3/getting-started/introduction/
  why: Bootstrap 5.3 integration patterns
  critical: Grid system, utility classes, responsive breakpoints

# SCHOOL MANAGEMENT PATTERNS
- url: https://stackoverflow.com/questions/53974638/database-structure-for-a-school-management-system-using-laravel-to-implement-th
  why: Proven database schema patterns for academic entities
  critical: Student-Subject many-to-many, Teacher-Class assignments, academic year handling

- url: https://github.com/spatie/laravel-permission
  why: Real-world implementation examples and common patterns
  critical: Middleware usage, policy patterns, role hierarchy
```

### Current Codebase Structure
```bash
G:\My Drive\Irish\context-engineering-intro\
├── CLAUDE.md                    # Project standards (500-line limit, PEP8 patterns)
├── INITIAL.md                   # Complete requirements
├── design-system.json           # Cuba theme specifications (CRITICAL)
├── use-cases/
│   ├── pydantic-ai/            # Python patterns (adapt structure concepts)
│   └── mcp-server/             # TypeScript auth patterns (adapt for Laravel)
└── PRPs/
    └── templates/
        └── prp_base.md         # This template
```

### Desired Laravel Codebase Structure
```bash
school-management-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Role-specific controllers
│   │   │   ├── Teacher/
│   │   │   ├── Student/
│   │   │   └── Api/            # API controllers
│   │   ├── Middleware/         # Role-based middleware
│   │   └── Requests/           # Form request validation
│   ├── Models/                 # Eloquent models with relationships
│   │   ├── User.php
│   │   ├── Student.php
│   │   ├── Teacher.php
│   │   ├── SchoolClass.php
│   │   ├── Subject.php
│   │   └── TeacherSubject.php
│   ├── Policies/               # Authorization policies
│   └── Services/               # Business logic services
├── database/
│   ├── migrations/             # Schema definitions
│   ├── seeders/                # Initial data
│   └── factories/              # Test data generation
├── resources/
│   ├── views/
│   │   ├── layouts/            # Master layouts
│   │   ├── components/         # Blade components
│   │   ├── admin/              # Admin-specific views
│   │   ├── teacher/            # Teacher-specific views
│   │   └── student/            # Student-specific views
│   ├── css/                    # Cuba theme CSS
│   └── js/                     # Cuba theme JS
├── public/
│   ├── cuba-theme/             # Extracted theme assets
│   └── storage/                # File uploads
├── routes/
│   ├── web.php                 # Web routes by role
│   └── api.php                 # API routes
└── tests/                      # Feature and unit tests
```

### Known Gotchas & Critical Patterns
```php
// CRITICAL: Spatie Permission Setup
// User model MUST use HasRoles trait
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable {
    use HasRoles;
    // GOTCHA: Never assign permissions directly to SuperAdmin
    // Use Gate::before() in AuthServiceProvider instead
}

// CRITICAL: SuperAdmin Bypass Pattern
// In AuthServiceProvider.php
Gate::before(function ($user, $ability) {
    if ($user->hasRole('SuperAdmin')) {
        return true; // SuperAdmin bypasses all permissions
    }
});

// CRITICAL: School Management Relationships
// Many-to-many with pivot data
class Student extends Model {
    public function subjects() {
        return $this->belongsToMany(Subject::class, 'student_subjects')
                    ->withPivot('academic_year', 'enrollment_date')
                    ->withTimestamps();
    }
}

// GOTCHA: Academic Year Handling
// ALWAYS include academic_year in queries to prevent data mixing
$students = Student::whereHas('subjects', function($query) {
    $query->where('academic_year', config('app.current_academic_year'));
});

// CRITICAL: Cuba Theme Helper Classes Pattern
// Must implement exact design-system.json specifications
<div class="card bg-primary-50 border-primary-200 shadow-md">
    <div class="card-body p-24"> <!-- 24 = 1.5rem from spacing scale -->
        <h5 class="card-title f-18 f-w-600 text-primary-700">
            <!-- f-18 = font-size 18px, f-w-600 = font-weight 600 -->
        </h5>
    </div>
</div>

// GOTCHA: Laravel Eloquent N+1 Prevention
// ALWAYS eager load relationships
$teachers = Teacher::with(['subjects', 'classes.students'])->get();

// CRITICAL: Route Model Binding with Policies
Route::middleware(['role:Admin'])->group(function () {
    Route::resource('students', StudentController::class);
});
```

## Implementation Blueprint

### Core Data Models & Relationships
```php
// Following proven school management patterns from research
class User extends Authenticatable {
    use HasRoles, SoftDeletes;
    // Polymorphic relationship to Student/Teacher/Admin profiles
}

class Student extends Model {
    public function user() { return $this->belongsTo(User::class); }
    public function classes() { return $this->belongsToMany(SchoolClass::class); }
    public function subjects() { return $this->belongsToMany(Subject::class)->withPivot('academic_year'); }
}

class Teacher extends Model {
    public function user() { return $this->belongsTo(User::class); }
    public function subjects() { return $this->belongsToMany(Subject::class); }
    public function classes() { return $this->belongsToMany(SchoolClass::class); }
}

class SchoolClass extends Model {
    public function students() { return $this->hasMany(Student::class); }
    public function subjects() { return $this->belongsToMany(Subject::class); }
    public function teachers() { return $this->belongsToMany(Teacher::class); }
}

class Subject extends Model {
    public function teachers() { return $this->belongsToMany(Teacher::class); }
    public function students() { return $this->belongsToMany(Student::class); }
    public function classes() { return $this->belongsToMany(SchoolClass::class); }
}
```

### Implementation Tasks (Sequential Order)

```yaml
Task 1: Laravel Project Setup & Dependencies
CREATE new Laravel 10+ project:
  - composer create-project laravel/laravel school-management-system
  - Install Spatie Permission: composer require spatie/laravel-permission
  - Install Laravel Sanctum: php artisan sanctum:install
  - Configure database connection (MySQL/PostgreSQL)

Task 2: Database Schema & Migrations
CREATE migrations following school management patterns:
  - php artisan make:migration create_students_table
  - php artisan make:migration create_teachers_table  
  - php artisan make:migration create_school_classes_table
  - php artisan make:migration create_subjects_table
  - php artisan make:migration create_pivot_tables
  - IMPLEMENT soft deletes on all academic entities
  - ADD proper foreign key constraints and indexes

Task 3: Eloquent Models with Relationships
CREATE models with proper relationships:
  - IMPLEMENT User model with HasRoles trait
  - CREATE Student, Teacher, SchoolClass, Subject models
  - DEFINE all many-to-many relationships with pivot data
  - ADD academic_year handling to prevent data mixing
  - IMPLEMENT SoftDeletes trait where needed

Task 4: Spatie Permission Setup
CONFIGURE role-based access control:
  - CREATE roles: SuperAdmin, Admin, Teacher, Student
  - DEFINE permissions for each academic entity CRUD
  - IMPLEMENT Gate::before() for SuperAdmin bypass
  - CREATE role-specific middleware
  - SETUP permission assignment patterns

Task 5: Cuba Admin Theme Integration  
INTEGRATE design-system.json specifications:
  - EXTRACT Cuba theme assets to public/cuba-theme/
  - CREATE master layout blade template
  - IMPLEMENT exact color palette (primary-500: #0ea5e9, etc.)
  - SETUP typography system (Inter/Poppins fonts)
  - CREATE helper CSS classes (p-{size}, f-{size}, etc.)
  - ENSURE Bootstrap 5.3 breakpoint compatibility

Task 6: Blade Components & Layouts
CREATE component-based architecture:
  - IMPLEMENT sidebar component (260px expanded, 60px collapsed)
  - CREATE navbar component (70px height, white background)
  - BUILD card/widget components following design-system.json
  - SETUP role-specific layouts (admin, teacher, student)
  - CREATE form components with validation styling

Task 7: Authentication & Authorization
IMPLEMENT secure authentication flow:
  - CONFIGURE Laravel Sanctum for API authentication
  - CREATE login/logout functionality
  - IMPLEMENT role-based route protection
  - SETUP authorization policies for each model
  - CREATE middleware for role-specific access

Task 8: Controllers & Business Logic
CREATE role-specific controllers:
  - SuperAdmin: UserController (create all user types)
  - Admin: ClassController, SubjectController, TeacherController
  - Teacher: StudentController (limited), ClassController (readonly)
  - Student: ProfileController, ScheduleController (readonly)
  - IMPLEMENT proper eager loading to prevent N+1 queries

Task 9: School Management Dashboard Widgets
IMPLEMENT 15 specific widgets from design-system.json:
  - Academic Performance widget
  - School Performance metrics
  - Teachers Statistics
  - Students Statistics  
  - School Finance summary
  - Performance Overview
  - School Calendar
  - Today's Tasks
  - Notice Board
  - Shining Stars (top students)
  - Unpaid Fees tracking
  - Top Students ranking
  - New Enrolled Students
  - Attendance Tracking
  - Parent Statistics (if needed)

Task 10: API Endpoints & Resources
CREATE RESTful API with Laravel Resources:
  - IMPLEMENT StudentResource, TeacherResource, etc.
  - CREATE API controllers with proper authentication
  - SETUP consistent JSON response formatting
  - IMPLEMENT pagination for large datasets
  - ADD proper error handling and validation

Task 11: Form Requests & Validation
CREATE validation classes:
  - IMPLEMENT CreateStudentRequest, UpdateTeacherRequest, etc.
  - ADD business rule validation (unique email, grade level limits)
  - SETUP error message customization
  - ENSURE XSS protection with proper escaping

Task 12: Database Seeding & Factory Setup
CREATE test data generation:
  - IMPLEMENT UserSeeder with default roles
  - CREATE StudentFactory, TeacherFactory for testing
  - SETUP development database with sample data
  - ENSURE proper academic year handling in seeds

Task 13: Testing & Quality Assurance
IMPLEMENT comprehensive test suite:
  - CREATE feature tests for each role's capabilities
  - TEST role-based access control thoroughly
  - VERIFY widget functionality with real data
  - ENSURE responsive design across breakpoints
  - TEST API endpoints with different permission levels
```

### Widget Implementation Pseudocode
```php
// Task 9 - Critical School Management Widgets
class DashboardController extends Controller {
    public function academicPerformance() {
        // PATTERN: Always scope by academic year
        $data = Student::with('subjects')
            ->whereHas('subjects', function($q) {
                $q->where('academic_year', current_academic_year());
            })
            ->get()
            ->map(function($student) {
                return [
                    'name' => $student->name,
                    'average_grade' => $student->calculateAverageGrade(),
                    'status' => $student->getPerformanceStatus()
                ];
            });
            
        return view('widgets.academic-performance', compact('data'));
    }
    
    public function schoolFinance() {
        // GOTCHA: Use Laravel's built-in aggregation methods
        $stats = [
            'total_fees' => Student::sum('total_fees'),
            'paid_fees' => Payment::where('status', 'paid')->sum('amount'),
            'pending_fees' => $this->calculatePendingFees(),
            'overdue_count' => $this->getOverduePaymentsCount()
        ];
        
        return view('widgets.school-finance', compact('stats'));
    }
}

// CRITICAL: Widget Blade Component Pattern
// resources/views/components/widget-card.blade.php
<div class="card bg-{{ $bgColor ?? 'white' }} shadow-{{ $shadow ?? 'md' }} border-radius-{{ $radius ?? 'lg' }}">
    <div class="card-header bg-{{ $headerBg ?? 'gray-50' }} border-bottom border-{{ $borderColor ?? 'gray-200' }}">
        <h5 class="f-16 f-w-600 text-{{ $titleColor ?? 'gray-800' }} m-0">{{ $title }}</h5>
    </div>
    <div class="card-body p-{{ $padding ?? '24' }}">
        {{ $slot }}
    </div>
</div>
```

### Integration Points
```yaml
DATABASE:
  - academic_years table: "Manage multiple academic years"
  - student_subjects pivot: "Include academic_year, enrollment_date columns"
  - teacher_classes pivot: "Include assigned_date, status columns"
  - users table: "Add profile_type polymorphic columns"

CUBA THEME ASSETS:
  - public/cuba-theme/css/: "All theme CSS files"
  - public/cuba-theme/js/: "JavaScript and jQuery plugins"
  - public/cuba-theme/images/: "Icons and UI images"
  - resources/sass/: "SCSS files for customization"

CONFIGURATION:
  - config/permission.php: "Spatie permission configuration"
  - config/sanctum.php: "API authentication settings"
  - config/app.php: "Add current_academic_year setting"

ROUTES:
  - routes/web.php: "Role-based route groups with middleware"
  - routes/api.php: "Sanctum-protected API endpoints"
```

## Validation Loop

### Level 1: Laravel Standards & Setup
```bash
# Run these FIRST - ensure Laravel project is properly configured
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed

# Check Laravel installation
php artisan about
# Expected: Laravel 10.x with all components working

# Install and configure dependencies
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan sanctum:install
php artisan migrate

# Expected: All migrations run successfully, no errors
```

### Level 2: Model Relationships & Database
```bash
# Test model relationships work correctly
php artisan tinker
# In tinker:
$user = User::factory()->create();
$student = Student::factory()->create(['user_id' => $user->id]);
$student->user; // Should return User instance
$student->subjects()->attach(Subject::first()->id, ['academic_year' => '2024-2025']);
$student->subjects; // Should return Collection with pivot data

# Expected: All relationships return correct data, no SQL errors
```

### Level 3: Role-Based Access Control
```bash
# Test Spatie permissions work
php artisan tinker
# In tinker:
$superAdmin = User::create(['name' => 'Super Admin', 'email' => 'super@test.com']);
$superAdmin->assignRole('SuperAdmin');
$superAdmin->hasRole('SuperAdmin'); // Should return true
$superAdmin->can('manage-students'); // Should return true (Gate::before)

# Test middleware protection
curl -X GET http://localhost:8000/admin/students
# Expected: Redirect to login for unauthenticated users
```

### Level 4: Cuba Theme Integration
```bash
# Check theme assets are properly loaded
php artisan serve
# Visit: http://localhost:8000
# Expected: Cuba theme styling visible, no 404 errors for CSS/JS

# Verify design-system.json implementation
# Check in browser dev tools:
# - Primary color #0ea5e9 is applied
# - Inter/Poppins fonts are loaded
# - Bootstrap 5.3 grid system works
# - Helper classes (p-24, f-18, etc.) function correctly
```

### Level 5: Widget Functionality
```bash
# Test dashboard widgets load with real data
# Visit: http://localhost:8000/admin/dashboard
# Expected: All 15 widgets display without errors

# Test responsive design
# Chrome DevTools: Check mobile, tablet, desktop views
# Expected: Widgets adapt correctly to breakpoints
```

### Level 6: API Endpoints
```bash
# Test API authentication and responses
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@test.com", "password": "password"}'
# Expected: {"token": "...", "user": {...}}

curl -X GET http://localhost:8000/api/students \
  -H "Authorization: Bearer {token}"
# Expected: Paginated student data in consistent JSON format
```

## Final Validation Checklist
- [ ] All migrations run: `php artisan migrate:status`
- [ ] Roles created: Check roles table has SuperAdmin, Admin, Teacher, Student
- [ ] Permissions working: SuperAdmin can access everything, others role-limited
- [ ] Theme integrated: Cuba admin theme fully functional with design-system.json
- [ ] Widgets operational: All 15 school management widgets displaying real data
- [ ] Relationships working: Students-Classes-Subjects-Teachers properly linked
- [ ] API functional: RESTful endpoints with Sanctum authentication
- [ ] Responsive design: Works across all Bootstrap 5.3 breakpoints
- [ ] No N+1 queries: Use Laravel Debugbar to verify optimized queries
- [ ] Error handling: Graceful error pages and validation messages

## Anti-Patterns to Avoid
- ❌ Don't assign permissions directly to SuperAdmin (use Gate::before())
- ❌ Don't ignore academic year scoping (data will mix across years)
- ❌ Don't skip eager loading (causes N+1 query problems)
- ❌ Don't deviate from design-system.json specifications
- ❌ Don't use hard deletes on academic records (use SoftDeletes)
- ❌ Don't forget CSRF protection on forms
- ❌ Don't skip input validation (use Form Requests)
- ❌ Don't ignore responsive design requirements

---

## PRP Confidence Score: 9/10

**Justification**: This PRP provides comprehensive context including real-world Laravel patterns, Spatie permission implementation details, Cuba theme specifications, proven database schemas, and executable validation steps. The only uncertainty is potential Laravel version compatibility issues or Cuba theme asset integration complexities, hence 9/10 rather than 10/10.

**Expected Implementation Time**: 3-5 days for experienced Laravel developer, 1-2 weeks for intermediate developer following this PRP.