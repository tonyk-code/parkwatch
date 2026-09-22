<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('organization_id');

            $table->string('plate_normalised', 20);
            $table->string('plate_display', 25);
            $table->string('plate_region', 10)->nullable();

            $table->string('vehicle_type', 20)->default('car');
            $table->string('make', 50)->nullable();
            $table->string('model', 50)->nullable();
            $table->string('colour', 30)->nullable();

            $table->unsignedInteger('owner_user_id')->nullable();

            $table->boolean('is_blacklisted')->default(false);
            $table->string('notes', 500)->nullable();

            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};