@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>🕒 Lịch sử Đơn hàng của bạn</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if($orders->isEmpty())
        <div class="alert alert-info">Bạn chưa có đơn hàng nào. <a href="{{ route('books.index') }}">Bắt đầu mua sắm ngay!</a></div>
    @else
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mã ĐH</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
    <tbody>
    @foreach ($orders as $order)
        <tr>
            <td>#{{ $order->id }}</td>
            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
            <td>
                <small class="d-block text-muted">
                    {{ $order->payment_method === 'online' ? '💳 Chuyển khoản' : '💵 Khi nhận hàng (COD)' }}
                </small>
                <span class="badge {{ $order->payment_status === 'paid' ? 'text-success' : 'text-muted' }}">
                    {{ $order->payment_status === 'paid' ? '● Đã thanh toán' : '○ Chưa thanh toán' }}
                </span>
            </td>
            <td><strong class="text-danger">{{ number_format($order->total_price, 0, ',', '.') }} đ</strong></td>
            <td>
                @php
                    $statusMap = [
                        'pending' => ['label' => 'Chờ duyệt', 'class' => 'bg-warning text-dark', 'note' => 'Đơn hàng đang chờ quản trị viên xác nhận.'],
                        'processing' => ['label' => 'Đã duyệt', 'class' => 'bg-info text-dark', 'note' => 'Admin đã duyệt, đang chuẩn bị sách.'],
                        'shipped' => ['label' => 'Đang giao', 'class' => 'bg-primary', 'note' => 'Sách đang trên đường đến với bạn.'],
                        'completed' => ['label' => 'Hoàn thành', 'class' => 'bg-success', 'note' => 'Cảm ơn bạn đã mua sách!'],
                        'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-danger', 'note' => 'Đơn hàng đã bị hủy. Vui lòng kiểm tra lại.'],
                    ];
                    $statusInfo = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'bg-secondary', 'note' => ''];
                @endphp
                <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                
                {{-- Hiển thị dòng thông báo nhỏ ngay dưới trạng thái --}}
                <small class="d-block text-muted mt-1" style="font-size: 0.8rem;">
                    <i>{{ $statusInfo['note'] }}</i>
                </small>
            </td>
            <td>
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-info">Chi tiết</a>
            </td>
        </tr>
    @endforeach
    </tbody>
        </table>
        
        {{ $orders->links() }}
    @endif
</div>
@endsection
