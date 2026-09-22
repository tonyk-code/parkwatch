<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spot_states', function (Blueprint $table) {
            $table->unsignedInteger('spot_id')->primary();

            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedInteger('reservation_id')->nullable();
            $table->unsignedInteger('camera_id')->nullable();

            $table->string('status', 20)->default('free');
            $table->decimal('confidence', 4, 3)->nullable();
            $table->string('source', 15)->default('cv');

            $table->boolean('manual_override')->default(false);
            $table->unsignedInteger('override_by')->nullable();
            $table->string('override_reason', 255)->nullable();
            $table->dateTime('override_until')->nullable();

            $table->unsignedInteger('polygon_version');
            $table->dateTime('last_changed_at');
            $table->dateTime('last_seen_at');

            $table->foreign('spot_id')
                ->references('id')
                ->on('spots')
                ->cascadeOnDelete();

            $table->foreign('camera_id')
                ->references('id')
                ->on('cameras')
                ->nullOnDelete();

            // session_id and reservation_id will get foreign keys
            // after those tables exist.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_states');
    }
};