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
        return Auth::check() 
            ? Auth::user()->carts()->first()
            : Cart::where('session_id', session()->getId())->first();
    }

    public function checkout()
    {
        $cart = $this->getCartForCheckout();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $cartItems = $cart->items()->with('book')->get();
        
        
        foreach ($cartItems as $item) {
            if ($item->quantity > $item->book->quantity) {
                return redirect()->route('cart.index')->with('error', "Sách '{$item->book->title}' không đủ hàng.");
            }
        }

        $total = $cartItems->sum(fn($item) => $item->price * $item->quantity);

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
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'required|email|max:255',
            'customer_phone'   => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => 'required|in:cod,online', 
        ]);

        $cart = $this->getCartForCheckout();
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống.');
        }

        DB::beginTransaction();
        try {
            $cartItems = $cart->items()->with('book')->get();
            $totalPrice = $cartItems->sum(fn($item) => $item->price * $item->quantity);

           
            $order = Order::create(array_merge($validated, [
                'user_id'        => Auth::id(),
                'total_price'    => $totalPrice,
                'status'         => 'pending', 
                'payment_method' => $request->payment_method,
                'payment_status' => ($request->payment_method === 'cod') ? 'unpaid' : 'pending_online',
            ]));
            foreach ($cartItems as $item) {
                
                $book = Book::where('id', $item->book_id)->lockForUpdate()->first();

                if ($item->quantity > $book->quantity) {
                    throw new \Exception("Sách '{$book->title}' đã hết hàng trong lúc bạn thao tác.");
                }

                $book->decrement('quantity', $item->quantity);

                $order->items()->create([
                    'book_id'     => $book->id,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->price,
                    'book_title'  => $book->title,
                    'book_author' => $book->author,
                ]);
            }

           
            $cart->delete(); 

            DB::commit();

            
            session()->put('last_order_id', $order->id);

            return redirect()->route('orders.show', $order->id)
                             ->with('success', 'Đặt hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    public function showOrder(Order $order)
    {
        
        $isOwner = Auth::check() && $order->user_id === Auth::id();
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';
        $isGuestRecentlyOrdered = session('last_order_id') == $order->id;

        if (!$isOwner && !$isAdmin && !$isGuestRecentlyOrdered) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }
        
        return view('cart.order-success', compact('order'));
    }

    public function orderHistory()
    {
        if (!Auth::check()) return redirect()->route('login');

        $orders = Auth::user()->orders()->latest()->paginate(10);
        return view('cart.order-history', compact('orders'));
    }

    public function requestCancellation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Hành động không hợp lệ.');
        }

        if (in_array($order->status, ['cancelled', 'completed', 'shipping'])) {
            return redirect()->back()->with('error', 'Không thể hủy đơn hàng ở trạng thái này.');
        }

        if ($order->cancellation_requested) {
            return redirect()->back()->with('error', 'Bạn đã gửi yêu cầu trước đó.');
        }

        $order->update(['cancellation_requested' => true]);

        return redirect()->back()->with('success', 'Yêu cầu hủy đơn hàng đã được gửi.');
    }
}