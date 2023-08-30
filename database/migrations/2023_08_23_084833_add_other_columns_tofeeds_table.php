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
        Schema::table('feeds', function (Blueprint $table) {
            $table->string('include')->nullable()->after('location');
            $table->string('exclude')->nullable()->after('location');
            $table->string('others')->nullable()->after('location');
            $table->string('category_id')->nullable()->after('location');
            $table->string('date_start')->nullable()->after('location');
            $table->string('date_end')->nullable()->after('location');
            $table->double('fee')->nullable()->after('location');
            $table->integer('max_person')->nullable()->after('location');
            $table->string('payment_account')->nullable()->after('location');
            $table->string('title')->nullable()->after('location');
            $table->string('type')->nullable()->after('location');
            $table->string('meeting_point')->nullable()->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
