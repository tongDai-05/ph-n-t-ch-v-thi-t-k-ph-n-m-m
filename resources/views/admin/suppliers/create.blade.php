@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Thêm Nhà cung cấp</h1>
    <form action="{{ route('admin.suppliers.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label>Tên nhà cung cấp</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Số điện thoại</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Địa chỉ</label>
            <input type="text" name="address" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Ngày cung cấp</label>
            <input type="date" name="supply_date" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Lưu lại</button>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection