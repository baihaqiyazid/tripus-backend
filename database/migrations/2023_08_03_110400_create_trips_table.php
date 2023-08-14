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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('description')->nullable();
            $table->string('location');
            $table->integer('fee');
            $table->dateTime('date_from');
            $table->dateTime('date_end');
            $table->string('transportation')->nullable();
            $table->string('guide')->nullable();
            $table->string('porter')->nullable();
            $table->string('eat')->nullable();
            $table->string('breakfast')->nullable();
            $table->string('lunch')->nullable();
            $table->string('permit')->nullable();
            $table->string('others')->nullable();
            $table->string('exclude')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
