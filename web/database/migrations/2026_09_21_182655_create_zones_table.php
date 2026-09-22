<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('site_id');

            $table->string('name', 100);
            $table->string('code', 30)->nullable();
            $table->string('floor_label', 30)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->integer('display_order')->default(0);
            $table->string('map_image_path', 500)->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};