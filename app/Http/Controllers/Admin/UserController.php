<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    // Hiển thị danh sách user
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    // Form thêm user mới
    public function create()
    {
        return view('admin.users.create');
    }

    // Lưu user mới vào hệ thống
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Thêm người dùng thành công!');
    }

    // Xóa người dùng
    public function destroy(User $user)
    {
        // Tránh admin tự xóa chính mình
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không thể tự xóa tài khoản của chính mình!');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Đã xóa người dùng khỏi hệ thống!');
    }

    // Tìm kiếm người dùng
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $users = User::where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%")
                    ->paginate(10);

        return view('admin.users.index', compact('users', 'keyword'));
    }
}