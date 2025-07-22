## FEATURE:

Role-based School Management System built with Laravel, featuring comprehensive user management, academic administration, and modern web interface using the Cuba admin theme.

**Technology Stack:**
- **Backend**: Laravel 10+ with Eloquent ORM
- **Frontend**: Blade templates with Cuba Admin Theme (Pixelstrap)
- **Database**: MySQL/PostgreSQL with Laravel migrations
- **Authentication**: Laravel Sanctum/Passport for API authentication
- **UI Framework**: Bootstrap 5 + Custom Cuba theme components
- **Design System**: Comprehensive Cuba Admin Theme design tokens (design-system.json)

**Design System Requirements:**
- **Complete Implementation**: Must follow design-system.json specifications exactly
- **13 Dashboard Variations**: Support for Default, Ecommerce, School Management, CRM, etc.
- **Color Palette**: Full implementation of primary, secondary, success, warning, danger, info color schemes with 50-950 shade scales
- **Typography**: Inter/Poppins font families with proper sizing (10px-64px) and weight system (100-900)
- **Component Library**: All components (sidebar, navbar, cards, buttons, forms, tables, avatars, badges, modals) must match design-system.json specs
- **Helper Classes**: Implement all utility classes (p-{size}, m-{size}, f-{size}, f-w-{weight}, b-r-{size}, bg-{color}, font-{color})
- **Responsive Design**: Bootstrap 5.3 breakpoints (xs, sm, md, lg, xl, xxl) with proper grid system
- **School Management Widgets**: 15 specific widgets including Academic Performance, School Finance, Attendance Tracking, Notice Board, etc.
- **Animations & Interactions**: Proper transitions, shadows, and hover states as defined in design system
- **Icon System**: Feather Icons integration with proper sizing (xs: 12px, sm: 16px, base: 20px, lg: 24px, xl: 32px, 2xl: 48px)

**Core Features:**
- **User Management**: SuperAdmin can create users with different roles (Admin, Teacher, Student) without parent functionality
- **Role-Based Access Control**: Hierarchical permissions system using Laravel Spatie Permission package
- **Academic Management**: Admin can create classes, subjects, and manage teacher-subject assignments
- **Teacher-Class Assignment**: Assign teachers to classes based on their subject expertise
- **Modern Dashboard**: Cuba admin theme with school-specific widgets and components
- **Modular Architecture**: Designed for future expansion into attendance tracking, timetable management, and additional modules

**User Roles & Permissions:**
- **SuperAdmin**: Complete system control, user creation, system configuration
- **Admin**: Class/subject management, teacher assignments, academic administration
- **Teacher**: View assigned classes and subjects, access student information (limited)
- **Student**: View class schedules, subjects, and assigned teachers

**Core Entities:**
- Users (SuperAdmin, Admin, Teacher, Student)
- Classes (Grade levels, sections)
- Subjects (Math, English, Science, etc.)
- Teacher-Subject relationships
- Teacher-Class assignments

## EXAMPLES:

The following examples should be created in the `examples/` folder to demonstrate system functionality:

- `examples/user_management/` - SuperAdmin creating different user types with role assignments
- `examples/academic_setup/` - Admin setting up classes, subjects, and teacher assignments
- `examples/role_permissions/` - Demonstrating different access levels using Laravel policies
- `examples/api_endpoints/` - Laravel API Resource examples for all CRUD operations
- `examples/models_migrations/` - Laravel Eloquent models and database migrations
- `examples/authentication/` - Laravel Sanctum authentication and role-based authorization
- `examples/blade_components/` - Cuba theme Blade components and layouts
- `examples/dashboard_widgets/` - School management specific dashboard widgets
- `examples/future_modules/` - Placeholder structure for attendance and timetable modules

## DOCUMENTATION:

**Technical Documentation:**
- **Laravel Documentation**: https://laravel.com/docs - Complete framework documentation
- **Laravel Eloquent ORM**: https://laravel.com/docs/eloquent - Database ORM and relationships
- **Laravel Sanctum**: https://laravel.com/docs/sanctum - API authentication
- **Spatie Laravel Permission**: https://spatie.be/docs/laravel-permission - Role and permission management
- **Laravel Blade**: https://laravel.com/docs/blade - Templating engine
- **Bootstrap 5**: https://getbootstrap.com/docs/5.0/ - CSS framework foundation
- **Cuba Admin Theme**: https://admin.pixelstrap.com/cuba/template/dashboard-07.html - Premium admin theme
- **MySQL Documentation**: https://dev.mysql.com/doc/ - Database setup and optimization

**Design System & UI:**
- **Cuba Admin Theme**: https://admin.pixelstrap.com/cuba/template/dashboard-07.html - Premium school management dashboard
- **Design System**: design-system.json - Complete design tokens and component specifications
- **Pixelstrap Documentation**: Theme customization and component usage
- **Feather Icons**: https://feathericons.com/ - Icon library used in Cuba theme

**Domain Knowledge:**
- School management system requirements and workflows
- Role-based access control (RBAC) patterns
- Academic calendar and scheduling principles
- Student information system (SIS) best practices

## OTHER CONSIDERATIONS:

**Security & Authentication:**
- Laravel Sanctum for API authentication with role-based permissions
- Bcrypt password hashing (Laravel default)
- Rate limiting using Laravel's built-in throttling
- Input validation using Laravel Form Requests
- CSRF protection and secure session management
- XSS protection with Blade template escaping

**Database Design:**
- Laravel Eloquent ORM with proper relationships
- Database migrations for version control
- Foreign key constraints and indexes
- Soft deletes using Laravel's SoftDeletes trait
- Database seeding for initial data

**API Design:**
- RESTful API using Laravel API Resources
- Consistent JSON response formatting
- API versioning through route prefixes
- Pagination using Laravel's built-in paginator
- Error handling with custom exception handling

**Frontend & Design System:**
- Cuba Admin Theme integration with Laravel Blade
- Component-based architecture using Blade components
- Responsive design following design-system.json specifications
- Modern dashboard with school management widgets
- Consistent UI patterns across all modules

**Modular Architecture:**
- Laravel service providers for module organization
- Clean separation between core system and future modules
- Shared contracts/interfaces for module integration
- Database schema designed for module expansion
- Route organization by module functionality
- Blade component libraries for consistent UI

**Common Gotchas for Laravel School Management Systems:**
- **Cascade Deletes**: Use Laravel's foreign key constraints carefully with academic data
- **Role Hierarchy**: Ensure superadmin role bypasses all policy checks
- **Academic Year Management**: Design pivot tables for multi-year data retention
- **Bulk Operations**: Use Laravel's bulk insert/update methods for performance
- **Data Export**: Implement Laravel Excel for student/teacher data exports
- **Audit Logging**: Use Laravel activity log packages for compliance tracking
- **Time Zone Handling**: Configure Laravel timezone properly for scheduling modules
- **Soft vs Hard Deletes**: Use Laravel's SoftDeletes trait for academic records
- **N+1 Query Problem**: Use Eloquent eager loading to prevent performance issues
- **Route Model Binding**: Implement proper model binding for clean controller methods

**Performance Considerations:**
- Database indexing on frequently queried fields (user roles, class IDs, foreign keys)
- Eloquent relationship optimization with eager loading
- Laravel caching for frequently accessed reference data (Redis/Memcached)
- Pagination using Laravel's LengthAwarePaginator
- Query optimization using Laravel Telescope for monitoring
- Asset compilation and minification using Laravel Mix/Vite
- Image optimization for user profile photos and documents

**Future Module Integration Points:**
- **Attendance Module**: User-class-date relationship tracking
- **Timetable Module**: Time-based scheduling with teacher-class-subject coordination
- **Grades Module**: Assessment and reporting functionality
- **Communication Module**: Teacher-student-admin messaging system
