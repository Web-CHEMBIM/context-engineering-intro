<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Subject Seeder for School Management System
 * 
 * Creates comprehensive curriculum with all standard subjects
 */
class SubjectSeeder extends Seeder
{
    /**
     * Predefined subjects with realistic data.
     */
    protected array $subjects = [
        // Core Subjects - Elementary (K-5)
        ['name' => 'Mathematics', 'code' => 'MATH101', 'department' => 'Mathematics', 'grades' => [1,2,3,4,5], 'mandatory' => true, 'credit_hours' => 5],
        ['name' => 'English Language Arts', 'code' => 'ELA101', 'department' => 'Language Arts', 'grades' => [1,2,3,4,5], 'mandatory' => true, 'credit_hours' => 5],
        ['name' => 'Science', 'code' => 'SCI101', 'department' => 'Science', 'grades' => [1,2,3,4,5], 'mandatory' => true, 'credit_hours' => 4],
        ['name' => 'Social Studies', 'code' => 'SS101', 'department' => 'Social Studies', 'grades' => [3,4,5], 'mandatory' => true, 'credit_hours' => 3],
        ['name' => 'Physical Education', 'code' => 'PE101', 'department' => 'Physical Education', 'grades' => [1,2,3,4,5], 'mandatory' => true, 'credit_hours' => 2],
        ['name' => 'Art', 'code' => 'ART101', 'department' => 'Arts', 'grades' => [1,2,3,4,5], 'mandatory' => false, 'credit_hours' => 2],
        ['name' => 'Music', 'code' => 'MUS101', 'department' => 'Arts', 'grades' => [1,2,3,4,5], 'mandatory' => false, 'credit_hours' => 2],

        // Middle School (6-8)
        ['name' => 'Pre-Algebra', 'code' => 'MATH201', 'department' => 'Mathematics', 'grades' => [6,7,8], 'mandatory' => true, 'credit_hours' => 5],
        ['name' => 'English Language Arts', 'code' => 'ELA201', 'department' => 'Language Arts', 'grades' => [6,7,8], 'mandatory' => true, 'credit_hours' => 5],
        ['name' => 'Life Science', 'code' => 'SCI201', 'department' => 'Science', 'grades' => [6,7], 'mandatory' => true, 'credit_hours' => 4],
        ['name' => 'Earth Science', 'code' => 'SCI202', 'department' => 'Science', 'grades' => [8], 'mandatory' => true, 'credit_hours' => 4],
        ['name' => 'World History', 'code' => 'HIST201', 'department' => 'Social Studies', 'grades' => [6,7], 'mandatory' => true, 'credit_hours' => 4],
        ['name' => 'American History', 'code' => 'HIST202', 'department' => 'Social Studies', 'grades' => [8], 'mandatory' => true, 'credit_hours' => 4],
        ['name' => 'Physical Education', 'code' => 'PE201', 'department' => 'Physical Education', 'grades' => [6,7,8], 'mandatory' => true, 'credit_hours' => 3],
        ['name' => 'Computer Applications', 'code' => 'TECH201', 'department' => 'Technology', 'grades' => [6,7,8], 'mandatory' => false, 'credit_hours' => 3],

        // High School Core (9-12)
        ['name' => 'Algebra I', 'code' => 'MATH301', 'department' => 'Mathematics', 'grades' => [9], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'Geometry', 'code' => 'MATH302', 'department' => 'Mathematics', 'grades' => [10], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'Algebra II', 'code' => 'MATH303', 'department' => 'Mathematics', 'grades' => [11], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'English I', 'code' => 'ENG301', 'department' => 'Language Arts', 'grades' => [9], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'English II', 'code' => 'ENG302', 'department' => 'Language Arts', 'grades' => [10], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'English III', 'code' => 'ENG303', 'department' => 'Language Arts', 'grades' => [11], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'English IV', 'code' => 'ENG304', 'department' => 'Language Arts', 'grades' => [12], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'Biology', 'code' => 'BIO301', 'department' => 'Science', 'grades' => [9], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'Chemistry', 'code' => 'CHEM301', 'department' => 'Science', 'grades' => [10,11], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'Physics', 'code' => 'PHYS301', 'department' => 'Science', 'grades' => [11,12], 'mandatory' => true, 'credit_hours' => 6],
        ['name' => 'World History', 'code' => 'HIST301', 'department' => 'Social Studies', 'grades' => [9], 'mandatory' => true, 'credit_hours' => 5],
        ['name' => 'US History', 'code' => 'HIST302', 'department' => 'Social Studies', 'grades' => [10], 'mandatory' => true, 'credit_hours' => 5],
        ['name' => 'Government & Economics', 'code' => 'GOV301', 'department' => 'Social Studies', 'grades' => [11,12], 'mandatory' => true, 'credit_hours' => 5],

        // High School Electives
        ['name' => 'Pre-Calculus', 'code' => 'MATH401', 'department' => 'Mathematics', 'grades' => [11,12], 'mandatory' => false, 'credit_hours' => 6],
        ['name' => 'AP Calculus', 'code' => 'MATH402', 'department' => 'Mathematics', 'grades' => [12], 'mandatory' => false, 'credit_hours' => 6],
        ['name' => 'Statistics', 'code' => 'MATH403', 'department' => 'Mathematics', 'grades' => [11,12], 'mandatory' => false, 'credit_hours' => 4],
        ['name' => 'AP Biology', 'code' => 'BIO401', 'department' => 'Science', 'grades' => [11,12], 'mandatory' => false, 'credit_hours' => 8],
        ['name' => 'AP Chemistry', 'code' => 'CHEM401', 'department' => 'Science', 'grades' => [11,12], 'mandatory' => false, 'credit_hours' => 8],
        ['name' => 'Environmental Science', 'code' => 'ENV301', 'department' => 'Science', 'grades' => [10,11,12], 'mandatory' => false, 'credit_hours' => 4],
        ['name' => 'Computer Science I', 'code' => 'CS301', 'department' => 'Technology', 'grades' => [9,10,11,12], 'mandatory' => false, 'credit_hours' => 5],
        ['name' => 'AP Computer Science', 'code' => 'CS401', 'department' => 'Technology', 'grades' => [11,12], 'mandatory' => false, 'credit_hours' => 6],
        ['name' => 'Spanish I', 'code' => 'SPAN301', 'department' => 'World Languages', 'grades' => [9,10,11,12], 'mandatory' => false, 'credit_hours' => 5],
        ['name' => 'Spanish II', 'code' => 'SPAN302', 'department' => 'World Languages', 'grades' => [10,11,12], 'mandatory' => false, 'credit_hours' => 5],
        ['name' => 'French I', 'code' => 'FREN301', 'department' => 'World Languages', 'grades' => [9,10,11,12], 'mandatory' => false, 'credit_hours' => 5],
        ['name' => 'French II', 'code' => 'FREN302', 'department' => 'World Languages', 'grades' => [10,11,12], 'mandatory' => false, 'credit_hours' => 5],
        ['name' => 'Art I', 'code' => 'ART301', 'department' => 'Arts', 'grades' => [9,10,11,12], 'mandatory' => false, 'credit_hours' => 3],
        ['name' => 'Art II', 'code' => 'ART302', 'department' => 'Arts', 'grades' => [10,11,12], 'mandatory' => false, 'credit_hours' => 3],
        ['name' => 'Drama', 'code' => 'DRA301', 'department' => 'Arts', 'grades' => [9,10,11,12], 'mandatory' => false, 'credit_hours' => 3],
        ['name' => 'Band', 'code' => 'MUS301', 'department' => 'Arts', 'grades' => [9,10,11,12], 'mandatory' => false, 'credit_hours' => 3],
        ['name' => 'Choir', 'code' => 'MUS302', 'department' => 'Arts', 'grades' => [9,10,11,12], 'mandatory' => false, 'credit_hours' => 3],
        ['name' => 'Physical Education', 'code' => 'PE301', 'department' => 'Physical Education', 'grades' => [9,10,11,12], 'mandatory' => true, 'credit_hours' => 2],
        ['name' => 'Health Education', 'code' => 'HLT301', 'department' => 'Physical Education', 'grades' => [9,10], 'mandatory' => true, 'credit_hours' => 2],
        ['name' => 'Psychology', 'code' => 'PSY301', 'department' => 'Social Studies', 'grades' => [11,12], 'mandatory' => false, 'credit_hours' => 4],
        ['name' => 'Sociology', 'code' => 'SOC301', 'department' => 'Social Studies', 'grades' => [11,12], 'mandatory' => false, 'credit_hours' => 4],
        ['name' => 'Business Studies', 'code' => 'BUS301', 'department' => 'Business', 'grades' => [10,11,12], 'mandatory' => false, 'credit_hours' => 4],
        ['name' => 'Accounting', 'code' => 'ACC301', 'department' => 'Business', 'grades' => [11,12], 'mandatory' => false, 'credit_hours' => 4],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Subjects...');

        $created = 0;
        foreach ($this->subjects as $subjectData) {
            $subject = Subject::firstOrCreate(
                ['code' => $subjectData['code']], // Unique key
                [
                    'name' => $subjectData['name'],
                    'department' => $subjectData['department'],
                    'is_core_subject' => $subjectData['mandatory'],
                    'credit_hours' => $subjectData['credit_hours'],
                    'description' => $this->generateDescription($subjectData),
                    'is_active' => true,
                ]
            );
            
            if ($subject->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command->info("✓ Created {$created} Subjects across all grade levels");
        $this->command->info('✓ Subjects organized by departments: Mathematics, Science, Language Arts, Social Studies, Arts, Technology, etc.');
    }

    /**
     * Generate appropriate description for a subject.
     */
    private function generateDescription(array $subjectData): string
    {
        $descriptions = [
            'Mathematics' => 'Comprehensive mathematics curriculum focusing on problem-solving, critical thinking, and mathematical reasoning.',
            'English Language Arts' => 'Language arts program emphasizing reading comprehension, writing skills, grammar, and literature analysis.',
            'Science' => 'Hands-on science education covering fundamental scientific concepts, experiments, and scientific method.',
            'Social Studies' => 'Study of human society, cultures, history, geography, and civic responsibility.',
            'Physical Education' => 'Physical fitness, sports, health awareness, and teamwork through various athletic activities.',
            'Arts' => 'Creative expression through various art forms, developing artistic skills and cultural appreciation.',
            'Technology' => 'Technology literacy, computer skills, and digital citizenship in the modern world.',
            'World Languages' => 'Foreign language acquisition focusing on communication, culture, and global awareness.',
            'Business' => 'Introduction to business principles, entrepreneurship, and economic concepts.',
        ];

        $department = $subjectData['department'];
        return $descriptions[$department] ?? 'Academic course designed to develop knowledge and skills in ' . strtolower($department) . '.';
    }

    /**
     * Generate appropriate prerequisites for advanced subjects.
     */
    private function generatePrerequisites(array $subjectData): ?string
    {
        $prerequisites = [
            'Algebra II' => 'Completion of Geometry with grade C or better',
            'Pre-Calculus' => 'Completion of Algebra II with grade B or better',
            'AP Calculus' => 'Completion of Pre-Calculus with grade B or better',
            'AP Biology' => 'Completion of Biology and Chemistry with grade B or better',
            'AP Chemistry' => 'Completion of Chemistry and Algebra II with grade B or better',
            'AP Computer Science' => 'Completion of Computer Science I with grade B or better',
            'Spanish II' => 'Completion of Spanish I with grade C or better',
            'French II' => 'Completion of French I with grade C or better',
            'Art II' => 'Completion of Art I with grade C or better',
            'Physics' => 'Completion of Algebra II (concurrent enrollment acceptable)',
            'Chemistry' => 'Completion of Algebra I with grade C or better',
        ];

        return $prerequisites[$subjectData['name']] ?? null;
    }
}