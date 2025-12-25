@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <img src="{{ str_starts_with($book->cover_image, 'imgs/') ? asset($book->cover_image) : asset('storage/' . $book->cover_image) }}" class="img-fluid rounded shadow-sm" alt="{{ $book->title }}">
        </div>
        <div class="col-md-8">
            <h2 class="mb-3">{{ $book->title }}</h2>
            <p class="fs-5"><strong>Tác giả:</strong> {{ $book->author }}</p>
            
          
            <h3 class="text-danger fw-bold my-4">
                Giá: {{ number_format($book->price, 0, ',', '.') }} VNĐ
            </h3>
            
            <p><strong>Kho:</strong> 
                @if($book->quantity > 0)
                    <span class="badge bg-success">Còn hàng ({{ $book->quantity }})</span>
                @else
                    <span class="badge bg-danger">Hết hàng</span>
                @endif
            </p>

            <hr>
          
            @if($book->quantity > 0)
                <form action="{{ route('cart.store') }}" method="POST" class="d-flex align-items-center mb-4">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                    
                    <label for="qty" class="me-3">Số lượng:</label>
                    <input 
                        type="number" 
                        id="qty" 
                        name="quantity" 
                        value="1" 
                        min="1" 
                        max="{{ $book->quantity }}" 
                        class="form-control me-3" 
                        style="width: 80px;" 
                        required
                    >
                    
                    <button type="submit" class="btn btn-primary">
                        🛒 Thêm vào giỏ hàng
                    </button>
              
                    @if(session('error'))
                        <div class="text-danger ms-3">{{ session('error') }}</div>
                    @endif
                </form>
            @endif
            
            <h4 class="mt-5 mb-3 text-info">Mô tả sách</h4>
            <p style="white-space: pre-wrap;">{{ $book->description }}</p>
       
            <div class="mt-4 pt-3 border-top">
                <a href="{{ route('books.index') }}" class="btn btn-secondary">Quay lại</a>
                
             
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <a href="{{ route('books.edit', $book->id) }}" class="btn btn-warning">Sửa</a>
                    <form action="{{ route('books.destroy', $book->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa sách này?')">Xóa</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection