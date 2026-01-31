<?php
namespace App\Http\Controllers;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use Maatwebsite\Excel\Facades\Excel; // Quan trọng nhất: Thêm dòng này
use App\Imports\BooksImport;
class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
        $this->middleware('role:admin')->except(['index', 'show']);
    }
    public function index(Request $request)
    {
       
        $query = \App\Models\Book::latest();  
        $search = trim($request->input('search'));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
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
        'category_id' => 'nullable|exists:categories,id',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
        'description' => 'nullable|string',
        'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);
    
    if ($request->hasFile('cover_image')) {
        $path = $request->file('cover_image')->store('books', 'public');
        $validated['cover_image'] = $path;
    }
    
    \App\Models\Book::create($validated);
    
    return redirect()->route('books.index')->with('success', 'Thêm sách thành công!');
    }
    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories')); // ⬅️ TRUYỀN DỮ LIỆU
    }
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $data = $request->all();
        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }
        $book->update($data);
        return redirect()->route('books.index')->with('success', 'Cập nhật sách thành công!');
    }
    public function destroy(Book $book)
    {
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Xóa sách thành công!');
    }

public function import(Request $request) 
{
    $request->validate([
        'file_excel' => 'required|mimes:xlsx,xls',
    ]);

    Excel::import(new BooksImport, $request->file('file_excel'));

    return redirect()->back()->with('success', 'Đã thêm sách từ Excel thành công!');
}
}