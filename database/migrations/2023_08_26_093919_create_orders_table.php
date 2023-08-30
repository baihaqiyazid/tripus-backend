<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id');
            $table->string('feed_id');
            $table->string('name');
            $table->string('email');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->integer('qty');
            $table->string('bank');
            $table->string('va_number');
            $table->integer('fee');
            $table->integer('admin_price');
            $table->integer('total_price');
            $table->enum('status', ['success', 'pending', 'cancel', 'expired']);
            $table->text('response_midtrans');
            $table->string('expire_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
