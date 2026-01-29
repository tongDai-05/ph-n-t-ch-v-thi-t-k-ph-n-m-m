@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Báo cáo Quản trị Chi tiết</h2>

    <form action="{{ route('admin.dashboard') }}" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <select name="view_type" class="form-control">
                    <option value="day" {{ $viewType == 'day' ? 'selected' : '' }}>Xem theo ngày</option>
                    <option value="month" {{ $viewType == 'month' ? 'selected' : '' }}>Xem theo tháng</option>
                    <option value="year" {{ $viewType == 'year' ? 'selected' : '' }}>Xem theo năm</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Lọc báo cáo</button>
            </div>
        </div>
    </form>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>Tổng Doanh Thu</h4>
                    <h3>{{ number_format($totalRevenue) }} VNĐ</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>Tổng Lợi Nhuận</h4>
                    <h3>{{ number_format($totalProfit ?? 0) }} VNĐ</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Quản lý Kho & Phân loại</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Tên sách</th>
                                <th>Phân loại</th>
                                <th>Nhà xuất bản</th>
                                <th>Giá nhập gần nhất</th>
                                <th>Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventory as $book)
                            <tr class="{{ $book->quantity < 5 ? 'table-danger' : '' }}">
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->category->name ?? 'Chưa phân loại' }}</td>
                                <td>{{ $book->publisher ?? 'N/A' }}</td>
                                <td>{{ number_format($book->import_price) }} VNĐ</td>
                                <td><strong>{{ $book->quantity }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Thống kê Bán hàng & Lợi nhuận chi tiết</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tên sách</th>
                                <th>Số lượng đã bán</th>
                                <th>Doanh thu</th>
                                <th>Lợi nhuận</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booksSold as $sold)
                            <tr>
                                <td>{{ $sold['title'] }}</td>
                                <td>{{ $sold['quantity'] }}</td>
                                <td>{{ number_format($sold['revenue'] ?? 0) }} VNĐ</td>
                                <td>{{ number_format($sold['profit'] ?? 0) }} VNĐ</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection