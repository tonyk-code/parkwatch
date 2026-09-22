<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cameras', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('site_id');
            $table->unsignedInteger('zone_id')->nullable();
            $table->unsignedInteger('gate_id')->nullable();

            $table->string('name', 100);
            $table->string('stream_url', 500)->nullable();
            $table->string('stream_protocol', 10)->default('rtsp');

            $table->unsignedInteger('resolution_w')->nullable();
            $table->unsignedInteger('resolution_h')->nullable();
            $table->unsignedInteger('fps_target')->nullable();

            $table->string('purpose', 15)->default('occupancy');

            $table->string('reference_frame_path', 500)->nullable();
            $table->dateTime('calibrated_at')->nullable();
            $table->unsignedInteger('calibrated_by')->nullable();

            $table->string('status', 25)->default('offline');
            $table->dateTime('last_heartbeat_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();

            $table->foreign('zone_id')
                ->references('id')
                ->on('zones')
                ->nullOnDelete();

            $table->foreign('gate_id')
                ->references('id')
                ->on('gates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cameras');
    }
};