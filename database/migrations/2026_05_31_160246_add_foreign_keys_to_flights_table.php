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
        Schema::table('flights', function (Blueprint $table) {
            $table->foreign(['departure_city_id'], 'flights_ibfk_1')->references(['id'])->on('cities')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['arrival_city_id'], 'flights_ibfk_2')->references(['id'])->on('cities')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropForeign('flights_ibfk_1');
            $table->dropForeign('flights_ibfk_2');
        });
    }
};
