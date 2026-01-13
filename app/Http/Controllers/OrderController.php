<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected function getCartForCheckout()
    {
        $cart = Auth::check() 
            ? Auth::user()->carts()->first()
            : Cart::where('session_id', session()->getId())->first();
        
        return $cart;
    }


    public function checkout()
    {
        $cart = $this->getCartForCheckout();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống. Vui lòng thêm sách trước khi thanh toán.');
        }

        $cartItems = $cart->items()->with('book')->get();
        
        foreach ($cartItems as $item) {
            if ($item->quantity > $item->book->quantity) {
                return redirect()->route('cart.index')->with('error', 'Sách "' . $item->book->title . '" không đủ số lượng trong kho. Vui lòng cập nhật giỏ hàng.');
            }
        }

        $total = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $userData = Auth::check() ? [
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ] : [
            'name' => old('customer_name'),
            'email' => old('customer_email'),
        ];


        return view('cart.checkout', compact('cartItems', 'total', 'userData'));
    }

    public function processOrder(Request $request)
{
    $validated = $request->validate([
        'customer_name' => 'required|string|max:255',
        'customer_email' => 'required|email|max:255',
        'customer_phone' => 'required|string|max:20',
        'shipping_address' => 'required|string|max:500',
        'payment_method' => 'required|in:cod,online', 
    ]);

    $cart = $this->getCartForCheckout();

    if (!$cart || $cart->items->isEmpty()) {
         return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đã bị thay đổi. Vui lòng kiểm tra lại.');
    }
    
    $cartItems = $cart->items()->with('book')->get();

    DB::beginTransaction();

    try {
        $totalPrice = 0;

       
        $paymentStatus = ($request->payment_method === 'cod') ? 'unpaid' : 'pending_online';

        $order = Order::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'total_price' => 0,
            'status' => 'pending', 
            'payment_method' => $request->payment_method,
            'payment_status' => $paymentStatus,
            'cancellation_requested' => false,
        ]));

        foreach ($cartItems as $item) {
            $book = Book::find($item->book_id);

            if ($item->quantity > $book->quantity) {
                DB::rollBack();
                return redirect()->route('cart.index')->with('error', 'Sách "' . $book->title . '" không đủ số lượng trong kho.');
            }

            $book->decrement('quantity', $item->quantity);

            $order->items()->create([
                'book_id' => $book->id,
                'quantity' => $item->quantity,
                'unit_price' => $item->price,
                'book_title' => $book->title,
                'book_author' => $book->author,
            ]);

            $totalPrice += $item->price * $item->quantity;
        }

        $order->update(['total_price' => $totalPrice]);
        $cart->delete(); 

        DB::commit();

       
        return redirect()->route('orders.show', $order->id)->with('success', 'Đặt hàng thành công! Đơn hàng #' . $order->id . ' đang chờ quản trị viên xác nhận.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
    }
}
    public function showOrder(Order $order)
    {

        if (Auth::check()) {

            $orderUserId = $order->user_id;

            if ($orderUserId !== Auth::id() && Auth::user()->role !== 'admin') {
                abort(403, 'Bạn không có quyền xem đơn hàng này.');
            }
        } else {
             abort(403, 'Bạn phải đăng nhập để xem đơn hàng này.'); 
        }
        
        return view('cart.order-success', compact('order'));
    }

    public function orderHistory()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $orders = Auth::user()->orders()->latest()->paginate(10);

        return view('cart.order-history', compact('orders'));
    }

    public function requestCancellation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập đơn hàng này.');
        }

        if ($order->status === 'cancelled' || $order->status === 'completed') {
            return redirect()->back()->with('error', 'Đơn hàng đã hoàn thành hoặc đã bị hủy. Không thể yêu cầu hủy.');
        }

        if ($order->cancellation_requested) {
             return redirect()->back()->with('error', 'Bạn đã gửi yêu cầu hủy đơn hàng này trước đó.');
        }

        $order->update(['cancellation_requested' => true]);

        return redirect()->back()->with('success', 'Yêu cầu hủy/hoàn tiền đơn hàng #' . $order->id . ' đã được gửi tới Admin. Chúng tôi sẽ phản hồi sớm!');
    }
}
