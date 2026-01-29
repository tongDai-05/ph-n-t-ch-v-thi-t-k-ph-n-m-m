<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
    Schema::table('books', function (Blueprint $table) {
        $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null'); 
        $table->string('publisher')->nullable(); 
        $table->decimal('import_price', 10, 2)->default(0);
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            //
        });
    }
};
