<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('payment_id');

            $table->string('event_id', 100)->unique();
            $table->string('event_type', 50);
            $table->string('status', 15);

            $table->json('payload')->nullable();

            $table->dateTime('occurred_at');
            $table->dateTime('created_at');

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};