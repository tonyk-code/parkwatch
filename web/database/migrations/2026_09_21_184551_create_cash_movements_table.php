<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('shift_id');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedInteger('created_by');

            $table->string('type', 15);
            $table->bigInteger('amount_minor');
            $table->string('reason', 255)->nullable();
            $table->dateTime('occurred_at');

            $table->foreign('shift_id')
                ->references('id')
                ->on('shifts')
                ->cascadeOnDelete();

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};