<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('admin.users.index', compact('roles'));
    }

    public function data(Request $request)
    {
        $users = User::with('roles')->select('users.*');

        // Apply custom filters
        if ($request->filled('role')) {
            $users->role($request->role);
        }

        if ($request->filled('status')) {
            $users->where('status', $request->status);
        }

        if ($request->filled('verify_status')) {
            if ($request->verify_status === 'verified') {
                $users->whereNotNull('email_verified_at');
            } elseif ($request->verify_status === 'unverified') {
                $users->whereNull('email_verified_at');
            }
        }

        return DataTables::of($users)
            ->addColumn('avatar', function ($user) {
                $path = $user->image_path ? asset($user->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF';
                return '<img src="' . $path . '" class="w-10 h-10 rounded-xl object-cover shadow-sm border border-gray-100 dark:border-zinc-700" alt="Avatar">';
            })
            ->addColumn('roles_list', function ($user) {
                $badges = '';
                foreach ($user->roles as $role) {
                    $colorClass = $role->name === 'SuperAdmin' ? 'bg-rose-50 text-rose-600' : 'bg-indigo-50 text-indigo-600';
                    $badges .= '<span class="px-2 py-0.5 mr-1 mb-1 rounded-full ' . $colorClass . ' text-[10px] font-bold">' . $role->name . '</span>';
                }
                return $badges ?: '<span class="text-gray-400 text-[10px] italic">None</span>';
            })
            ->addColumn('status', function ($user) {
                $statusText = $user->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $user->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('email_verified', function ($user) {
                if ($user->email_verified_at) {
                    return '<span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider"><i class="fas fa-check mr-1"></i> Verified</span>';
                }
                return '<span class="px-2 py-1 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-bold uppercase tracking-wider"><i class="fas fa-times mr-1"></i> Unverified</span>';
            })
            ->filterColumn('email_verified', function($query, $keyword) {
                if (strtolower($keyword) === 'verified') {
                    $query->whereNotNull('email_verified_at');
                } elseif (strtolower($keyword) === 'unverified') {
                    $query->whereNull('email_verified_at');
                }
            })
            ->addColumn('action', function ($user) {
                $editUrl = route('admin.users.edit', $user->id);
                $btn = '<div class="flex space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit User"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $user->id . ', \'' . addslashes($user->name) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete User"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['avatar', 'roles_list', 'status', 'email_verified', 'action'])
            ->make(true);
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.save', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'image_base64' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        $data['password'] = Hash::make($data['password']);

        if ($request->filled('image_base64')) {
            $data['image_path'] = $this->handleImageUpload($request->input('image_base64'));
        }

        $user = User::create($data);

        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        return redirect()->route('admin.users.index')->with('status', 'User created successfully!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('admin.users.save', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'image_base64' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->filled('image_base64')) {
            // Delete old image
            if ($user->image_path) {
                $oldPath = public_path($user->image_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $data['image_path'] = $this->handleImageUpload($request->input('image_base64'));
        }

        $user->update($data);

        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Don't delete self
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself!');
        }

        if ($user->image_path) {
            $path = public_path($user->image_path);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('status', 'User deleted successfully!');
    }

    private function handleImageUpload($base64Data)
    {
        $image_parts = explode(";base64,", $base64Data);
        $image_base64 = base64_decode($image_parts[1]);
        
        $fileName = time() . '_' . uniqid() . '.jpg';
        $directory = public_path('/image/users');
        
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        
        file_put_contents($directory . '/' . $fileName, $image_base64);
        return '/image/users/' . $fileName;
    }
}
