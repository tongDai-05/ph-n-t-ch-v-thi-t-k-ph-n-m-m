<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    
    public function index()
    {
        $orders = Order::latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    
    
    public function show(Order $order)
    {
        $order->load('items');

        $statuses = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đã giao hàng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return view('admin.orders.show', compact('order', 'statuses'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:processing,shipped,completed,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng!');
    }
    
    public function processRefund(Order $order)
    {
        
        if (in_array($order->status, ['cancelled', 'completed'])) {
            return redirect()->back()->with('error', 'Không thể hoàn tiền cho đơn hàng đã ' . $order->status . '.');
        }

        DB::beginTransaction();
        try {
           
            $order->update(['status' => 'cancelled']);

            
            foreach ($order->items as $item) {
                
                $book = Book::find($item->book_id);
                if ($book) {
                    $book->increment('quantity', $item->quantity);
                }
            }
            
            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)->with('success', 'Đã hủy và hoàn tiền thành công! Tồn kho đã được cập nhật.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->with('error', 'Lỗi: Không thể xử lý hoàn tiền. Vui lòng thử lại. Lỗi chi tiết: ' . $e->getMessage());
        }
    }
    public function dashboard(Request $request)
{

    $inventory = \App\Models\Book::select('title', 'quantity', 'price')->get();


    $date = $request->input('date', Carbon::today()->toDateString());
    $month = $request->input('month', Carbon::now()->month);
    $year = $request->input('year', Carbon::now()->year);
    $viewType = $request->input('view_type', 'day');

    $query = \App\Models\Order::where('status', 'completed');

  
    if ($viewType == 'day') {
        $query->whereDate('created_at', $date);
    } elseif ($viewType == 'month') {
        $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
    } else {
        $query->whereYear('created_at', $year);
    }

    $orders = $query->with('items')->get();

    $totalRevenue = $orders->sum('total_price');
    $booksSold = [];

    foreach ($orders as $order) {
        foreach ($order->items as $item) {
            if (isset($booksSold[$item->book_id])) {
                $booksSold[$item->book_id]['quantity'] += $item->quantity;
            } else {
                $booksSold[$item->book_id] = [
                    'title' => $item->book_title,
                    'quantity' => $item->quantity
                ];
            }
        }
    }

    return view('admin.dashboard', compact('inventory', 'totalRevenue', 'booksSold', 'viewType', 'date', 'month', 'year'));
}
}
