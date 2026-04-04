<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PermissionTypeWebController;
use App\Http\Controllers\Admin\AdminDataController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\UserAssignmentController;
use App\Http\Controllers\Admin\OpenedCourseController;
use App\Http\Controllers\Admin\GlobalScheduleController;
use App\Http\Controllers\Admin\YearlyScheduleController;
use App\Http\Controllers\Admin\EducationLevelController;
use App\Http\Controllers\Admin\SubjectGroupController;
use App\Http\Controllers\Admin\BuildingController;
use App\Http\Controllers\Admin\CourseTypeController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Admin\TimetableExportController;
use App\Http\Controllers\Api\RoleController as ApiRoleController;
use App\Http\Controllers\Api\PermissionController as ApiPermissionController;
use App\Http\Controllers\Api\UserRoleController as ApiUserRoleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [WelcomeController::class, 'index']);

Auth::routes();

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('auth/facebook', [FacebookController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('auth/facebook/callback', [FacebookController::class, 'handleFacebookCallback']);

// Role-based redirect (replaces old /home route)
Route::get('/home', function () {
    $user = auth()->user();
    if ($user) {
        $roles = $user->getRoleNames()->map(fn($r) => strtoupper($r));
        if ($roles->intersect(['SUPERADMIN', 'ADMIN'])->isNotEmpty()) {
            return redirect()->route('admin.dashboard');
        }
    }
    return redirect()->route('profile.index');
})->middleware('auth')->name('home');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Language Switcher (any user)
Route::get('/locale/{code}', [LanguageController::class, 'switchLocale'])->name('locale.switch');

// SuperAdmin only
Route::middleware(['auth', 'role:SuperAdmin'])->group(function () {
    // Settings
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

    // Global Schedule
    Route::get('/admin/global-schedule', [GlobalScheduleController::class, 'index'])->name('admin.global-schedule.index');
    Route::get('/admin/global-schedule/{educationLevelId}/edit', [GlobalScheduleController::class, 'edit'])->name('admin.global-schedule.edit');
    Route::put('/admin/global-schedule/{educationLevelId}', [GlobalScheduleController::class, 'update'])->name('admin.global-schedule.update');

    // Role Management
    Route::get('/admin/roles-permissions', [RoleManagementController::class, 'index'])->name('admin.roles-permissions');
    Route::get('/admin/roles/data', [RoleManagementController::class, 'data'])->name('admin.roles.data');
    Route::get('/admin/roles/create', [RoleManagementController::class, 'create'])->name('admin.roles.create');
    Route::post('/admin/roles', [RoleManagementController::class, 'store'])->name('admin.roles.store');
    Route::get('/admin/roles/{id}/edit', [RoleManagementController::class, 'edit'])->name('admin.roles.edit');
    Route::put('/admin/roles/{id}', [RoleManagementController::class, 'update'])->name('admin.roles.update');
    Route::delete('/admin/roles/{id}', [RoleManagementController::class, 'destroy'])->name('admin.roles.destroy');

    // Permissions
    Route::get('/admin/permissions', [PermissionController::class, 'index'])->name('admin.permissions');
    Route::get('/admin/permissions/data', [PermissionController::class, 'data'])->name('admin.permissions.data');
    Route::get('/admin/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
    Route::post('/admin/permissions', [PermissionController::class, 'store'])->name('admin.permissions.store');
    Route::get('/admin/permissions/{id}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('/admin/permissions/{id}', [PermissionController::class, 'update'])->name('admin.permissions.update');
    Route::delete('/admin/permissions/{id}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy');

    // Permission Types
    Route::get('/admin/permission-types', [PermissionTypeWebController::class, 'index'])->name('admin.permission-types');
    Route::get('/admin/permission-types/data', [PermissionTypeWebController::class, 'data'])->name('admin.permission-types.data');
    Route::get('/admin/permission-types/create', [PermissionTypeWebController::class, 'create'])->name('admin.permission-types.create');
    Route::post('/admin/permission-types', [PermissionTypeWebController::class, 'store'])->name('admin.permission-types.store');
    Route::get('/admin/permission-types/{id}/edit', [PermissionTypeWebController::class, 'edit'])->name('admin.permission-types.edit');
    Route::put('/admin/permission-types/{id}', [PermissionTypeWebController::class, 'update'])->name('admin.permission-types.update');
    Route::delete('/admin/permission-types/{id}', [PermissionTypeWebController::class, 'destroy'])->name('admin.permission-types.destroy');

    // Education Levels
    Route::get('/admin/education-levels', [EducationLevelController::class, 'index'])->name('admin.education-levels.index');
    Route::get('/admin/education-levels/data', [EducationLevelController::class, 'data'])->name('admin.education-levels.data');
    Route::get('/admin/education-levels/create', [EducationLevelController::class, 'create'])->name('admin.education-levels.create');
    Route::post('/admin/education-levels', [EducationLevelController::class, 'store'])->name('admin.education-levels.store');
    Route::get('/admin/education-levels/{id}/edit', [EducationLevelController::class, 'edit'])->name('admin.education-levels.edit');
    Route::put('/admin/education-levels/{id}', [EducationLevelController::class, 'update'])->name('admin.education-levels.update');
    Route::delete('/admin/education-levels/{id}', [EducationLevelController::class, 'destroy'])->name('admin.education-levels.destroy');

    // Subject Groups
    Route::get('/admin/subject-groups', [SubjectGroupController::class, 'index'])->name('admin.subject-groups.index');
    Route::get('/admin/subject-groups/data', [SubjectGroupController::class, 'data'])->name('admin.subject-groups.data');
    Route::get('/admin/subject-groups/create', [SubjectGroupController::class, 'create'])->name('admin.subject-groups.create');
    Route::post('/admin/subject-groups', [SubjectGroupController::class, 'store'])->name('admin.subject-groups.store');
    Route::get('/admin/subject-groups/{id}/edit', [SubjectGroupController::class, 'edit'])->name('admin.subject-groups.edit');
    Route::put('/admin/subject-groups/{id}', [SubjectGroupController::class, 'update'])->name('admin.subject-groups.update');
    Route::delete('/admin/subject-groups/{id}', [SubjectGroupController::class, 'destroy'])->name('admin.subject-groups.destroy');

    // Buildings
    Route::get('/admin/buildings', [BuildingController::class, 'index'])->name('admin.buildings.index');
    Route::get('/admin/buildings/data', [BuildingController::class, 'data'])->name('admin.buildings.data');
    Route::get('/admin/buildings/create', [BuildingController::class, 'create'])->name('admin.buildings.create');
    Route::post('/admin/buildings', [BuildingController::class, 'store'])->name('admin.buildings.store');
    Route::get('/admin/buildings/{id}/edit', [BuildingController::class, 'edit'])->name('admin.buildings.edit');
    Route::put('/admin/buildings/{id}', [BuildingController::class, 'update'])->name('admin.buildings.update');
    Route::delete('/admin/buildings/{id}', [BuildingController::class, 'destroy'])->name('admin.buildings.destroy');

    // Course Types
    Route::get('/admin/course-types', [CourseTypeController::class, 'index'])->name('admin.course-types.index');
    Route::get('/admin/course-types/data', [CourseTypeController::class, 'data'])->name('admin.course-types.data');
    Route::get('/admin/course-types/create', [CourseTypeController::class, 'create'])->name('admin.course-types.create');
    Route::post('/admin/course-types', [CourseTypeController::class, 'store'])->name('admin.course-types.store');
    Route::get('/admin/course-types/{id}/edit', [CourseTypeController::class, 'edit'])->name('admin.course-types.edit');
    Route::put('/admin/course-types/{id}', [CourseTypeController::class, 'update'])->name('admin.course-types.update');
    Route::delete('/admin/course-types/{id}', [CourseTypeController::class, 'destroy'])->name('admin.course-types.destroy');

    // Rooms
    Route::get('/admin/rooms', [RoomController::class, 'index'])->name('admin.rooms.index');
    Route::get('/admin/rooms/data', [RoomController::class, 'data'])->name('admin.rooms.data');
    Route::get('/admin/rooms/create', [RoomController::class, 'create'])->name('admin.rooms.create');
    Route::post('/admin/rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
    Route::get('/admin/rooms/{id}/edit', [RoomController::class, 'edit'])->name('admin.rooms.edit');
    Route::put('/admin/rooms/{id}', [RoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('/admin/rooms/{id}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

    // Academic Years (CRUD — SuperAdmin only)
    Route::get('/admin/academic-years', [AcademicYearController::class, 'index'])->name('admin.academic-years.index');
    Route::get('/admin/academic-years/data', [AcademicYearController::class, 'data'])->name('admin.academic-years.data');
    Route::get('/admin/academic-years/create', [AcademicYearController::class, 'create'])->name('admin.academic-years.create');
    Route::post('/admin/academic-years', [AcademicYearController::class, 'store'])->name('admin.academic-years.store');
    Route::get('/admin/academic-years/{id}/edit', [AcademicYearController::class, 'edit'])->name('admin.academic-years.edit');
    Route::put('/admin/academic-years/{id}', [AcademicYearController::class, 'update'])->name('admin.academic-years.update');
    Route::delete('/admin/academic-years/{id}', [AcademicYearController::class, 'destroy'])->name('admin.academic-years.destroy');

    // Semesters
    Route::get('/admin/semesters', [SemesterController::class, 'index'])->name('admin.semesters.index');
    Route::get('/admin/semesters/data', [SemesterController::class, 'data'])->name('admin.semesters.data');
    Route::get('/admin/semesters/create', [SemesterController::class, 'create'])->name('admin.semesters.create');
    Route::post('/admin/semesters', [SemesterController::class, 'store'])->name('admin.semesters.store');
    Route::get('/admin/semesters/{id}/edit', [SemesterController::class, 'edit'])->name('admin.semesters.edit');
    Route::put('/admin/semesters/{id}', [SemesterController::class, 'update'])->name('admin.semesters.update');
    Route::delete('/admin/semesters/{id}', [SemesterController::class, 'destroy'])->name('admin.semesters.destroy');
});

// Admin & SuperAdmin
Route::middleware(['auth', 'role:admin|SuperAdmin'])->group(function () {

    // Academic Year / Semester session selectors (all admins can select for their session)
    Route::post('/admin/academic-years/select-current', [AcademicYearController::class, 'selectCurrent'])->name('admin.academic-years.select-current');
    Route::post('/admin/academic-years/select-current-global', [AcademicYearController::class, 'selectCurrentGlobal'])->name('admin.academic-years.select-current-global');

    // Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/user-assignments', [UserAssignmentController::class, 'index'])->name('admin.user-assignments');

    // Opened Grades
    Route::get('/admin/dashboard/available-grades', [DashboardController::class, 'availableGrades'])->name('admin.dashboard.available-grades');
    Route::get('/admin/dashboard/courses-by-grade', [DashboardController::class, 'coursesByGrade'])->name('admin.dashboard.courses-by-grade');
    Route::post('/admin/dashboard/open-grade', [DashboardController::class, 'openGrade'])->name('admin.dashboard.open-grade');
    Route::delete('/admin/dashboard/close-grade/{id}', [DashboardController::class, 'closeGrade'])->name('admin.dashboard.close-grade');
    Route::post('/admin/dashboard/sync-grade-classrooms', [DashboardController::class, 'syncGradeClassrooms'])->name('admin.dashboard.sync-grade-classrooms');
    Route::get('/admin/dashboard/stats', [DashboardController::class, 'stats'])->name('admin.dashboard.stats');

    // Yearly Schedule
    Route::get('/admin/yearly-schedule', [YearlyScheduleController::class, 'index'])->name('admin.yearly-schedule.index');
    Route::post('/admin/yearly-schedule/copy', [YearlyScheduleController::class, 'copyFromGlobal'])->name('admin.yearly-schedule.copy');
    Route::post('/admin/yearly-schedule/copy-all', [YearlyScheduleController::class, 'copyAllFromGlobal'])->name('admin.yearly-schedule.copy-all');
    Route::get('/admin/yearly-schedule/{academicYearId}/{semesterId}/{educationLevelId}/edit', [YearlyScheduleController::class, 'edit'])->name('admin.yearly-schedule.edit');
    Route::put('/admin/yearly-schedule/{academicYearId}/{semesterId}/{educationLevelId}', [YearlyScheduleController::class, 'update'])->name('admin.yearly-schedule.update');

    // Opened Courses
    Route::get('/admin/opened-courses', [OpenedCourseController::class, 'index'])->name('admin.opened-courses.index');
    Route::get('/admin/opened-courses/data', [OpenedCourseController::class, 'data'])->name('admin.opened-courses.data');
    Route::get('/admin/opened-courses/classrooms-by-grade', [OpenedCourseController::class, 'classroomsByGrade'])->name('admin.opened-courses.classrooms-by-grade');
    Route::get('/admin/opened-courses/courses-by-grade', [OpenedCourseController::class, 'coursesByGrade'])->name('admin.opened-courses.courses-by-grade');
    Route::get('/admin/opened-courses/create', [OpenedCourseController::class, 'create'])->name('admin.opened-courses.create');
    Route::post('/admin/opened-courses', [OpenedCourseController::class, 'store'])->name('admin.opened-courses.store');
    Route::get('/admin/opened-courses/{id}/edit', [OpenedCourseController::class, 'edit'])->name('admin.opened-courses.edit');
    Route::put('/admin/opened-courses/{id}', [OpenedCourseController::class, 'update'])->name('admin.opened-courses.update');
    Route::delete('/admin/opened-courses/{id}', [OpenedCourseController::class, 'destroy'])->name('admin.opened-courses.destroy');

    // Grades
    Route::get('/admin/grades', [GradeController::class, 'index'])->name('admin.grades.index');
    Route::get('/admin/grades/data', [GradeController::class, 'data'])->name('admin.grades.data');
    Route::get('/admin/grades/create', [GradeController::class, 'create'])->name('admin.grades.create');
    Route::post('/admin/grades', [GradeController::class, 'store'])->name('admin.grades.store');
    Route::get('/admin/grades/{id}/edit', [GradeController::class, 'edit'])->name('admin.grades.edit');
    Route::put('/admin/grades/{id}', [GradeController::class, 'update'])->name('admin.grades.update');
    Route::delete('/admin/grades/{id}', [GradeController::class, 'destroy'])->name('admin.grades.destroy');

    // Classrooms
    Route::get('/admin/classrooms', [ClassroomController::class, 'index'])->name('admin.classrooms.index');
    Route::get('/admin/classrooms/data', [ClassroomController::class, 'data'])->name('admin.classrooms.data');
    Route::get('/admin/classrooms/create', [ClassroomController::class, 'create'])->name('admin.classrooms.create');
    Route::post('/admin/classrooms', [ClassroomController::class, 'store'])->name('admin.classrooms.store');
    Route::get('/admin/classrooms/{id}/edit', [ClassroomController::class, 'edit'])->name('admin.classrooms.edit');
    Route::put('/admin/classrooms/{id}', [ClassroomController::class, 'update'])->name('admin.classrooms.update');
    Route::delete('/admin/classrooms/{id}', [ClassroomController::class, 'destroy'])->name('admin.classrooms.destroy');

    // Courses
    Route::get('/admin/courses', [CourseController::class, 'index'])->name('admin.courses.index');
    Route::get('/admin/courses/data', [CourseController::class, 'data'])->name('admin.courses.data');
    Route::get('/admin/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
    Route::post('/admin/courses', [CourseController::class, 'store'])->name('admin.courses.store');
    Route::get('/admin/courses/{id}/edit', [CourseController::class, 'edit'])->name('admin.courses.edit');
    Route::put('/admin/courses/{id}', [CourseController::class, 'update'])->name('admin.courses.update');
    Route::delete('/admin/courses/{id}', [CourseController::class, 'destroy'])->name('admin.courses.destroy');

    // Language Management
    Route::get('/admin/languages', [LanguageController::class, 'index'])->name('admin.languages.index');
    Route::get('/admin/languages/data', [LanguageController::class, 'data'])->name('admin.languages.data');
    Route::get('/admin/languages/create', [LanguageController::class, 'create'])->name('admin.languages.create');
    Route::post('/admin/languages', [LanguageController::class, 'store'])->name('admin.languages.store');
    Route::get('/admin/languages/{id}/edit', [LanguageController::class, 'edit'])->name('admin.languages.edit');
    Route::put('/admin/languages/{id}', [LanguageController::class, 'update'])->name('admin.languages.update');
    Route::delete('/admin/languages/{id}', [LanguageController::class, 'destroy'])->name('admin.languages.destroy');
    Route::get('/admin/languages/{code}/translations', [LanguageController::class, 'translations'])->name('admin.languages.translations');
    Route::put('/admin/languages/{code}/translations', [LanguageController::class, 'updateTranslations'])->name('admin.languages.translations.update');

    // Teacher Management
    Route::get('/admin/teachers', [TeacherController::class, 'index'])->name('admin.teachers.index');
    Route::get('/admin/teachers/data', [TeacherController::class, 'data'])->name('admin.teachers.data');
    Route::get('/admin/teachers/search-courses', [TeacherController::class, 'searchCourses'])->name('admin.teachers.search-courses');
    Route::get('/admin/teachers/schedule-data', [TeacherController::class, 'scheduleData'])->name('admin.teachers.schedule-data');
    Route::get('/admin/teachers/create', [TeacherController::class, 'create'])->name('admin.teachers.create');
    Route::post('/admin/teachers', [TeacherController::class, 'store'])->name('admin.teachers.store');
    Route::get('/admin/teachers/{id}/edit', [TeacherController::class, 'edit'])->name('admin.teachers.edit');
    Route::put('/admin/teachers/{id}', [TeacherController::class, 'update'])->name('admin.teachers.update');
    Route::delete('/admin/teachers/{id}', [TeacherController::class, 'destroy'])->name('admin.teachers.destroy');

    // User Management
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/data', [UserController::class, 'data'])->name('admin.users.data_crud');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Timetable Scheduling
    Route::get('/admin/timetable', [TimetableController::class, 'index'])->name('admin.timetable.index');
    Route::get('/admin/timetable/generate', [TimetableController::class, 'generateForm'])->name('admin.timetable.generate');
    Route::post('/admin/timetable/generate', [TimetableController::class, 'generateStore'])->name('admin.timetable.generate.store');
    Route::get('/admin/timetable/generations/{id}', [TimetableController::class, 'showGeneration'])->name('admin.timetable.generations.show');
    Route::get('/admin/timetable/solutions/{id}', [TimetableController::class, 'showSolution'])->name('admin.timetable.solutions.show');
    Route::get('/admin/timetable/view/classroom/{classroomId}', [TimetableController::class, 'viewByClassroom'])->name('admin.timetable.view.classroom');
    Route::get('/admin/timetable/view/teacher/{teacherId}', [TimetableController::class, 'viewByTeacher'])->name('admin.timetable.view.teacher');
    Route::get('/admin/timetable/view/room/{roomId}', [TimetableController::class, 'viewByRoom'])->name('admin.timetable.view.room');
    Route::get('/admin/timetable/conflicts/{solutionId}', [TimetableController::class, 'conflicts'])->name('admin.timetable.conflicts');
    Route::get('/admin/timetable/manual', [TimetableController::class, 'manualSelect'])->name('admin.timetable.manual.select');
    Route::get('/admin/timetable/manual/{gradeId}/{classroomId}', [TimetableController::class, 'manualEditor'])->name('admin.timetable.manual.editor');

    // Timetable API (JSON)
    Route::prefix('admin/timetable/api')->group(function () {
        Route::get('/generations/{id}/progress', [TimetableController::class, 'apiProgress'])->name('admin.timetable.api.progress');
        Route::post('/solutions/{id}/select', [TimetableController::class, 'apiSelect'])->name('admin.timetable.api.select');
        Route::get('/solutions/{id}/entries', [TimetableController::class, 'apiEntries'])->name('admin.timetable.api.entries');
        Route::post('/entries/{id}/move', [TimetableController::class, 'apiMove'])->name('admin.timetable.api.move');
        Route::post('/entries/{id}/lock', [TimetableController::class, 'apiLock'])->name('admin.timetable.api.lock');
        Route::delete('/entries/{id}', [TimetableController::class, 'apiDelete'])->name('admin.timetable.api.delete');
        Route::post('/entries', [TimetableController::class, 'apiCreate'])->name('admin.timetable.api.create');
        Route::get('/check-conflicts', [TimetableController::class, 'apiCheckConflicts'])->name('admin.timetable.api.check');
        Route::get('/explain-slot', [TimetableController::class, 'apiExplainSlot'])->name('admin.timetable.api.explain');
        Route::get('/solutions/{id}/fitness', [TimetableController::class, 'apiFitness'])->name('admin.timetable.api.fitness');
        Route::post('/export/classroom-pdf', [TimetableExportController::class, 'exportClassroomPdf'])->name('admin.timetable.api.export.classroom-pdf');
        Route::post('/export/teacher-pdf', [TimetableExportController::class, 'exportTeacherPdf'])->name('admin.timetable.api.export.teacher-pdf');
        Route::post('/export/room-pdf', [TimetableExportController::class, 'exportRoomPdf'])->name('admin.timetable.api.export.room-pdf');
        Route::post('/export/excel', [TimetableExportController::class, 'exportExcel'])->name('admin.timetable.api.export.excel');
        Route::post('/export/word', [TimetableExportController::class, 'exportWord'])->name('admin.timetable.api.export.word');
    });

    // Admin Data (DataTables)
    Route::prefix('admin/data')->group(function () {
        Route::get('/roles', [AdminDataController::class, 'roles'])->name('admin.data.roles');
        Route::get('/users', [AdminDataController::class, 'users'])->name('admin.users.data');
    });

    // Admin AJAX endpoints (JSON) — used by web admin panel
    Route::prefix('admin/ajax')->group(function () {
        Route::get('/permissions', [ApiPermissionController::class, 'index'])->name('admin.ajax.permissions');
        Route::get('/roles', [ApiRoleController::class, 'index'])->name('admin.ajax.roles.index');
        Route::post('/roles', [ApiRoleController::class, 'store'])->name('admin.ajax.roles.store');
        Route::delete('/roles/{role}', [ApiRoleController::class, 'destroy'])->name('admin.ajax.roles.destroy');
        Route::put('/users/{user}/roles', [ApiUserRoleController::class, 'update'])->name('admin.ajax.users.roles');
    });

});
