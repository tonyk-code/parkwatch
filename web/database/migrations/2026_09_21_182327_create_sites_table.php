<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('organization_id');

            $table->string('name', 120);
            $table->string('code', 30)->unique();
            $table->string('address', 250)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('timezone', 50)->nullable();
            $table->unsignedInteger('total_capacity')->nullable();

            $table->string('session_mode', 10)->default('spot');

            $table->unsignedInteger('occupancy_enter_seconds')->default(10);
            $table->unsignedInteger('occupancy_exit_seconds')->default(45);

            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_24_hours')->default(false);
            $table->boolean('is_active')->default(true);

            $table->json('settings')->nullable();

            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};