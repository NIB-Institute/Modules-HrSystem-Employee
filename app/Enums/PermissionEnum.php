<?php

namespace Modules\Employee\Enums;

/**
 * Single source of truth for every permission the Employee module owns.
 *
 * Use ::value (e.g. PermissionEnum::EMPLOYEES_VIEW_ANY->value) wherever
 * Spatie permission names are required: middleware, route guards,
 * MenuService registrations, FormRequests, Policies. Never bare strings.
 *
 * RolesAndPermissionsSeeder reads ::values() to seed the database.
 */
enum PermissionEnum: string
{
    // ----- employees -----
    case EMPLOYEES_VIEW = 'employees.view';
    case EMPLOYEES_VIEW_ANY = 'employees.view_any';
    case EMPLOYEES_CREATE = 'employees.create';
    case EMPLOYEES_UPDATE = 'employees.update';
    case EMPLOYEES_DELETE = 'employees.delete';
    case EMPLOYEES_RESTORE = 'employees.restore';
    case EMPLOYEES_FORCE_DELETE = 'employees.force_delete';
    case EMPLOYEES_EXPORT = 'employees.export';
    case EMPLOYEES_IMPORT = 'employees.import';
    case EMPLOYEES_ASSIGN_DEPARTMENT = 'employees.assign_department';
    case EMPLOYEES_MANAGE_SCHEDULE = 'employees.manage_schedule';
    case EMPLOYEES_VIEW_ATTENDANCE = 'employees.view_attendance';
    case EMPLOYEES_GENERATE_QR = 'employees.generate_qr';
    case EMPLOYEES_CREATE_ACCOUNT = 'employees.create_account';
    case EMPLOYEES_CHANGE_PASSWORD = 'employees.change_password';
    case EMPLOYEES_MANAGE_ACCOUNT = 'employees.manage_account';
    case EMPLOYEES_BULK_DELETE = 'employees.bulk_delete';
    case EMPLOYEES_TOGGLE_STATUS = 'employees.toggle_status';
    case EMPLOYEES_MANAGE_TELEGRAM = 'employees.manage_telegram';

    // ----- employee_documents -----
    case EMPLOYEE_DOCUMENTS_VIEW = 'employee_documents.view';
    case EMPLOYEE_DOCUMENTS_VIEW_ANY = 'employee_documents.view_any';
    case EMPLOYEE_DOCUMENTS_CREATE = 'employee_documents.create';
    case EMPLOYEE_DOCUMENTS_UPDATE = 'employee_documents.update';
    case EMPLOYEE_DOCUMENTS_DELETE = 'employee_documents.delete';
    case EMPLOYEE_DOCUMENTS_DOWNLOAD = 'employee_documents.download';

    // ----- employee_types -----
    case EMPLOYEE_TYPES_VIEW = 'employee_types.view';
    case EMPLOYEE_TYPES_VIEW_ANY = 'employee_types.view_any';
    case EMPLOYEE_TYPES_CREATE = 'employee_types.create';
    case EMPLOYEE_TYPES_UPDATE = 'employee_types.update';
    case EMPLOYEE_TYPES_DELETE = 'employee_types.delete';
    case EMPLOYEE_TYPES_RESTORE = 'employee_types.restore';
    case EMPLOYEE_TYPES_FORCE_DELETE = 'employee_types.force_delete';
    case EMPLOYEE_TYPES_EXPORT = 'employee_types.export';
    case EMPLOYEE_TYPES_IMPORT = 'employee_types.import';

    // ----- attendances -----
    case ATTENDANCES_VIEW = 'attendances.view';
    case ATTENDANCES_VIEW_ANY = 'attendances.view_any';
    case ATTENDANCES_CREATE = 'attendances.create';
    case ATTENDANCES_UPDATE = 'attendances.update';
    case ATTENDANCES_DELETE = 'attendances.delete';
    case ATTENDANCES_RESTORE = 'attendances.restore';
    case ATTENDANCES_FORCE_DELETE = 'attendances.force_delete';
    case ATTENDANCES_CHECK_IN = 'attendances.check_in';
    case ATTENDANCES_CHECK_OUT = 'attendances.check_out';
    case ATTENDANCES_SCAN_QR = 'attendances.scan_qr';
    case ATTENDANCES_EXPORT = 'attendances.export';
    case ATTENDANCES_IMPORT = 'attendances.import';
    case ATTENDANCES_VIEW_ANALYTICS = 'attendances.view_analytics';
    case ATTENDANCES_VIEW_REPORTS = 'attendances.view_reports';
    case ATTENDANCES_BULK_UPDATE = 'attendances.bulk_update';
    case ATTENDANCES_BULK_DELETE = 'attendances.bulk_delete';

    // ----- locations -----
    case LOCATIONS_VIEW = 'locations.view';
    case LOCATIONS_VIEW_ANY = 'locations.view_any';
    case LOCATIONS_CREATE = 'locations.create';
    case LOCATIONS_UPDATE = 'locations.update';
    case LOCATIONS_DELETE = 'locations.delete';
    case LOCATIONS_RESTORE = 'locations.restore';
    case LOCATIONS_FORCE_DELETE = 'locations.force_delete';
    case LOCATIONS_EXPORT = 'locations.export';
    case LOCATIONS_IMPORT = 'locations.import';
    case LOCATIONS_MANAGE_SCHEDULE = 'locations.manage_schedule';
    case LOCATIONS_TOGGLE_STATUS = 'locations.toggle_status';
    case LOCATIONS_VIEW_MAP = 'locations.view_map';
    case LOCATIONS_GENERATE_QR = 'locations.generate_qr';
    case LOCATIONS_SCAN_QR = 'locations.scan_qr';
    case LOCATIONS_ASSIGN_DEPARTMENT = 'locations.assign_department';
    case LOCATIONS_VIEW_ANALYTICS = 'locations.view_analytics';
    case LOCATIONS_MANAGE_GEOFENCE = 'locations.manage_geofence';
    case LOCATIONS_MANAGE_POLYGON = 'locations.manage_polygon';
    case LOCATIONS_VIEW_SCANS = 'locations.view_scans';

    // ----- attendance_scans -----
    case ATTENDANCE_SCANS_VIEW = 'attendance_scans.view';
    case ATTENDANCE_SCANS_VIEW_ANY = 'attendance_scans.view_any';
    case ATTENDANCE_SCANS_CREATE = 'attendance_scans.create';
    case ATTENDANCE_SCANS_UPDATE = 'attendance_scans.update';
    case ATTENDANCE_SCANS_DELETE = 'attendance_scans.delete';
    case ATTENDANCE_SCANS_RESTORE = 'attendance_scans.restore';
    case ATTENDANCE_SCANS_FORCE_DELETE = 'attendance_scans.force_delete';
    case ATTENDANCE_SCANS_EXPORT = 'attendance_scans.export';
    case ATTENDANCE_SCANS_VIEW_DETAILS = 'attendance_scans.view_details';
    case ATTENDANCE_SCANS_VERIFY = 'attendance_scans.verify';
    case ATTENDANCE_SCANS_VIEW_MAP = 'attendance_scans.view_map';

    // ----- employee_experiences -----
    case EMPLOYEE_EXPERIENCES_VIEW = 'employee_experiences.view';
    case EMPLOYEE_EXPERIENCES_VIEW_ANY = 'employee_experiences.view_any';
    case EMPLOYEE_EXPERIENCES_CREATE = 'employee_experiences.create';
    case EMPLOYEE_EXPERIENCES_UPDATE = 'employee_experiences.update';
    case EMPLOYEE_EXPERIENCES_DELETE = 'employee_experiences.delete';
    case EMPLOYEE_EXPERIENCES_RESTORE = 'employee_experiences.restore';
    case EMPLOYEE_EXPERIENCES_FORCE_DELETE = 'employee_experiences.force_delete';

    // ----- permission_requests -----
    case PERMISSION_REQUESTS_VIEW = 'permission_requests.view';
    case PERMISSION_REQUESTS_VIEW_ANY = 'permission_requests.view_any';
    case PERMISSION_REQUESTS_CREATE = 'permission_requests.create';
    case PERMISSION_REQUESTS_UPDATE = 'permission_requests.update';
    case PERMISSION_REQUESTS_DELETE = 'permission_requests.delete';
    case PERMISSION_REQUESTS_RESTORE = 'permission_requests.restore';
    case PERMISSION_REQUESTS_FORCE_DELETE = 'permission_requests.force_delete';
    case PERMISSION_REQUESTS_APPROVE = 'permission_requests.approve';
    case PERMISSION_REQUESTS_REJECT = 'permission_requests.reject';
    case PERMISSION_REQUESTS_REVIEW = 'permission_requests.review';
    case PERMISSION_REQUESTS_EXPORT = 'permission_requests.export';
    case PERMISSION_REQUESTS_CREATE_OWN = 'permission_requests.create_own';
    case PERMISSION_REQUESTS_VIEW_OWN = 'permission_requests.view_own';

    // ----- employee_family_members -----
    case EMPLOYEE_FAMILY_MEMBERS_VIEW = 'employee_family_members.view';
    case EMPLOYEE_FAMILY_MEMBERS_VIEW_ANY = 'employee_family_members.view_any';
    case EMPLOYEE_FAMILY_MEMBERS_CREATE = 'employee_family_members.create';
    case EMPLOYEE_FAMILY_MEMBERS_UPDATE = 'employee_family_members.update';
    case EMPLOYEE_FAMILY_MEMBERS_DELETE = 'employee_family_members.delete';
    case EMPLOYEE_FAMILY_MEMBERS_RESTORE = 'employee_family_members.restore';
    case EMPLOYEE_FAMILY_MEMBERS_FORCE_DELETE = 'employee_family_members.force_delete';

    // ----- employee_academic_levels -----
    case EMPLOYEE_ACADEMIC_LEVELS_VIEW = 'employee_academic_levels.view';
    case EMPLOYEE_ACADEMIC_LEVELS_VIEW_ANY = 'employee_academic_levels.view_any';
    case EMPLOYEE_ACADEMIC_LEVELS_CREATE = 'employee_academic_levels.create';
    case EMPLOYEE_ACADEMIC_LEVELS_UPDATE = 'employee_academic_levels.update';
    case EMPLOYEE_ACADEMIC_LEVELS_DELETE = 'employee_academic_levels.delete';
    case EMPLOYEE_ACADEMIC_LEVELS_RESTORE = 'employee_academic_levels.restore';
    case EMPLOYEE_ACADEMIC_LEVELS_FORCE_DELETE = 'employee_academic_levels.force_delete';

    // ----- employee_foreign_languages -----
    case EMPLOYEE_FOREIGN_LANGUAGES_VIEW = 'employee_foreign_languages.view';
    case EMPLOYEE_FOREIGN_LANGUAGES_VIEW_ANY = 'employee_foreign_languages.view_any';
    case EMPLOYEE_FOREIGN_LANGUAGES_CREATE = 'employee_foreign_languages.create';
    case EMPLOYEE_FOREIGN_LANGUAGES_UPDATE = 'employee_foreign_languages.update';
    case EMPLOYEE_FOREIGN_LANGUAGES_DELETE = 'employee_foreign_languages.delete';
    case EMPLOYEE_FOREIGN_LANGUAGES_RESTORE = 'employee_foreign_languages.restore';
    case EMPLOYEE_FOREIGN_LANGUAGES_FORCE_DELETE = 'employee_foreign_languages.force_delete';

    // ----- employee_job_experiences -----
    case EMPLOYEE_JOB_EXPERIENCES_VIEW = 'employee_job_experiences.view';
    case EMPLOYEE_JOB_EXPERIENCES_VIEW_ANY = 'employee_job_experiences.view_any';
    case EMPLOYEE_JOB_EXPERIENCES_CREATE = 'employee_job_experiences.create';
    case EMPLOYEE_JOB_EXPERIENCES_UPDATE = 'employee_job_experiences.update';
    case EMPLOYEE_JOB_EXPERIENCES_DELETE = 'employee_job_experiences.delete';
    case EMPLOYEE_JOB_EXPERIENCES_RESTORE = 'employee_job_experiences.restore';
    case EMPLOYEE_JOB_EXPERIENCES_FORCE_DELETE = 'employee_job_experiences.force_delete';

    // ----- employee_plans -----
    case EMPLOYEE_PLANS_VIEW = 'employee_plans.view';
    case EMPLOYEE_PLANS_VIEW_ANY = 'employee_plans.view_any';
    case EMPLOYEE_PLANS_CREATE = 'employee_plans.create';
    case EMPLOYEE_PLANS_UPDATE = 'employee_plans.update';
    case EMPLOYEE_PLANS_DELETE = 'employee_plans.delete';
    case EMPLOYEE_PLANS_RESTORE = 'employee_plans.restore';
    case EMPLOYEE_PLANS_FORCE_DELETE = 'employee_plans.force_delete';

    // ----- employee_availabilities -----
    case EMPLOYEE_AVAILABILITIES_VIEW = 'employee_availabilities.view';
    case EMPLOYEE_AVAILABILITIES_VIEW_ANY = 'employee_availabilities.view_any';
    case EMPLOYEE_AVAILABILITIES_CREATE = 'employee_availabilities.create';
    case EMPLOYEE_AVAILABILITIES_UPDATE = 'employee_availabilities.update';
    case EMPLOYEE_AVAILABILITIES_DELETE = 'employee_availabilities.delete';
    case EMPLOYEE_AVAILABILITIES_RESTORE = 'employee_availabilities.restore';
    case EMPLOYEE_AVAILABILITIES_FORCE_DELETE = 'employee_availabilities.force_delete';

    // ----- employee_plan_assignments -----
    case EMPLOYEE_PLAN_ASSIGNMENTS_VIEW = 'employee_plan_assignments.view';
    case EMPLOYEE_PLAN_ASSIGNMENTS_VIEW_ANY = 'employee_plan_assignments.view_any';
    case EMPLOYEE_PLAN_ASSIGNMENTS_CREATE = 'employee_plan_assignments.create';
    case EMPLOYEE_PLAN_ASSIGNMENTS_UPDATE = 'employee_plan_assignments.update';
    case EMPLOYEE_PLAN_ASSIGNMENTS_DELETE = 'employee_plan_assignments.delete';
    case EMPLOYEE_PLAN_ASSIGNMENTS_BULK_ASSIGN = 'employee_plan_assignments.bulk_assign';

    /**
     * Plain string values for every case. Used by the seeder.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
