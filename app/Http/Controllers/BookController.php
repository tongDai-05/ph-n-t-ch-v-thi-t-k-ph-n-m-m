<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // Thêm DB để dùng Transaction

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        // Thêm eager loading 'category' để tối ưu truy vấn khi hiển thị phân loại
        $query = Book::with('category')->latest();  
        $search = trim($request->input('search'));

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%"); // Tìm theo cả nhà xuất bản
            });
        }
        $books = $query->paginate(10)->withQueryString();
        
        return view('books.index', compact('books'));
    }

    public function create()
    {
        $categories = Category::all(); 
        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id', // Phân loại
            'publisher' => 'nullable|string|max:255',        // Nhà xuất bản
            'price' => 'required|numeric|min:0',             // Giá bán
            'import_price' => 'required|numeric|min:0',      // Giá nhập (Mới)
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('books', 'public');
            $validated['cover_image'] = $path;
        }
        
        Book::create($validated);
        
        return redirect()->route('books.index')->with('success', 'Thêm sách thành common!');
    }

    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'publisher' => 'nullable|string|max:255',        // Cập nhật nhà xuất bản
            'price' => 'required|numeric|min:0',
            'import_price' => 'required|numeric|min:0',      // Cập nhật giá nhập
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $book->update($validated);
        return redirect()->route('books.index')->with('success', 'Cập nhật sách thành công!');
    }

    /**
     * Chức năng nhập hàng bổ sung (Mới)
     */
    public function import(Request $request, Book $book)
    {
        $request->validate([
            'added_quantity' => 'required|integer|min:1',
            'import_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $book) {
            // 1. Lưu vào nhật ký nhập hàng
            DB::table('import_logs')->insert([
                'book_id' => $book->id,
                'quantity' => $request->added_quantity,
                'import_price' => $request->import_price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Cập nhật tồn kho và giá nhập mới nhất cho sách
            $book->increment('quantity', $request->added_quantity);
            $book->update(['import_price' => $request->import_price]);
        });

        return redirect()->back()->with('success', 'Nhập thêm hàng thành công!');
    }

    public function destroy(Book $book)
    {
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Xóa sách thành công!');
    }
}