<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:suppliers',
            'phone' => 'required',
            'address' => 'required',
            'supply_date' => 'required|date',
        ]);

        Supplier::create($request->all());
        return redirect()->route('admin.suppliers.index')->with('success', 'Thêm nhà cung cấp thành công!');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'required',
            'address' => 'required',
            'supply_date' => 'required|date',
        ]);

        $supplier->update($request->all());
        return redirect()->route('admin.suppliers.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success', 'Xóa thành công!');
    }
}