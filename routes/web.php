<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    $setting = \App\Models\Setting::first() ?? new \App\Models\Setting(['app_name' => 'Laravel']);
    return view('welcome', compact('setting'));
});

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

Route::middleware(['auth', 'role:SuperAdmin'])->group(function () {
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');
});

Route::middleware(['auth', 'role:admin|SuperAdmin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/grades', [GradeController::class, 'index'])->name('admin.grades.index');
    Route::get('/admin/grades/data', [GradeController::class, 'data'])->name('admin.grades.data');
    Route::get('/admin/grades/create', [GradeController::class, 'create'])->name('admin.grades.create');
    Route::post('/admin/grades', [GradeController::class, 'store'])->name('admin.grades.store');
    Route::get('/admin/grades/{id}/edit', [GradeController::class, 'edit'])->name('admin.grades.edit');
    Route::put('/admin/grades/{id}', [GradeController::class, 'update'])->name('admin.grades.update');
    Route::delete('/admin/grades/{id}', [GradeController::class, 'destroy'])->name('admin.grades.destroy');

    Route::post('/admin/academic-years/set-current', [AcademicYearController::class, 'setCurrent'])->name('admin.academic-years.set-current');
    Route::get('/admin/academic-years', [AcademicYearController::class, 'index'])->name('admin.academic-years.index');
    Route::get('/admin/academic-years/data', [AcademicYearController::class, 'data'])->name('admin.academic-years.data');
    Route::get('/admin/academic-years/create', [AcademicYearController::class, 'create'])->name('admin.academic-years.create');
    Route::post('/admin/academic-years', [AcademicYearController::class, 'store'])->name('admin.academic-years.store');
    Route::get('/admin/academic-years/{id}/edit', [AcademicYearController::class, 'edit'])->name('admin.academic-years.edit');
    Route::put('/admin/academic-years/{id}', [AcademicYearController::class, 'update'])->name('admin.academic-years.update');
    Route::delete('/admin/academic-years/{id}', [AcademicYearController::class, 'destroy'])->name('admin.academic-years.destroy');

    Route::get('/admin/semesters', [SemesterController::class, 'index'])->name('admin.semesters.index');
    Route::get('/admin/semesters/data', [SemesterController::class, 'data'])->name('admin.semesters.data');
    Route::get('/admin/semesters/create', [SemesterController::class, 'create'])->name('admin.semesters.create');
    Route::post('/admin/semesters', [SemesterController::class, 'store'])->name('admin.semesters.store');
    Route::get('/admin/semesters/{id}/edit', [SemesterController::class, 'edit'])->name('admin.semesters.edit');
    Route::put('/admin/semesters/{id}', [SemesterController::class, 'update'])->name('admin.semesters.update');
    Route::delete('/admin/semesters/{id}', [SemesterController::class, 'destroy'])->name('admin.semesters.destroy');

    Route::get('/admin/classrooms', [ClassroomController::class, 'index'])->name('admin.classrooms.index');
    Route::get('/admin/classrooms/data', [ClassroomController::class, 'data'])->name('admin.classrooms.data');
    Route::get('/admin/classrooms/create', [ClassroomController::class, 'create'])->name('admin.classrooms.create');
    Route::post('/admin/classrooms', [ClassroomController::class, 'store'])->name('admin.classrooms.store');
    Route::get('/admin/classrooms/{id}/edit', [ClassroomController::class, 'edit'])->name('admin.classrooms.edit');
    Route::put('/admin/classrooms/{id}', [ClassroomController::class, 'update'])->name('admin.classrooms.update');
    Route::delete('/admin/classrooms/{id}', [ClassroomController::class, 'destroy'])->name('admin.classrooms.destroy');

    Route::get('/admin/courses', [CourseController::class, 'index'])->name('admin.courses.index');
    Route::get('/admin/courses/data', [CourseController::class, 'data'])->name('admin.courses.data');
    Route::get('/admin/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
    Route::post('/admin/courses', [CourseController::class, 'store'])->name('admin.courses.store');
    Route::get('/admin/courses/{id}/edit', [CourseController::class, 'edit'])->name('admin.courses.edit');
    Route::put('/admin/courses/{id}', [CourseController::class, 'update'])->name('admin.courses.update');
    Route::delete('/admin/courses/{id}', [CourseController::class, 'destroy'])->name('admin.courses.destroy');

    Route::get('/admin/roles-permissions', function () {
        return view('admin.roles-permissions');
    })->name('admin.roles-permissions');

    Route::get('/admin/permissions', function () {
        return view('admin.permissions.index');
    })->name('admin.permissions');

    Route::get('/admin/permissions/data', function () {
        try {
            $query = \Spatie\Permission\Models\Permission::query();
            return datatables()->of($query)
                ->addColumn('action', function($row){
                    $editUrl = route('admin.permissions.edit', $row->id);
                    $btn = '<div class="space-x-2">';
                    $btn .= '<a href="'.$editUrl.'" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-amber-500 hover:border-amber-400 hover:bg-amber-50 transition-all duration-200 shadow-sm hover:shadow-md" title="Edit Permission"><i class="fas fa-pen-nib text-sm"></i></a>';
                    $btn .= '<button type="button" onclick="confirmDelete('.$row->id.', \''.addslashes($row->name).'\')" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-rose-500 hover:border-rose-400 hover:bg-rose-50 transition-all duration-200 shadow-sm hover:shadow-md" title="Delete Permission"><i class="fas fa-trash-alt text-sm"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('admin.permissions.data');

    Route::get('/admin/permissions/create', function () {
        return view('admin.permissions.save');
    })->name('admin.permissions.create');

    Route::post('/admin/permissions', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'required|string',
        ]);

        \Spatie\Permission\Models\Permission::create($data);

        return redirect()->route('admin.permissions')->with('status', 'Permission created successfully!');
    })->name('admin.permissions.store');

    Route::get('/admin/permissions/{id}/edit', function ($id) {
        $permission = \Spatie\Permission\Models\Permission::findOrFail($id);
        return view('admin.permissions.save', compact('permission'));
    })->name('admin.permissions.edit');

    Route::put('/admin/permissions/{id}', function (\Illuminate\Http\Request $request, $id) {
        $permission = \Spatie\Permission\Models\Permission::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $id,
            'guard_name' => 'required|string',
        ]);

        $permission->update($data);

        return redirect()->route('admin.permissions')->with('status', 'Permission updated successfully!');
    })->name('admin.permissions.update');

    Route::delete('/admin/permissions/{id}', function ($id) {
        $permission = \Spatie\Permission\Models\Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('admin.permissions')->with('status', 'Permission removed successfully!');
    })->name('admin.permissions.destroy');

    Route::get('/admin/permission-types', function () {
        return view('admin.permission-types.index');
    })->name('admin.permission-types');

    Route::get('/admin/permission-types/data', function () {
        try {
            $query = \App\Models\PermissionType::query();
            return datatables()->of($query)
                ->addColumn('action', function($row){
                    $editUrl = route('admin.permission-types.edit', $row->permissionType_id);
                    $btn = '<div class="space-x-2">';
                    $btn .= '<a href="'.$editUrl.'" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-amber-500 hover:border-amber-400 hover:bg-amber-50 transition-all duration-200 shadow-sm hover:shadow-md" title="Modify Category"><i class="fas fa-pen-nib text-sm"></i></a>';
                    $btn .= '<button type="button" onclick="confirmDelete('.$row->permissionType_id.', \''.addslashes($row->permissionType_name).'\')" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-rose-500 hover:border-rose-400 hover:bg-rose-50 transition-all duration-200 shadow-sm hover:shadow-md" title="Remove Category"><i class="fas fa-trash-alt text-sm"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('admin.permission-types.data');

    Route::get('/admin/permission-types/create', function () {
        return view('admin.permission-types.save');
    })->name('admin.permission-types.create');

    Route::post('/admin/permission-types', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'permissionType_name' => 'required|string|unique:permission_types,permissionType_name',
            'image_base64' => 'nullable|string',
        ]);

        if ($request->filled('image_base64')) {
            $base64Image = $request->input('image_base64');
            $image_parts = explode(";base64,", $base64Image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);

            $fileName = time() . '.jpg';
            $directory = public_path('/image/permissionTypes');

            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            file_put_contents($directory . '/' . $fileName, $image_base64);
            $data['permissionType_image_path'] = '/image/permissionTypes/' . $fileName;
        }

        unset($data['image_base64']);
        \App\Models\PermissionType::create($data);

        return redirect()->route('admin.permission-types')->with('status', 'Category created successfully!');
    })->name('admin.permission-types.store');

    Route::get('/admin/permission-types/{id}/edit', function ($id) {
        $permissionType = \App\Models\PermissionType::findOrFail($id);
        return view('admin.permission-types.save', compact('permissionType'));
    })->name('admin.permission-types.edit');

    Route::put('/admin/permission-types/{id}', function (\Illuminate\Http\Request $request, $id) {
        $permissionType = \App\Models\PermissionType::findOrFail($id);
        $data = $request->validate([
            'permissionType_name' => 'required|string|unique:permission_types,permissionType_name,' . $id . ',permissionType_id',
            'image_base64' => 'nullable|string',
        ]);

        if ($request->filled('image_base64')) {
            $base64Image = $request->input('image_base64');
            $image_parts = explode(";base64,", $base64Image);
            $image_base64 = base64_decode($image_parts[1]);

            $fileName = time() . '.jpg';
            $directory = public_path('/image/permissionTypes');

            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Delete old image if exists
            if ($permissionType->permissionType_image_path) {
                $oldPath = public_path($permissionType->permissionType_image_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            file_put_contents($directory . '/' . $fileName, $image_base64);
            $data['permissionType_image_path'] = '/image/permissionTypes/' . $fileName;
        }

        unset($data['image_base64']);
        $permissionType->update($data);

        return redirect()->route('admin.permission-types')->with('status', 'Category updated successfully!');
    })->name('admin.permission-types.update');

    Route::delete('/admin/permission-types/{id}', function ($id) {
        $permissionType = \App\Models\PermissionType::findOrFail($id);
        $permissionType->delete();

        return redirect()->route('admin.permission-types')->with('status', 'Category removed successfully!');
    })->name('admin.permission-types.destroy');

    // User Management CRUD
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/data', [UserController::class, 'data'])->name('admin.users.data_crud');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Data routes for Admin DataTables (Session-based)
    Route::prefix('admin/data')->group(function () {
        Route::get('/roles', function () {
            $roles = \Spatie\Permission\Models\Role::with('permissions')->get();
            return datatables()->of($roles)
                ->addColumn('permissions_list', function($row){
                    $badges = '';
                    foreach($row->permissions as $p) {
                        $badges .= '<span class="px-2 py-0.5 mr-1 mb-1 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold">'.$p->name.'</span>';
                    }
                    return $badges ?: '<span class="text-gray-400 text-[10px] italic">None</span>';
                })
                ->addColumn('action', function($row){
                    return '<button type="button" onclick="confirmDeleteRole('.$row->id.', \''.addslashes($row->name).'\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm"><i class="fas fa-trash-alt text-xs"></i></button>';
                })
                ->rawColumns(['permissions_list', 'action'])
                ->make(true);
        })->name('admin.roles.data');

        Route::get('/users', function () {
            $users = \App\Models\User::with('roles')->get();
            return datatables()->of($users)
                ->addColumn('roles_assignment', function($row){
                    $roles = \Spatie\Permission\Models\Role::all();
                    $select = '<select multiple class="user-role-select w-full px-3 py-2 rounded-xl bg-gray-50 border-0 text-xs focus:ring-2 focus:ring-indigo-500 transition-all font-medium" data-user-id="'.$row->id.'" style="height: 80px;">';
                    foreach($roles as $role) {
                        $selected = $row->hasRole($role->name) ? 'selected' : '';
                        $select .= '<option value="'.$role->name.'" '.$selected.'>'.$role->name.'</option>';
                    }
                    $select .= '</select>';
                    return $select;
                })
                ->rawColumns(['roles_assignment'])
                ->make(true);
        })->name('admin.users.data');
    });

});
