// Employee Module Types

export type EmployeeType = 'full_time' | 'part_time' | 'contract' | 'intern';
export type Gender = 'male' | 'female' | 'other';
export type MaritalStatus = 'single' | 'married';
export type FamilyRelationship = 'spouse' | 'child' | 'father' | 'mother' | 'sibling';
export type AcademicLevel = 'high_school' | 'vocational' | 'associate' | 'bachelor' | 'master' | 'doctorate' | 'other';
export type LanguageProficiency = 'beginner' | 'elementary' | 'intermediate' | 'upper_intermediate' | 'advanced' | 'native';
export type EmploymentType = 'full_time' | 'part_time' | 'contract' | 'freelance' | 'internship';

// Family Member Types
export interface FamilyMember {
    id?: number;
    uuid?: string;
    _key?: number; // For Vue v-for tracking
    relationship: FamilyRelationship;
    name: string;
    gender: Gender | null;
    date_of_birth: string;
    age: number | null;
    occupation: string;
    phone_number: string;
    email: string;
    address: string;
    notes: string;
    is_emergency_contact: boolean;
    is_dependent: boolean;
}

export interface FamilyMemberFormData {
    _key: number;
    id?: number;
    relationship: FamilyRelationship;
    name: string;
    gender: Gender | null;
    date_of_birth: string;
    age: number | null;
    occupation: string;
    phone_number: string;
    email: string;
    address: string;
    notes: string;
    is_emergency_contact: boolean;
    is_dependent: boolean;
}

// Academic Level Types
export interface AcademicLevelData {
    id?: number;
    uuid?: string;
    _key?: number;
    level: AcademicLevel;
    institution: string;
    field_of_study: string;
    degree: string;
    start_date: string;
    end_date: string;
    gpa: number | null;
    certificate: string;
    notes: string;
}

export interface AcademicLevelFormData {
    _key: number;
    id?: number;
    level: AcademicLevel | null;
    institution: string;
    field_of_study: string;
    degree: string;
    start_date: string;
    end_date: string;
    gpa: number | null;
    certificate: string;
    notes: string;
}

// Foreign Language Types
export interface ForeignLanguageData {
    id?: number;
    uuid?: string;
    _key?: number;
    language: string;
    proficiency: LanguageProficiency;
    certificate: string;
    certificate_score: string;
    notes: string;
}

export interface ForeignLanguageFormData {
    _key: number;
    id?: number;
    language: string;
    proficiency: LanguageProficiency | null;
    certificate: string;
    certificate_score: string;
    notes: string;
}

// Job Experience Types
export interface JobExperienceData {
    id?: number;
    uuid?: string;
    _key?: number;
    company: string;
    position: string;
    employment_type: EmploymentType | null;
    province: string;
    city: string;
    start_date: string;
    end_date: string;
    is_current: boolean;
    responsibilities: string;
    achievements: string;
    reason_for_leaving: string;
    notes: string;
}

export interface JobExperienceFormData {
    _key: number;
    id?: number;
    company: string;
    position: string;
    employment_type: EmploymentType | null;
    province: string;
    city: string;
    start_date: string;
    end_date: string;
    is_current: boolean;
    responsibilities: string;
    achievements: string;
    reason_for_leaving: string;
    notes: string;
}

export interface EmployeeUser {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    roles: string[];
}

export interface Employee {
    id: number;
    uuid: string;
    employee_code: string;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string | null;
    phone_number: string | null;
    gender: Gender | null;
    marital_status: MaritalStatus | null;
    date_of_birth: string | null;
    birth_place: string | null;
    ethnicity: string | null;
    current_address: string | null;
    school_id: number | null;
    department_id: number | null;
    position_id: number | null;
    type_employee_id: number | null;
    job_title: string | null;
    employee_type: EmployeeType | null;
    employee_type_label: string | null;
    employee_type_name: string | null;
    salary: number | null;
    hire_date: string | null;
    probation_date: string | null;
    probation_end_date: string | null;
    certificate: string | null;
    certificate_image: string | null;
    certificate_images: string[] | null;
    certificate_code: string | null;
    avatar_url: string | null;
    employee_qr_code: string | null;
    employee_barcode: string | null;
    status: boolean;
    is_on_probation: boolean;
    school_name: string | null;
    department_name: string | null;
    courses_count: number | null;
    // User account data
    user_id: number | null;
    has_account: boolean;
    user: EmployeeUser | null;
    // Attendance counts
    attendance_total: number | null;
    attendance_present: number | null;
    attendance_absent: number | null;
    attendance_late: number | null;
    attendance_on_leave: number | null;
    // Family members
    family_members: FamilyMember[] | null;
    // Academic levels
    academic_levels: AcademicLevelData[] | null;
    // Foreign languages
    foreign_languages: ForeignLanguageData[] | null;
    // Job experiences
    job_experiences: JobExperienceData[] | null;
    created_at: string;
    updated_at: string;
}

export interface EmployeeStats {
    total: number;
    active: number;
    inactive: number;
}

export interface EmployeeAttendanceStats {
    total_records: number;
    present: number;
    absent: number;
    late: number;
    early_leave: number;
    half_day: number;
    on_leave: number;
    date_from: string;
    date_to: string;
}

export interface EmployeeAttendanceStatsPeriod {
    total: number;
    present: number;
    late: number;
    absent?: number;
    on_leave?: number;
    work_hours: number;
    work_hours_formatted: string;
}

export interface RecentAttendance {
    uuid: string;
    date: string | null;
    status: string;
    status_label: string;
    check_in: string | null;
    check_out: string | null;
    work_hours: string;
}

export interface EmployeeDetailedAttendanceStats {
    this_month: EmployeeAttendanceStatsPeriod;
    this_year: EmployeeAttendanceStatsPeriod;
    all_time: {
        total: number;
        work_hours: number;
        work_hours_formatted: string;
    };
    recent: RecentAttendance[];
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
}

export interface EmployeeFilters {
    status?: string;
    search?: string;
    employee_type?: string;
    school_id?: string;
    department_id?: string;
    date_from?: string;
    date_to?: string;
}

export interface EmployeeFormData {
    employee_code: string;
    first_name: string;
    last_name: string;
    email: string;
    phone_number: string;
    gender: Gender | null;
    marital_status: MaritalStatus | null;
    date_of_birth: string;
    birth_place: string;
    ethnicity: string;
    current_address: string;
    school_id: number | null;
    department_id: number | null;
    position_id: number | null;
    type_employee_id: number | null;
    job_title: string;
    employee_type: EmployeeType | null;
    salary: number | null;
    hire_date: string;
    probation_date: string;
    probation_end_date: string;
    certificate: string;
    certificate_image: string;
    certificate_images: string[];
    certificate_code: string;
    avatar_url: string;
    status: boolean;
    // Family members
    family_members: FamilyMemberFormData[];
    // Academic levels
    academic_levels: AcademicLevelFormData[];
    // Foreign languages
    foreign_languages: ForeignLanguageFormData[];
    // Job experiences
    job_experiences: JobExperienceFormData[];
    // Account creation fields
    create_account?: boolean;
    password?: string;
    password_confirmation?: string;
}

export interface MaritalStatusOption {
    value: MaritalStatus;
    label: string;
}

export interface FamilyRelationshipOption {
    value: FamilyRelationship;
    label: string;
}

export interface AcademicLevelOption {
    value: AcademicLevel;
    label: string;
}

export interface LanguageProficiencyOption {
    value: LanguageProficiency;
    label: string;
}

export interface EmploymentTypeOption {
    value: EmploymentType;
    label: string;
}

export interface SchoolOption {
    id: number;
    name: string;
}

export interface DepartmentOption {
    id: number;
    name: string;
}

export interface EmployeeTypeOption {
    value: EmployeeType;
    label: string;
}

export interface EmployeeIndexProps {
    employees: PaginatedResponse<Employee>;
    filters: EmployeeFilters;
    stats: EmployeeStats;
    attendanceStats: EmployeeAttendanceStats;
    schools: SchoolOption[];
}

export interface EmployeeShowProps {
    employee: Employee;
    attendanceStats: EmployeeDetailedAttendanceStats;
}

export interface EmployeeCreateProps {
    schools: SchoolOption[];
    departments: DepartmentOption[];
    employeeTypes: EmployeeTypeOption[];
    maritalStatuses: MaritalStatusOption[];
    relationshipTypes: FamilyRelationshipOption[];
    academicLevels: AcademicLevelOption[];
    languageProficiencies: LanguageProficiencyOption[];
    employmentTypes: EmploymentTypeOption[];
    generatedCode: string;
}

export interface EmployeeEditProps {
    employee: Employee;
    schools: SchoolOption[];
    departments: DepartmentOption[];
    employeeTypes: EmployeeTypeOption[];
    maritalStatuses: MaritalStatusOption[];
    relationshipTypes: FamilyRelationshipOption[];
    academicLevels: AcademicLevelOption[];
    languageProficiencies: LanguageProficiencyOption[];
    employmentTypes: EmploymentTypeOption[];
}

export interface EmployeeDeleteProps {
    employee: Employee;
}

// Employee Type (Model) Types
export interface EmployeeTypeModel {
    id: number;
    uuid: string;
    name: string;
    description: string | null;
    time_start: string | null;
    time_end: string | null;
    status: boolean;
    employees_count: number | null;
    created_at: string;
    updated_at: string;
}

export interface EmployeeTypeStats {
    total: number;
    active: number;
    inactive: number;
}

export interface EmployeeTypeFilters {
    status?: string;
    search?: string;
}

export interface EmployeeTypeFormData {
    name: string;
    description: string;
    time_start: string;
    time_end: string;
    status: boolean;
}

export interface EmployeeTypeIndexProps {
    employeeTypes: PaginatedResponse<EmployeeTypeModel>;
    filters: EmployeeTypeFilters;
    stats: EmployeeTypeStats;
}

export interface EmployeeTypeShowProps {
    employeeType: EmployeeTypeModel;
}

export interface EmployeeTypeCreateProps {}

export interface EmployeeTypeEditProps {
    employeeType: EmployeeTypeModel;
}

export interface EmployeeTypeDeleteProps {
    employeeType: EmployeeTypeModel;
}

// Attendance Types
export type AttendanceStatus = 'present' | 'absent' | 'late' | 'early_leave' | 'half_day' | 'on_leave';
export type CheckInMethod = 'qr_scan' | 'manual' | 'biometric' | 'face_recognition';

export interface Attendance {
    id: number;
    uuid: string;
    employee_id: number;
    employee_name: string | null;
    employee_code: string | null;
    employee_avatar: string | null;
    employee_email: string | null;
    employee_phone: string | null;
    employee_job_title: string | null;
    employee_department: string | null;
    user_id: number | null;
    user_name: string | null;
    user_email: string | null;
    school_id: number | null;
    school_name: string | null;
    department_id: number | null;
    department_name: string | null;
    classroom_id: number | null;
    classroom_name: string | null;
    attendance_date: string;
    attendance_date_formatted: string;
    check_in_time: string | null;
    check_out_time: string | null;
    status: AttendanceStatus;
    status_label: string;
    check_in_method: CheckInMethod;
    check_in_method_label: string;
    check_out_method: CheckInMethod | null;
    check_out_method_label: string | null;
    check_in_location: string | null;
    check_out_location: string | null;
    check_in_coordinates: { lat: number; lng: number } | null;
    check_out_coordinates: { lat: number; lng: number } | null;
    work_hours: number | null;
    work_hours_formatted: string;
    overtime_hours: number | null;
    notes: string | null;
    device_info: string | null;
    ip_address: string | null;
    created_at: string;
    updated_at: string;
}

export interface AttendanceStats {
    total_employees: number;
    present_today: number;
    absent_today: number;
    late_today: number;
    on_leave_today: number;
}

export interface AttendanceFilters {
    search?: string;
    status?: string;
    employee_id?: number;
    department_id?: number;
    date_from?: string;
    date_to?: string;
}

export interface AttendanceFormData {
    employee_id: number | null;
    attendance_date: string;
    check_in_time: string;
    check_out_time: string;
    status: AttendanceStatus;
    department_id: number | null;
    classroom_id: number | null;
    notes: string;
}

export interface SelectOption {
    value: number;
    label: string;
    department?: string;
    department_id?: number;
}

export interface AttendanceIndexProps {
    attendances: PaginatedResponse<Attendance>;
    filters: AttendanceFilters;
    stats: AttendanceStats;
    statuses: Record<AttendanceStatus, string>;
    employeeOptions: { id: number; name: string }[];
    departmentOptions: DepartmentOption[];
}

export interface AttendanceShowProps {
    attendance: Attendance;
    statuses: Record<AttendanceStatus, string>;
    methods: Record<CheckInMethod, string>;
}

export interface AttendanceCreateProps {
    employeeOptions: SelectOption[];
    departmentOptions: SelectOption[];
    statuses: Record<AttendanceStatus, string>;
}

export interface AttendanceEditProps {
    attendance: Attendance;
    statuses: Record<AttendanceStatus, string>;
}

export interface AttendanceScannerProps {
    departmentOptions: SelectOption[];
    classroomOptions: SelectOption[];
}

// Employee Option for selects
export interface EmployeeOption {
    id: number;
    uuid?: string;
    full_name: string;
    employee_code: string;
    avatar_url?: string | null;
}

// Permission Request Types
export type PermissionRequestType = 'leave' | 'overtime' | 'remote' | 'early_leave' | 'late_arrival' | 'other';
export type PermissionRequestStatus = 'pending' | 'approved' | 'rejected';

export interface PermissionRequest {
    id: number;
    uuid: string;
    employee_id: number;
    type: PermissionRequestType;
    type_label: string;
    type_description: string;
    reason: string;
    from_date: string;
    to_date: string;
    total_days: number;
    request_date: string;
    request_date_formatted: string;
    status: PermissionRequestStatus;
    status_label: string;
    reviewed_by: number | null;
    reviewed_at: string | null;
    reviewed_at_formatted: string | null;
    review_note: string | null;
    rejected_status: boolean;
    rejected_reason: string | null;
    employee?: {
        id: number;
        uuid: string;
        full_name: string;
        employee_code: string;
        avatar_url: string | null;
    };
    reviewer?: {
        id: number;
        name: string;
    };
    created_at: string;
    updated_at: string;
}

export interface PermissionRequestStats {
    total: number;
    pending: number;
    approved: number;
    rejected: number;
}

export interface PermissionRequestFilters {
    search?: string;
    employee_id?: number;
    type?: string;
    status?: string;
    date_from?: string;
    date_to?: string;
}

export interface PermissionRequestFormData {
    employee_id: number | null;
    type: PermissionRequestType;
    reason: string;
    from_date: string;
    to_date: string;
}

export interface PermissionRequestIndexProps {
    permissionRequests: PaginatedResponse<PermissionRequest>;
    filters: PermissionRequestFilters;
    stats: PermissionRequestStats;
    employees: EmployeeOption[];
    types: Record<PermissionRequestType, string>;
    typeDescriptions: Record<PermissionRequestType, string>;
    statuses: Record<PermissionRequestStatus, string>;
}

export interface PermissionRequestCreateProps {
    employees: EmployeeOption[];
    types: Record<PermissionRequestType, string>;
    typeDescriptions: Record<PermissionRequestType, string>;
    selectedEmployeeId: number | null;
}

export interface PermissionRequestEditProps {
    permissionRequest: PermissionRequest;
    employees: EmployeeOption[];
    types: Record<PermissionRequestType, string>;
    typeDescriptions: Record<PermissionRequestType, string>;
}

export interface PermissionRequestShowProps {
    permissionRequest: PermissionRequest;
    types: Record<PermissionRequestType, string>;
    statuses: Record<PermissionRequestStatus, string>;
}

export interface PermissionRequestDeleteProps {
    permissionRequest: PermissionRequest;
}

export interface PermissionRequestReviewProps {
    permissionRequest: PermissionRequest;
}

// Self-Service Attendance Types
export interface SelfServiceEmployee {
    id: number;
    uuid: string;
    full_name: string;
    employee_code: string;
    avatar_url: string | null;
    job_title: string | null;
    department_name: string | null;
    employee_type_name: string | null;
}

export interface SelfServiceAttendance {
    id: number;
    uuid: string;
    check_in_time: string | null;
    check_out_time: string | null;
    status: AttendanceStatus;
    status_label: string;
    work_hours: number | null;
    work_hours_formatted: string;
    check_in_location: string | null;
    check_out_location: string | null;
    notes: string | null;
}

export interface SelfServiceState {
    canCheckIn: boolean;
    canCheckOut: boolean;
    isCompleted: boolean;
}

export interface SelfServiceConfig {
    allowManualCheckIn: boolean;
    requireLocation: boolean;
    workStartTime: string;
    workEndTime: string;
    lateThresholdMinutes: number;
}

export interface SelfServiceProps {
    employee: SelfServiceEmployee;
    todayAttendance: SelfServiceAttendance | null;
    state: SelfServiceState;
    config: SelfServiceConfig;
}
