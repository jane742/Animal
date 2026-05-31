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
        Schema::create('flight_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('flight_id')->index();
            $table->unsignedBigInteger('airplane_id')->index();
            $table->dateTime('departure_time')->index();
            $table->dateTime('arrival_time');
            $table->decimal('base_price');
            $table->enum('status', ['scheduled', 'delayed', 'departed', 'arrived', 'cancelled'])->default('scheduled');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_schedules');
    }
};
