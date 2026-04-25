<?php

namespace Modules\Employee\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeType;
use Modules\Employee\Models\EmployeeFamilyMember;
use Modules\Employee\Models\EmployeeAcademicLevel;
use Modules\Employee\Models\EmployeeForeignLanguage;
use Modules\Employee\Models\EmployeeJobExperience;
use Modules\School\Models\School;
use Modules\School\Models\Department;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get available schools and departments
        $schools = School::where('status', true)->pluck('id')->toArray();
        $departmentsBySchool = [];

        if (!empty($schools)) {
            foreach ($schools as $schoolId) {
                $deps = Department::where('school_id', $schoolId)
                    ->where('status', true)
                    ->pluck('id')
                    ->toArray();
                if (!empty($deps)) {
                    $departmentsBySchool[$schoolId] = $deps;
                }
            }
        }

        $employees = $this->getEmployeesData();
        $employeeCount = Employee::count();

        // Map old employee_type values to EmployeeType names
        $typeMapping = [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'intern' => 'Intern',
        ];

        // Cache employee types
        $employeeTypes = EmployeeType::pluck('id', 'name')->toArray();
        $colors = ['4f46e5', '7c3aed', 'db2777', 'ea580c', '16a34a', '0891b2', '6366f1', 'a855f7'];

        foreach ($employees as $index => $data) {
            $code = sprintf('EMP-%06d', $employeeCount + $index + 1);

            // Generate avatar URL
            $initials = strtoupper(substr($data['first_name'], 0, 1) . substr($data['last_name'], 0, 1));
            $bgColor = $colors[$index % count($colors)];
            $avatarUrl = "https://ui-avatars.com/api/?name={$initials}&background={$bgColor}&color=ffffff&size=200&font-size=0.4&bold=true";

            // Generate certificate image
            $certificateImage = null;
            if (!empty($data['certificate'])) {
                $certificateImage = "https://placehold.co/800x600/1e40af/ffffff?text=" . urlencode($data['certificate']);
            }

            // Get type_employee_id from mapping
            $typeEmployeeId = null;
            if (!empty($data['employee_type']) && isset($typeMapping[$data['employee_type']])) {
                $typeName = $typeMapping[$data['employee_type']];
                $typeEmployeeId = $employeeTypes[$typeName] ?? null;
            }

            // Assign random school and department
            $schoolId = null;
            $departmentId = null;
            if (!empty($schools)) {
                $schoolId = $schools[array_rand($schools)];
                if (isset($departmentsBySchool[$schoolId]) && !empty($departmentsBySchool[$schoolId])) {
                    $departmentId = $departmentsBySchool[$schoolId][array_rand($departmentsBySchool[$schoolId])];
                }
            }

            // Create employee
            $employee = Employee::create([
                'uuid' => (string) Str::uuid(),
                'employee_code' => $code,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'gender' => $data['gender'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'birth_place' => $data['birth_place'] ?? null,
                'ethnicity' => $data['ethnicity'] ?? null,
                'current_address' => $data['current_address'] ?? null,
                'school_id' => $schoolId,
                'department_id' => $departmentId,
                'job_title' => $data['job_title'] ?? null,
                'type_employee_id' => $typeEmployeeId,
                'employee_type' => $data['employee_type'] ?? null,
                'salary' => $data['salary'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,
                'probation_date' => $data['probation_date'] ?? null,
                'probation_end_date' => $data['probation_end_date'] ?? null,
                'certificate' => $data['certificate'] ?? null,
                'certificate_code' => $data['certificate_code'] ?? null,
                'certificate_image' => $certificateImage,
                'avatar_url' => $avatarUrl,
                'status' => $data['status'],
            ]);

            // Create family members
            if (!empty($data['family_members'])) {
                foreach ($data['family_members'] as $familyMember) {
                    EmployeeFamilyMember::create([
                        'uuid' => (string) Str::uuid(),
                        'employee_id' => $employee->id,
                        'relationship' => $familyMember['relationship'],
                        'name' => $familyMember['name'],
                        'gender' => $familyMember['gender'] ?? null,
                        'date_of_birth' => $familyMember['date_of_birth'] ?? null,
                        'occupation' => $familyMember['occupation'] ?? null,
                        'phone_number' => $familyMember['phone_number'] ?? null,
                        'email' => $familyMember['email'] ?? null,
                        'is_emergency_contact' => $familyMember['is_emergency_contact'] ?? false,
                        'is_dependent' => $familyMember['is_dependent'] ?? false,
                    ]);
                }
            }

            // Create academic levels
            if (!empty($data['academic_levels'])) {
                foreach ($data['academic_levels'] as $academic) {
                    EmployeeAcademicLevel::create([
                        'uuid' => (string) Str::uuid(),
                        'employee_id' => $employee->id,
                        'level' => $academic['level'],
                        'institution' => $academic['institution'],
                        'field_of_study' => $academic['field_of_study'] ?? null,
                        'degree' => $academic['degree'] ?? null,
                        'start_date' => $academic['start_date'] ?? null,
                        'end_date' => $academic['end_date'] ?? null,
                        'gpa' => $academic['gpa'] ?? null,
                        'certificate' => $academic['certificate'] ?? null,
                        'notes' => $academic['notes'] ?? null,
                    ]);
                }
            }

            // Create foreign languages
            if (!empty($data['foreign_languages'])) {
                foreach ($data['foreign_languages'] as $language) {
                    EmployeeForeignLanguage::create([
                        'uuid' => (string) Str::uuid(),
                        'employee_id' => $employee->id,
                        'language' => $language['language'],
                        'proficiency' => $language['proficiency'],
                        'certificate' => $language['certificate'] ?? null,
                        'certificate_score' => $language['certificate_score'] ?? null,
                        'notes' => $language['notes'] ?? null,
                    ]);
                }
            }

            // Create job experiences
            if (!empty($data['job_experiences'])) {
                foreach ($data['job_experiences'] as $experience) {
                    EmployeeJobExperience::create([
                        'uuid' => (string) Str::uuid(),
                        'employee_id' => $employee->id,
                        'company' => $experience['company'],
                        'position' => $experience['position'] ?? null,
                        'employment_type' => $experience['employment_type'] ?? null,
                        'province' => $experience['province'] ?? null,
                        'city' => $experience['city'] ?? null,
                        'start_date' => $experience['start_date'] ?? null,
                        'end_date' => $experience['end_date'] ?? null,
                        'is_current' => $experience['is_current'] ?? false,
                        'responsibilities' => $experience['responsibilities'] ?? null,
                        'achievements' => $experience['achievements'] ?? null,
                        'reason_for_leaving' => $experience['reason_for_leaving'] ?? null,
                    ]);
                }
            }
        }

        $this->command->info('Created ' . count($employees) . ' employees with full profiles.');
    }

    /**
     * Get employees data with all relationships.
     */
    private function getEmployeesData(): array
    {
        return [
            // Employee 1: John Smith - Married with children
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone_number' => '+1 234 567 8901',
                'gender' => 'male',
                'marital_status' => 'married',
                'date_of_birth' => '1990-05-15',
                'birth_place' => 'New York, USA',
                'ethnicity' => 'American',
                'current_address' => '<p>123 Main Street, New York, NY 10001</p>',
                'job_title' => 'Software Engineer',
                'employee_type' => 'full_time',
                'salary' => 75000.00,
                'hire_date' => '2022-01-15',
                'status' => true,
                'family_members' => [
                    [
                        'relationship' => 'father',
                        'name' => 'Robert Smith',
                        'gender' => 'male',
                        'date_of_birth' => '1960-03-20',
                        'occupation' => 'Retired Engineer',
                        'phone_number' => '+1 234 567 1001',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'mother',
                        'name' => 'Mary Smith',
                        'gender' => 'female',
                        'date_of_birth' => '1962-07-15',
                        'occupation' => 'Teacher',
                        'phone_number' => '+1 234 567 1002',
                        'is_emergency_contact' => false,
                    ],
                    [
                        'relationship' => 'spouse',
                        'name' => 'Jane Smith',
                        'gender' => 'female',
                        'date_of_birth' => '1992-08-25',
                        'occupation' => 'Marketing Manager',
                        'phone_number' => '+1 234 567 1003',
                        'email' => 'jane.smith@example.com',
                        'is_emergency_contact' => true,
                        'is_dependent' => false,
                    ],
                    [
                        'relationship' => 'child',
                        'name' => 'Tommy Smith',
                        'gender' => 'male',
                        'date_of_birth' => '2018-04-10',
                        'occupation' => 'Student',
                        'is_dependent' => true,
                    ],
                    [
                        'relationship' => 'child',
                        'name' => 'Emma Smith',
                        'gender' => 'female',
                        'date_of_birth' => '2020-09-22',
                        'occupation' => 'Student',
                        'is_dependent' => true,
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'MIT',
                        'field_of_study' => 'Computer Science',
                        'degree' => 'Bachelor of Science',
                        'start_date' => '2008-09-01',
                        'end_date' => '2012-06-15',
                        'gpa' => 3.85,
                        'certificate' => 'BSc Computer Science',
                    ],
                    [
                        'level' => 'master',
                        'institution' => 'Stanford University',
                        'field_of_study' => 'Software Engineering',
                        'degree' => 'Master of Science',
                        'start_date' => '2012-09-01',
                        'end_date' => '2014-06-15',
                        'gpa' => 3.92,
                        'certificate' => 'MSc Software Engineering',
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'Spanish',
                        'proficiency' => 'intermediate',
                        'certificate' => 'DELE',
                        'certificate_score' => 'B1',
                    ],
                    [
                        'language' => 'French',
                        'proficiency' => 'beginner',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'Google Inc.',
                        'position' => 'Junior Software Engineer',
                        'employment_type' => 'full_time',
                        'province' => 'California',
                        'city' => 'Mountain View',
                        'start_date' => '2014-07-01',
                        'end_date' => '2018-06-30',
                        'is_current' => false,
                        'responsibilities' => 'Developed backend services for Google Cloud Platform',
                        'reason_for_leaving' => 'Career advancement',
                    ],
                    [
                        'company' => 'Amazon',
                        'position' => 'Software Engineer II',
                        'employment_type' => 'full_time',
                        'province' => 'Washington',
                        'city' => 'Seattle',
                        'start_date' => '2018-07-15',
                        'end_date' => '2022-01-10',
                        'is_current' => false,
                        'responsibilities' => 'Led development of AWS Lambda features',
                        'reason_for_leaving' => 'Better opportunity',
                    ],
                ],
            ],

            // Employee 2: Sarah Johnson - Single
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone_number' => '+1 234 567 8902',
                'gender' => 'female',
                'marital_status' => 'single',
                'date_of_birth' => '1988-08-22',
                'birth_place' => 'Los Angeles, USA',
                'ethnicity' => 'American',
                'current_address' => '<p>456 Oak Avenue, Los Angeles, CA 90001</p>',
                'job_title' => 'Project Manager',
                'employee_type' => 'full_time',
                'salary' => 85000.00,
                'hire_date' => '2021-06-01',
                'certificate' => 'PMP Certification',
                'certificate_code' => 'PMP-2021-001',
                'status' => true,
                'family_members' => [
                    [
                        'relationship' => 'father',
                        'name' => 'Michael Johnson',
                        'gender' => 'male',
                        'date_of_birth' => '1958-11-05',
                        'occupation' => 'Business Owner',
                        'phone_number' => '+1 234 567 2001',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'mother',
                        'name' => 'Patricia Johnson',
                        'gender' => 'female',
                        'date_of_birth' => '1961-02-18',
                        'occupation' => 'Nurse',
                        'phone_number' => '+1 234 567 2002',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'sibling',
                        'name' => 'David Johnson',
                        'gender' => 'male',
                        'date_of_birth' => '1991-05-30',
                        'occupation' => 'Lawyer',
                        'phone_number' => '+1 234 567 2003',
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'UCLA',
                        'field_of_study' => 'Business Administration',
                        'degree' => 'Bachelor of Business Administration',
                        'start_date' => '2006-09-01',
                        'end_date' => '2010-06-15',
                        'gpa' => 3.75,
                    ],
                    [
                        'level' => 'master',
                        'institution' => 'Harvard Business School',
                        'field_of_study' => 'MBA',
                        'degree' => 'Master of Business Administration',
                        'start_date' => '2015-09-01',
                        'end_date' => '2017-06-15',
                        'gpa' => 3.88,
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'Mandarin Chinese',
                        'proficiency' => 'upper_intermediate',
                        'certificate' => 'HSK',
                        'certificate_score' => 'Level 5',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'Deloitte',
                        'position' => 'Business Analyst',
                        'employment_type' => 'full_time',
                        'province' => 'New York',
                        'city' => 'New York City',
                        'start_date' => '2010-08-01',
                        'end_date' => '2015-07-31',
                        'is_current' => false,
                        'responsibilities' => 'Conducted business analysis for Fortune 500 clients',
                        'reason_for_leaving' => 'Pursuing MBA',
                    ],
                    [
                        'company' => 'Microsoft',
                        'position' => 'Senior Project Manager',
                        'employment_type' => 'full_time',
                        'province' => 'California',
                        'city' => 'San Francisco',
                        'start_date' => '2017-08-01',
                        'end_date' => '2021-05-15',
                        'is_current' => false,
                        'responsibilities' => 'Managed Azure cloud migration projects',
                        'reason_for_leaving' => 'New challenge',
                    ],
                ],
            ],

            // Employee 3: Michael Brown - Single, On Probation
            [
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@example.com',
                'phone_number' => '+1 234 567 8903',
                'gender' => 'male',
                'marital_status' => 'single',
                'date_of_birth' => '1995-03-10',
                'birth_place' => 'Chicago, USA',
                'ethnicity' => 'African American',
                'current_address' => '<p>789 Pine Road, Chicago, IL 60601</p>',
                'job_title' => 'UI/UX Designer',
                'employee_type' => 'full_time',
                'salary' => 65000.00,
                'hire_date' => '2023-02-20',
                'probation_date' => '2023-02-20',
                'probation_end_date' => '2023-08-20',
                'status' => true,
                'family_members' => [
                    [
                        'relationship' => 'father',
                        'name' => 'James Brown',
                        'gender' => 'male',
                        'date_of_birth' => '1965-09-12',
                        'occupation' => 'Accountant',
                        'phone_number' => '+1 234 567 3001',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'mother',
                        'name' => 'Linda Brown',
                        'gender' => 'female',
                        'date_of_birth' => '1968-04-25',
                        'occupation' => 'Homemaker',
                        'phone_number' => '+1 234 567 3002',
                    ],
                    [
                        'relationship' => 'sibling',
                        'name' => 'Jennifer Brown',
                        'gender' => 'female',
                        'date_of_birth' => '1998-07-08',
                        'occupation' => 'College Student',
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'Art Institute of Chicago',
                        'field_of_study' => 'Graphic Design',
                        'degree' => 'Bachelor of Fine Arts',
                        'start_date' => '2013-09-01',
                        'end_date' => '2017-06-15',
                        'gpa' => 3.65,
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'Japanese',
                        'proficiency' => 'elementary',
                        'notes' => 'Currently studying',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'Creative Agency XYZ',
                        'position' => 'Junior Designer',
                        'employment_type' => 'full_time',
                        'province' => 'Illinois',
                        'city' => 'Chicago',
                        'start_date' => '2017-07-01',
                        'end_date' => '2020-12-31',
                        'is_current' => false,
                        'responsibilities' => 'Created visual designs for clients',
                        'reason_for_leaving' => 'Seeking growth',
                    ],
                    [
                        'company' => 'Startup Inc.',
                        'position' => 'UI Designer',
                        'employment_type' => 'contract',
                        'province' => 'Illinois',
                        'city' => 'Chicago',
                        'start_date' => '2021-01-15',
                        'end_date' => '2023-02-10',
                        'is_current' => false,
                        'responsibilities' => 'Designed mobile app interfaces',
                        'reason_for_leaving' => 'Contract ended',
                    ],
                ],
            ],

            // Employee 4: Emily Davis - Married
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily.davis@example.com',
                'phone_number' => '+1 234 567 8904',
                'gender' => 'female',
                'marital_status' => 'married',
                'date_of_birth' => '1992-11-28',
                'birth_place' => 'Houston, USA',
                'ethnicity' => 'Hispanic',
                'current_address' => '<p>321 Elm Street, Houston, TX 77001</p>',
                'job_title' => 'Marketing Specialist',
                'employee_type' => 'part_time',
                'salary' => 35000.00,
                'hire_date' => '2023-05-10',
                'status' => true,
                'family_members' => [
                    [
                        'relationship' => 'father',
                        'name' => 'Carlos Davis',
                        'gender' => 'male',
                        'date_of_birth' => '1962-06-18',
                        'occupation' => 'Restaurant Owner',
                        'phone_number' => '+1 234 567 4001',
                    ],
                    [
                        'relationship' => 'mother',
                        'name' => 'Maria Davis',
                        'gender' => 'female',
                        'date_of_birth' => '1965-12-03',
                        'occupation' => 'Chef',
                        'phone_number' => '+1 234 567 4002',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'spouse',
                        'name' => 'Mark Davis',
                        'gender' => 'male',
                        'date_of_birth' => '1990-04-15',
                        'occupation' => 'Civil Engineer',
                        'phone_number' => '+1 234 567 4003',
                        'email' => 'mark.davis@example.com',
                        'is_emergency_contact' => true,
                        'is_dependent' => false,
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'University of Texas',
                        'field_of_study' => 'Marketing',
                        'degree' => 'Bachelor of Business',
                        'start_date' => '2010-09-01',
                        'end_date' => '2014-06-15',
                        'gpa' => 3.55,
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'Spanish',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'Portuguese',
                        'proficiency' => 'intermediate',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'Marketing Solutions LLC',
                        'position' => 'Marketing Coordinator',
                        'employment_type' => 'full_time',
                        'province' => 'Texas',
                        'city' => 'Houston',
                        'start_date' => '2014-08-01',
                        'end_date' => '2019-03-31',
                        'is_current' => false,
                        'responsibilities' => 'Coordinated marketing campaigns',
                        'reason_for_leaving' => 'Career break',
                    ],
                ],
            ],

            // Employee 5: David Wilson - Married, Senior
            [
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'email' => 'david.wilson@example.com',
                'phone_number' => '+1 234 567 8905',
                'gender' => 'male',
                'marital_status' => 'married',
                'date_of_birth' => '1985-07-04',
                'birth_place' => 'Phoenix, USA',
                'ethnicity' => 'American',
                'current_address' => '<p>654 Cedar Lane, Phoenix, AZ 85001</p>',
                'job_title' => 'Senior Developer',
                'employee_type' => 'full_time',
                'salary' => 95000.00,
                'hire_date' => '2019-09-15',
                'certificate' => 'AWS Solutions Architect',
                'certificate_code' => 'AWS-SA-2023-001',
                'status' => true,
                'family_members' => [
                    [
                        'relationship' => 'father',
                        'name' => 'William Wilson',
                        'gender' => 'male',
                        'date_of_birth' => '1955-01-20',
                        'occupation' => 'Retired Military',
                        'phone_number' => '+1 234 567 5001',
                    ],
                    [
                        'relationship' => 'mother',
                        'name' => 'Elizabeth Wilson',
                        'gender' => 'female',
                        'date_of_birth' => '1958-08-10',
                        'occupation' => 'Retired Teacher',
                        'phone_number' => '+1 234 567 5002',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'spouse',
                        'name' => 'Jennifer Wilson',
                        'gender' => 'female',
                        'date_of_birth' => '1987-03-22',
                        'occupation' => 'Doctor',
                        'phone_number' => '+1 234 567 5003',
                        'email' => 'jennifer.wilson@hospital.com',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'child',
                        'name' => 'Lucas Wilson',
                        'gender' => 'male',
                        'date_of_birth' => '2015-11-08',
                        'occupation' => 'Student',
                        'is_dependent' => true,
                    ],
                    [
                        'relationship' => 'sibling',
                        'name' => 'Daniel Wilson',
                        'gender' => 'male',
                        'date_of_birth' => '1988-02-14',
                        'occupation' => 'Pilot',
                        'phone_number' => '+1 234 567 5004',
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'Arizona State University',
                        'field_of_study' => 'Computer Engineering',
                        'degree' => 'Bachelor of Science',
                        'start_date' => '2003-09-01',
                        'end_date' => '2007-06-15',
                        'gpa' => 3.78,
                    ],
                    [
                        'level' => 'master',
                        'institution' => 'Carnegie Mellon University',
                        'field_of_study' => 'Software Engineering',
                        'degree' => 'Master of Science',
                        'start_date' => '2010-09-01',
                        'end_date' => '2012-06-15',
                        'gpa' => 3.95,
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'German',
                        'proficiency' => 'advanced',
                        'certificate' => 'Goethe-Zertifikat',
                        'certificate_score' => 'C1',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'Intel Corporation',
                        'position' => 'Software Engineer',
                        'employment_type' => 'full_time',
                        'province' => 'Arizona',
                        'city' => 'Chandler',
                        'start_date' => '2007-08-01',
                        'end_date' => '2012-07-31',
                        'is_current' => false,
                        'responsibilities' => 'Developed firmware for processors',
                        'reason_for_leaving' => 'Graduate school',
                    ],
                    [
                        'company' => 'Oracle',
                        'position' => 'Senior Software Engineer',
                        'employment_type' => 'full_time',
                        'province' => 'California',
                        'city' => 'Redwood City',
                        'start_date' => '2012-09-01',
                        'end_date' => '2019-09-01',
                        'is_current' => false,
                        'responsibilities' => 'Led database optimization team',
                        'reason_for_leaving' => 'New opportunity',
                    ],
                ],
            ],

            // Employee 6: Jessica Martinez - Single
            [
                'first_name' => 'Jessica',
                'last_name' => 'Martinez',
                'email' => 'jessica.martinez@example.com',
                'phone_number' => '+1 234 567 8906',
                'gender' => 'female',
                'marital_status' => 'single',
                'date_of_birth' => '1993-02-14',
                'birth_place' => 'San Diego, USA',
                'ethnicity' => 'Hispanic',
                'current_address' => '<p>987 Maple Drive, San Diego, CA 92101</p>',
                'job_title' => 'HR Manager',
                'employee_type' => 'full_time',
                'salary' => 70000.00,
                'hire_date' => '2020-03-01',
                'certificate' => 'SHRM Certified Professional',
                'certificate_code' => 'SHRM-CP-2022-456',
                'status' => true,
                'family_members' => [
                    [
                        'relationship' => 'father',
                        'name' => 'Antonio Martinez',
                        'gender' => 'male',
                        'date_of_birth' => '1963-10-30',
                        'occupation' => 'Construction Manager',
                        'phone_number' => '+1 234 567 6001',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'mother',
                        'name' => 'Rosa Martinez',
                        'gender' => 'female',
                        'date_of_birth' => '1966-05-22',
                        'occupation' => 'Real Estate Agent',
                        'phone_number' => '+1 234 567 6002',
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'San Diego State University',
                        'field_of_study' => 'Human Resources',
                        'degree' => 'Bachelor of Business',
                        'start_date' => '2011-09-01',
                        'end_date' => '2015-06-15',
                        'gpa' => 3.62,
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'Spanish',
                        'proficiency' => 'native',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'HR Solutions Inc.',
                        'position' => 'HR Coordinator',
                        'employment_type' => 'full_time',
                        'province' => 'California',
                        'city' => 'San Diego',
                        'start_date' => '2015-07-01',
                        'end_date' => '2018-02-28',
                        'is_current' => false,
                        'responsibilities' => 'Managed recruitment and onboarding',
                        'reason_for_leaving' => 'Promotion opportunity',
                    ],
                    [
                        'company' => 'Tech Corp',
                        'position' => 'HR Specialist',
                        'employment_type' => 'full_time',
                        'province' => 'California',
                        'city' => 'Los Angeles',
                        'start_date' => '2018-03-15',
                        'end_date' => '2020-02-28',
                        'is_current' => false,
                        'responsibilities' => 'Handled employee relations',
                        'reason_for_leaving' => 'Better role',
                    ],
                ],
            ],

            // Employee 7: James Anderson - Contract
            [
                'first_name' => 'James',
                'last_name' => 'Anderson',
                'email' => 'james.anderson@example.com',
                'phone_number' => '+1 234 567 8907',
                'gender' => 'male',
                'marital_status' => 'single',
                'date_of_birth' => '1991-09-30',
                'birth_place' => 'Dallas, USA',
                'ethnicity' => 'American',
                'current_address' => '<p>147 Birch Court, Dallas, TX 75201</p>',
                'job_title' => 'DevOps Engineer',
                'employee_type' => 'contract',
                'salary' => 80000.00,
                'hire_date' => '2023-07-01',
                'status' => true,
                'family_members' => [
                    [
                        'relationship' => 'father',
                        'name' => 'Richard Anderson',
                        'gender' => 'male',
                        'date_of_birth' => '1961-04-05',
                        'occupation' => 'IT Consultant',
                        'phone_number' => '+1 234 567 7001',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'mother',
                        'name' => 'Susan Anderson',
                        'gender' => 'female',
                        'date_of_birth' => '1964-09-18',
                        'occupation' => 'Financial Advisor',
                        'phone_number' => '+1 234 567 7002',
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'University of Texas at Dallas',
                        'field_of_study' => 'Information Technology',
                        'degree' => 'Bachelor of Science',
                        'start_date' => '2009-09-01',
                        'end_date' => '2013-06-15',
                        'gpa' => 3.70,
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'CloudOps Inc.',
                        'position' => 'Systems Administrator',
                        'employment_type' => 'full_time',
                        'province' => 'Texas',
                        'city' => 'Austin',
                        'start_date' => '2013-08-01',
                        'end_date' => '2018-05-31',
                        'is_current' => false,
                        'responsibilities' => 'Managed cloud infrastructure',
                        'reason_for_leaving' => 'Career change to DevOps',
                    ],
                    [
                        'company' => 'DevOps Solutions',
                        'position' => 'DevOps Engineer',
                        'employment_type' => 'contract',
                        'province' => 'Texas',
                        'city' => 'Dallas',
                        'start_date' => '2018-06-15',
                        'end_date' => '2023-06-30',
                        'is_current' => false,
                        'responsibilities' => 'Implemented CI/CD pipelines',
                        'reason_for_leaving' => 'Contract ended',
                    ],
                ],
            ],

            // Employee 8: Ashley Taylor - Intern
            [
                'first_name' => 'Ashley',
                'last_name' => 'Taylor',
                'email' => 'ashley.taylor@example.com',
                'phone_number' => '+1 234 567 8908',
                'gender' => 'female',
                'marital_status' => 'single',
                'date_of_birth' => '1997-12-05',
                'birth_place' => 'Seattle, USA',
                'ethnicity' => 'Asian American',
                'current_address' => '<p>258 Walnut Street, Seattle, WA 98101</p>',
                'job_title' => 'Junior Developer',
                'employee_type' => 'intern',
                'salary' => 40000.00,
                'hire_date' => '2024-01-15',
                'probation_date' => '2024-01-15',
                'probation_end_date' => '2024-07-15',
                'status' => true,
                'family_members' => [
                    [
                        'relationship' => 'father',
                        'name' => 'Kevin Taylor',
                        'gender' => 'male',
                        'date_of_birth' => '1968-07-12',
                        'occupation' => 'Software Architect',
                        'phone_number' => '+1 234 567 8001',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'mother',
                        'name' => 'Michelle Taylor',
                        'gender' => 'female',
                        'date_of_birth' => '1970-03-28',
                        'occupation' => 'UX Researcher',
                        'phone_number' => '+1 234 567 8002',
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'University of Washington',
                        'field_of_study' => 'Computer Science',
                        'degree' => 'Bachelor of Science',
                        'start_date' => '2019-09-01',
                        'end_date' => '2023-06-15',
                        'gpa' => 3.82,
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'Korean',
                        'proficiency' => 'intermediate',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'Tech Startup ABC',
                        'position' => 'Software Intern',
                        'employment_type' => 'internship',
                        'province' => 'Washington',
                        'city' => 'Seattle',
                        'start_date' => '2022-06-01',
                        'end_date' => '2022-08-31',
                        'is_current' => false,
                        'responsibilities' => 'Assisted in frontend development',
                        'reason_for_leaving' => 'Internship ended',
                    ],
                ],
            ],

            // Employee 9: Robert Thomas - Inactive
            [
                'first_name' => 'Robert',
                'last_name' => 'Thomas',
                'email' => 'robert.thomas@example.com',
                'phone_number' => '+1 234 567 8909',
                'gender' => 'male',
                'marital_status' => 'married',
                'date_of_birth' => '1987-04-18',
                'birth_place' => 'Boston, USA',
                'ethnicity' => 'American',
                'current_address' => '<p>369 Spruce Avenue, Boston, MA 02101</p>',
                'job_title' => 'Data Analyst',
                'employee_type' => 'full_time',
                'salary' => 72000.00,
                'hire_date' => '2021-11-01',
                'status' => false,
                'family_members' => [
                    [
                        'relationship' => 'spouse',
                        'name' => 'Catherine Thomas',
                        'gender' => 'female',
                        'date_of_birth' => '1989-08-20',
                        'occupation' => 'Pharmacist',
                        'phone_number' => '+1 234 567 9003',
                        'is_emergency_contact' => true,
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'Boston University',
                        'field_of_study' => 'Statistics',
                        'degree' => 'Bachelor of Science',
                        'start_date' => '2005-09-01',
                        'end_date' => '2009-06-15',
                        'gpa' => 3.58,
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'Data Analytics Corp',
                        'position' => 'Data Analyst',
                        'employment_type' => 'full_time',
                        'province' => 'Massachusetts',
                        'city' => 'Boston',
                        'start_date' => '2009-08-01',
                        'end_date' => '2021-10-31',
                        'is_current' => false,
                        'responsibilities' => 'Analyzed business data and created reports',
                        'reason_for_leaving' => 'New opportunity',
                    ],
                ],
            ],

            // Employee 10: Amanda Garcia - Active
            [
                'first_name' => 'Amanda',
                'last_name' => 'Garcia',
                'email' => 'amanda.garcia@example.com',
                'phone_number' => '+1 234 567 8910',
                'gender' => 'female',
                'marital_status' => 'married',
                'date_of_birth' => '1994-06-25',
                'birth_place' => 'Miami, USA',
                'ethnicity' => 'Hispanic',
                'current_address' => '<p>741 Palm Boulevard, Miami, FL 33101</p>',
                'job_title' => 'Account Manager',
                'employee_type' => 'full_time',
                'salary' => 68000.00,
                'hire_date' => '2022-08-15',
                'certificate' => 'PMP Certification',
                'certificate_code' => 'PMP-2023-789',
                'status' => true,
                'family_members' => [
                    [
                        'relationship' => 'father',
                        'name' => 'Roberto Garcia',
                        'gender' => 'male',
                        'date_of_birth' => '1960-11-15',
                        'occupation' => 'Retired Banker',
                        'phone_number' => '+1 234 567 0001',
                    ],
                    [
                        'relationship' => 'mother',
                        'name' => 'Isabella Garcia',
                        'gender' => 'female',
                        'date_of_birth' => '1963-02-28',
                        'occupation' => 'Interior Designer',
                        'phone_number' => '+1 234 567 0002',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'spouse',
                        'name' => 'Daniel Garcia',
                        'gender' => 'male',
                        'date_of_birth' => '1992-09-10',
                        'occupation' => 'Architect',
                        'phone_number' => '+1 234 567 0003',
                        'email' => 'daniel.garcia@architect.com',
                        'is_emergency_contact' => true,
                    ],
                    [
                        'relationship' => 'child',
                        'name' => 'Sofia Garcia',
                        'gender' => 'female',
                        'date_of_birth' => '2021-03-15',
                        'occupation' => 'Baby',
                        'is_dependent' => true,
                    ],
                    [
                        'relationship' => 'sibling',
                        'name' => 'Marco Garcia',
                        'gender' => 'male',
                        'date_of_birth' => '1997-07-20',
                        'occupation' => 'Medical Student',
                        'phone_number' => '+1 234 567 0004',
                    ],
                    [
                        'relationship' => 'sibling',
                        'name' => 'Lucia Garcia',
                        'gender' => 'female',
                        'date_of_birth' => '2000-12-05',
                        'occupation' => 'College Student',
                        'phone_number' => '+1 234 567 0005',
                    ],
                ],
                'academic_levels' => [
                    [
                        'level' => 'bachelor',
                        'institution' => 'University of Miami',
                        'field_of_study' => 'Business Management',
                        'degree' => 'Bachelor of Business Administration',
                        'start_date' => '2012-09-01',
                        'end_date' => '2016-06-15',
                        'gpa' => 3.72,
                    ],
                    [
                        'level' => 'master',
                        'institution' => 'Florida International University',
                        'field_of_study' => 'Project Management',
                        'degree' => 'Master of Science',
                        'start_date' => '2018-09-01',
                        'end_date' => '2020-06-15',
                        'gpa' => 3.85,
                    ],
                ],
                'foreign_languages' => [
                    [
                        'language' => 'English',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'Spanish',
                        'proficiency' => 'native',
                    ],
                    [
                        'language' => 'Portuguese',
                        'proficiency' => 'advanced',
                        'certificate' => 'CELPE-Bras',
                        'certificate_score' => 'Advanced',
                    ],
                    [
                        'language' => 'Italian',
                        'proficiency' => 'intermediate',
                    ],
                ],
                'job_experiences' => [
                    [
                        'company' => 'Financial Services Inc.',
                        'position' => 'Account Executive',
                        'employment_type' => 'full_time',
                        'province' => 'Florida',
                        'city' => 'Miami',
                        'start_date' => '2016-08-01',
                        'end_date' => '2020-07-31',
                        'is_current' => false,
                        'responsibilities' => 'Managed client accounts and relationships',
                        'reason_for_leaving' => 'Graduate school',
                    ],
                    [
                        'company' => 'Global Consulting Group',
                        'position' => 'Senior Account Manager',
                        'employment_type' => 'full_time',
                        'province' => 'Florida',
                        'city' => 'Miami',
                        'start_date' => '2020-09-01',
                        'end_date' => '2022-08-01',
                        'is_current' => false,
                        'responsibilities' => 'Led account management team',
                        'reason_for_leaving' => 'Better opportunity',
                    ],
                ],
            ],
        ];
    }
}
