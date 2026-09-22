<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('session_id');

            $table->string('event_type', 40);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();

            $table->json('payload')->nullable();

            $table->string('actor_type', 15);
            $table->unsignedInteger('actor_id')->nullable();

            $table->dateTime('occurred_at');

            $table->foreign('session_id')
                ->references('id')
                ->on('parking_sessions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_events');
    }
};