<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration để thêm cột.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Thêm phương thức thanh toán (mặc định là cod) sau cột total_price
            $table->string('payment_method')->default('cod')->after('total_price');
            
            // Thêm trạng thái thanh toán (mặc định là chưa thanh toán) sau cột payment_method
            $table->string('payment_status')->default('unpaid')->after('payment_method');
            
            // Đảm bảo có cột này để hỗ trợ chức năng yêu cầu hủy đơn trong code của bạn
            if (!Schema::hasColumn('orders', 'cancellation_requested')) {
                $table->boolean('cancellation_requested')->default(false)->after('status');
            }
        });
    }

    /**
     * Hoàn tác migration (xóa cột).
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'cancellation_requested']);
        });
    }
};