<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parking_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('site_id');
            $table->unsignedInteger('zone_id')->nullable();
            $table->unsignedInteger('spot_id')->nullable();
            $table->unsignedInteger('vehicle_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('rate_plan_id')->nullable();
            $table->unsignedInteger('permit_id')->nullable();
            $table->unsignedInteger('entry_gate_id')->nullable();
            $table->unsignedInteger('exit_gate_id')->nullable();

            $table->string('reference_code', 20)->unique();

            $table->dateTime('entered_at');
            $table->dateTime('exited_at')->nullable();

            $table->string('status', 20)->default('active');
            $table->string('session_mode', 10);

            $table->bigInteger('amount_due')->default(0);
            $table->bigInteger('amount_paid')->default(0);
            $table->bigInteger('amount_waived')->default(0);

            $table->char('currency', 3);

            $table->string('entry_plate_read', 25)->nullable();
            $table->string('exit_plate_read', 25)->nullable();

            $table->unsignedInteger('entry_media_id')->nullable();
            $table->unsignedInteger('exit_media_id')->nullable();

            $table->unsignedInteger('opened_by')->nullable();
            $table->unsignedInteger('closed_by')->nullable();

            $table->string('closed_reason', 255)->nullable();
            $table->string('notes', 500)->nullable();

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

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->nullOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('entry_gate_id')
                ->references('id')
                ->on('gates')
                ->nullOnDelete();

            $table->foreign('exit_gate_id')
                ->references('id')
                ->on('gates')
                ->nullOnDelete();

            // rate_plan_id, permit_id, entry_media_id, exit_media_id
            // receive foreign keys after those tables exist.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_sessions');
    }
};