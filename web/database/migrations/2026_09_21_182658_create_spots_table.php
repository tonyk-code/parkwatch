<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spots', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('zone_id');
            $table->unsignedInteger('camera_id')->nullable();

            $table->string('code', 20);
            $table->string('spot_type', 20)->default('standard');

            $table->json('polygon')->nullable();
            $table->unsignedInteger('polygon_version')->default(1);

            $table->decimal('display_x', 6, 3)->nullable();
            $table->decimal('display_y', 6, 3)->nullable();

            $table->boolean('is_bookable')->default(false);
            $table->boolean('is_active')->default(true);

            $table->foreign('zone_id')
                ->references('id')
                ->on('zones')
                ->cascadeOnDelete();

            $table->foreign('camera_id')
                ->references('id')
                ->on('cameras')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spots');
    }
};