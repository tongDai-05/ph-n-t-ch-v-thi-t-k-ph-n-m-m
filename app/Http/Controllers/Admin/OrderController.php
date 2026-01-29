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

        $updateData = ['status' => $validated['status']];

      
        if ($validated['status'] === 'completed') {
            $updateData['payment_status'] = 'paid';
        }

        $order->update($updateData);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng và thanh toán!');
    }
    
     
    public function processRefund(Order $order)
    {
        if (in_array($order->status, ['cancelled', 'completed'])) {
            return redirect()->back()->with('error', 'Không thể hoàn tiền cho đơn hàng đã ' . $order->status . '.');
        }

        DB::beginTransaction();
        try {
            
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'unpaid' 
            ]);

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
    // Lấy tồn kho kèm phân loại
    $inventory = \App\Models\Book::with('category')->get();

    // Lọc theo thời gian (giữ nguyên logic của bạn)
    $date = $request->input('date', \Carbon\Carbon::today()->toDateString());
    $viewType = $request->input('view_type', 'day');

    // Lấy các đơn hàng hoàn thành để tính lợi nhuận
    // Lưu ý: Quan hệ trong Model Order của bạn là 'orderItems'
    $orders = \App\Models\Order::where('status', 'completed')
                ->with('orderItems.book')
                ->get();

    $totalRevenue = 0;
    $totalProfit = 0;
    $booksSold = [];

    foreach ($orders as $order) {
        $totalRevenue += $order->total_price;
        foreach ($order->orderItems as $item) {
            $book = $item->book;
            $revenue = $item->price * $item->quantity;
            // Lợi nhuận = Doanh thu - (Giá nhập * Số lượng bán)
            $profit = $revenue - (($book->import_price ?? 0) * $item->quantity);

            $totalProfit += $profit;

            if (isset($booksSold[$item->book_id])) {
                $booksSold[$item->book_id]['quantity'] += $item->quantity;
                $booksSold[$item->book_id]['revenue'] += $revenue;
                $booksSold[$item->book_id]['profit'] += $profit;
            } else {
                $booksSold[$item->book_id] = [
                    'title' => $book->title ?? ($item->book_title ?? 'Sách đã xóa'),
                    'quantity' => $item->quantity,
                    'revenue' => $revenue,
                    'profit' => $profit
                ];
            }
        }
    }

    return view('admin.dashboard', compact('inventory', 'totalRevenue', 'totalProfit', 'booksSold', 'viewType', 'date'));
}
}