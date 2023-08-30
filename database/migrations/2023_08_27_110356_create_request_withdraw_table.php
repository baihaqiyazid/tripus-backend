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
        Schema::create('request_withdraw_trips', function (Blueprint $table) {
            $table->id();
            $table->integer('feed_id');
            $table->string('file');
            $table->enum('status', ['accept', 'reject', 'review']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_withdraw_trips');
    }
};
