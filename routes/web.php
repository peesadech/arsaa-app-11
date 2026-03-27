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

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// SuperAdmin only
Route::middleware(['auth', 'role:SuperAdmin'])->group(function () {
    // Settings
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

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

    // User Management
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/data', [UserController::class, 'data'])->name('admin.users.data_crud');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

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
