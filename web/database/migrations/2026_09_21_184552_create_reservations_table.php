<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('site_id');
            $table->unsignedInteger('zone_id')->nullable();
            $table->unsignedInteger('spot_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('vehicle_id')->nullable();

            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();

            $table->dateTime('starts_at');
            $table->dateTime('expires_at');

            $table->string('status', 15)->default('held');
            $table->bigInteger('amount_minor')->default(0);

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();

            $table->foreign('zone_id')
                ->references('id')
                ->on('zones')
                ->nullOnDelete();

            $table->foreign('spot_id')
                ->references('id')
                ->on('spots')
                ->nullOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->nullOnDelete();

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->nullOnDelete();

            $table->foreign('session_id')
                ->references('id')
                ->on('parking_sessions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};