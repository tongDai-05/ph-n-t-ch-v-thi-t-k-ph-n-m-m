@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Báo cáo Quản trị</h2>

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
                <button type="submit" class="btn btn-primary">Lọc báo cáo</button>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-6">
            <h4>Sách tồn kho</h4>
            <table class="table table-bordered">
                <thead>
                    <tr><th>Tên sách</th><th>Tồn kho</th></tr>
                </thead>
                <tbody>
                    @foreach($inventory as $book)
                    <tr class="{{ $book->quantity < 5 ? 'table-danger' : '' }}">
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <h4>Thống kê bán hàng</h4>
            <div class="alert alert-success">
                <strong>Tổng doanh thu:</strong> {{ number_format($totalRevenue) }} VNĐ
            </div>
            <table class="table table-striped">
                <thead>
                    <tr><th>Tên sách</th><th>Số lượng đã bán</th></tr>
                </thead>
                <tbody>
                    @foreach($booksSold as $sold)
                    <tr>
                        <td>{{ $sold['title'] }}</td>
                        <td>{{ $sold['quantity'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection