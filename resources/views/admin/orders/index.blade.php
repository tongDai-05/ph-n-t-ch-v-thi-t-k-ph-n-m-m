@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>📋 Quản lý Đơn hàng</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Mã ĐH</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày đặt</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                {{-- SỬA: Thêm class table-warning nếu khách yêu cầu hủy --}}
                <tr class="{{ $order->cancellation_requested ? 'table-warning' : '' }}">
                    <td>#{{ $order->id }}</td>
                    <td>
                        {{ $order->customer_name }} ({{ $order->customer_email }})
                        {{-- THÊM: Nhãn cảnh báo yêu cầu hủy đơn --}}
                        @if($order->cancellation_requested && $order->status !== 'cancelled')
                            <br>
                            <span class="badge bg-danger">⚠️ Yêu cầu hủy đơn</span>
                        @endif
                    </td>
                    <td>{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                    <td>
                        @php
                            $badgeClass = [
                                'pending' => 'bg-warning text-dark',
                                'processing' => 'bg-info text-dark',
                                'shipped' => 'bg-primary',
                                'completed' => 'bg-success',
                                'cancelled' => 'bg-danger',
                            ][$order->status] ?? 'bg-secondary';

                            $statusLabels = [
                                'pending' => 'Chờ duyệt',
                                'processing' => 'Đang xử lý',
                                'shipped' => 'Đang giao',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy',
                            ];
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">Xem chi tiết</a>
                        
                        {{-- THÊM: Nút duyệt nhanh nếu có yêu cầu hủy --}}
                        @if($order->cancellation_requested && $order->status !== 'cancelled')
                            <form action="{{ route('admin.orders.processRefund', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận hoàn tiền và hủy đơn này?')">
                                @csrf
                                <button class="btn btn-sm btn-danger">Hủy & Hoàn tiền</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted p-4">
                        Không có đơn hàng nào.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $orders->links() }}
</div>
@endsection