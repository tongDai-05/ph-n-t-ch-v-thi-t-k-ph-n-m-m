@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📋 Chi tiết Đơn hàng #{{ $order->id }}</h2>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">Quay lại danh sách</a>
    </div>

   
    @if($order->cancellation_requested && $order->status !== 'cancelled')
        <div class="alert alert-danger shadow-sm d-flex justify-content-between align-items-center">
            <div>
                <h4 class="alert-heading mb-1">⚠️ Khách hàng yêu cầu hủy đơn & hoàn tiền!</h4>
                <p class="mb-0">Vui lòng kiểm tra lý do hoặc liên hệ khách hàng trước khi thực hiện hoàn tiền.</p>
            </div>
            <form action="{{ route('admin.orders.processRefund', $order->id) }}" method="POST" onsubmit="return confirm('Xác nhận hoàn tiền? Sách sẽ tự động cộng lại vào kho.')">
                @csrf
                <button type="submit" class="btn btn-danger">Xác nhận Hoàn tiền & Hủy đơn</button>
            </form>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-5">
           
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    Thông tin Khách hàng & Vận chuyển
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Khách hàng:</strong> {{ $order->customer_name }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $order->customer_email }}</p>
                    <p class="mb-2"><strong>Điện thoại:</strong> {{ $order->customer_phone }}</p>
                    <p class="mb-2"><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>
                    <hr>
                    <p class="mb-2"><strong>Phương thức thanh toán:</strong> 
                        <span class="badge bg-info text-dark">
                            {{ $order->payment_method === 'online' ? '💳 Chuyển khoản' : '💵 Khi nhận hàng (COD)' }}
                        </span>
                    </p>
                    <p class="mb-2"><strong>Trạng thái tiền:</strong> 
                        <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                        </span>
                    </p>
                    <h4 class="mt-3 text-danger">Total: {{ number_format($order->total_price, 0, ',', '.') }} đ</h4>
                </div>
            </div>

            
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    Xử lý Đơn hàng
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT') 
                        <div class="mb-3">
                            <label class="form-label fw-bold">Trạng thái hiện tại</label>
                            <select name="status" class="form-select border-primary" required>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100 shadow-sm">Cập nhật Trạng thái</button>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    Sản phẩm trong đơn hàng
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sách</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $item->book_title }}</div>
                                            <small class="text-muted">Tác giả: {{ $item->book_author }}</small><br>
                                            <small class="text-muted">Đơn giá: {{ number_format($item->unit_price, 0, ',', '.') }} đ</small>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge bg-light text-dark border">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="text-end align-middle fw-bold">
                                            {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }} đ
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">Tổng số tiền:</td>
                                    <td class="text-end text-danger fw-bold" style="font-size: 1.2rem;">
                                        {{ number_format($order->total_price, 0, ',', '.') }} đ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection