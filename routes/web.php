<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('books.index');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::resource('books', BookController::class);
Route::controller(CartController::class)->group(function () {
    Route::get('/cart', 'index')->name('cart.index');
    Route::post('/cart/add', 'store')->name('cart.store');
    Route::post('/cart/update/{item}', 'update')->name('cart.update');
    Route::delete('/cart/remove/{item}', 'destroy')->name('cart.destroy');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/order', [OrderController::class, 'processOrder'])->name('order.process');
    Route::get('/orders/history', [OrderController::class, 'orderHistory'])->name('orders.history');
    Route::get('/order/{order}', [OrderController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/cancel-request', [OrderController::class, 'requestCancellation'])->name('orders.requestCancellation');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('orders/{order}/process-refund', [AdminOrderController::class, 'processRefund'])->name('orders.processRefund');
    Route::post('orders/{order}/admin-cancel', [AdminOrderController::class, 'adminCancelOrder'])->name('orders.adminCancel');
});