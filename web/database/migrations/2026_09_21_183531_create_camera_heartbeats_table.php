<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('camera_heartbeats', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('camera_id');
            $table->dateTime('reported_at');

            $table->decimal('fps_actual', 5, 2)->nullable();
            $table->unsignedInteger('frames_processed')->nullable();
            $table->unsignedInteger('buffer_depth')->nullable();

            $table->decimal('drift_score', 6, 4)->nullable();
            $table->string('worker_version', 20)->nullable();

            $table->foreign('camera_id')
                ->references('id')
                ->on('cameras')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_heartbeats');
    }
};