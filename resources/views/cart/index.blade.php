@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">🛒 Giỏ hàng của bạn</h2>

    {{-- HIỂN THỊ THÔNG BÁO LỖI (ERROR) VÀ THÀNH CÔNG (SUCCESS) --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    {{-- END HIỂN THỊ THÔNG BÁO --}}

    @if($cartItems->isEmpty())
        <div class="alert alert-info">Giỏ hàng của bạn đang trống! <a href="{{ route('books.index') }}">Tiếp tục mua sắm.</a></div>
    @else
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Sách</th>
                    <th>Giá</th>
                    <th style="width: 15%;">Số lượng</th>
                    <th>Tổng cộng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach ($cartItems as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($item->book->cover_image)
                                    <img src="{{ str_starts_with($item->book->cover_image, 'imgs/') ? asset($item->book->cover_image) : asset('storage/' . $item->book->cover_image) }}" width="40" height="60" class="me-3 shadow-sm" style="object-fit: cover;">
                                @endif
                                <span>{{ $item->book->title }}</span>
                            </div>
                        </td>
                        <td>{{ number_format($item->price, 0, ',', '.') }} đ</td>
                        <td>
                            
                            <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-flex">
                                @csrf
                                
                                @method('POST') 
                                
                                <input 
                                    type="number" 
                                    name="quantity" 
                                    value="{{ $item->quantity }}" 
                                    min="1" 
                                    max="{{ $item->book->quantity }}" 
                                    class="form-control form-control-sm me-2" 
                                    style="width: 70px;" 

                                    onchange="this.form.submit()" 
                                    required
                                >
                            </form>
                            @if($item->quantity > $item->book->quantity)
                                <small class="text-danger">Vượt quá tồn kho (Max: {{ $item->book->quantity }})</small>
                            @elseif($item->book->quantity < 5)
                                <small class="text-warning">Chỉ còn {{ $item->book->quantity }} trong kho!</small>
                            @endif
                        </td>
                        <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ</td>
                        <td>
                            <form action="{{ route('cart.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn muốn xóa mặt hàng này?')">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    @php $total += $item->price * $item->quantity; @endphp
                @endforeach
            </tbody>
        </table>
        
        <div class="d-flex justify-content-end align-items-center mt-4">
            <h4 class="me-4">Tổng tiền giỏ hàng: <span class="text-danger">{{ number_format($total, 0, ',', '.') }} đ</span></h4>
            <a href="{{ route('checkout') }}" class="btn btn-success btn-lg">Thanh toán (Checkout)</a>
        </div>
    @endif
</div>
@endsection