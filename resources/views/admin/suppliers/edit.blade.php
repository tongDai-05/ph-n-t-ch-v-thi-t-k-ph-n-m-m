@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chỉnh sửa Nhà cung cấp</h1>
    <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
            <label>Tên nhà cung cấp</label>
            <input type="text" name="name" value="{{ $supplier->name }}" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" value="{{ $supplier->email }}" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Số điện thoại</label>
            <input type="text" name="phone" value="{{ $supplier->phone }}" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Địa chỉ</label>
            <input type="text" name="address" value="{{ $supplier->address }}" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Ngày cung cấp</label>
            <input type="date" name="supply_date" value="{{ $supplier->supply_date }}" class="form-control" required>
        </div>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.suppliers.index') }}">Quản lý Nhà cung cấp</a>
        </li>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection