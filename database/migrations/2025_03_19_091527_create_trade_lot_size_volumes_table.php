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
        Schema::create('trade_lot_size_volumes', function (Blueprint $table) {
            $table->id();
            $table->integer('tlv_seq')->nullable();
            $table->unsignedInteger('tlv_year')->nullable();
            $table->integer('tlv_month')->nullable();
            $table->integer('tlv_day')->nullable();
            $table->double('tlv_lotsize')->nullable();
            $table->double('tlv_volume_usd')->nullable();
            $table->bigInteger('tlv_start_dealid')->nullable();
            $table->timestamp('created_at')->nullable(); 
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_lot_size_volumes');
    }
};
