<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detection_events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->char('event_uuid', 36)->unique();

            $table->unsignedInteger('camera_id');
            $table->unsignedInteger('spot_id')->nullable();
            $table->unsignedInteger('site_id');

            $table->string('event_type', 25);
            $table->json('payload')->nullable();
            $table->decimal('confidence', 4, 3)->nullable();

            $table->unsignedInteger('polygon_version')->nullable();
            $table->string('frame_ref', 500)->nullable();

            $table->dateTime('observed_at');
            $table->dateTime('received_at');

            $table->foreign('camera_id')
                ->references('id')
                ->on('cameras')
                ->cascadeOnDelete();

            $table->foreign('spot_id')
                ->references('id')
                ->on('spots')
                ->nullOnDelete();

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_events');
    }
};