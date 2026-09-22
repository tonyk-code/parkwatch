<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gates', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('site_id');
            $table->unsignedInteger('camera_id')->nullable();

            $table->string('name', 100);
            $table->string('direction', 10)->default('entry');
            $table->boolean('has_barrier')->default(false);
            $table->string('barrier_endpoint', 255)->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gates');
    }
};