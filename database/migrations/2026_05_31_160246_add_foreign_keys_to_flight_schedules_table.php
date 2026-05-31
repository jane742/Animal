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
        Schema::table('flight_schedules', function (Blueprint $table) {
            $table->foreign(['flight_id'], 'flight_schedules_ibfk_1')->references(['id'])->on('flights')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['airplane_id'], 'flight_schedules_ibfk_2')->references(['id'])->on('airplanes')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flight_schedules', function (Blueprint $table) {
            $table->dropForeign('flight_schedules_ibfk_1');
            $table->dropForeign('flight_schedules_ibfk_2');
        });
    }
};
