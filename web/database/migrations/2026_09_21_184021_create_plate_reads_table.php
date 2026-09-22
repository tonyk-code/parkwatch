<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plate_reads', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('camera_id');
            $table->unsignedInteger('gate_id')->nullable();
            $table->unsignedInteger('site_id');

            $table->unsignedInteger('matched_vehicle_id')->nullable();
            $table->unsignedBigInteger('matched_session_id')->nullable();
            $table->unsignedInteger('media_id')->nullable();

            $table->string('raw_text', 40);
            $table->string('normalised_text', 20);

            $table->decimal('confidence', 4, 3)->nullable();
            $table->integer('match_distance')->nullable();

            $table->string('match_status', 20)->default('unmatched');

            $table->dateTime('observed_at');

            $table->foreign('camera_id')
                ->references('id')
                ->on('cameras')
                ->cascadeOnDelete();

            $table->foreign('gate_id')
                ->references('id')
                ->on('gates')
                ->nullOnDelete();

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();

            $table->foreign('matched_vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->nullOnDelete();

            $table->foreign('matched_session_id')
                ->references('id')
                ->on('parking_sessions')
                ->nullOnDelete();

            // media_id receives its foreign key after media_assets exists.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plate_reads');
    }
};