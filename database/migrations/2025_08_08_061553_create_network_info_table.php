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
        Schema::create('network_info', function (Blueprint $table) {
            $table->id();
            $table->integer('chain_id')->index();
            $table->string('name', 50);
            $table->string('symbol', 20);
            $table->string('erc20_token_address', 42)->nullable();
            $table->string('defillama_chain_slug', 50)->nullable();
            $table->tinyInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_info');
    }
};
