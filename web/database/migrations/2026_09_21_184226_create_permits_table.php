<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permits', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('organization_id');
            $table->unsignedInteger('site_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('vehicle_id');

            $table->unsignedInteger('zone_id')->nullable();
            $table->unsignedInteger('spot_id')->nullable();

            $table->string('permit_type', 20);

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->bigInteger('price_minor')->nullable();
            $table->string('billing_cycle', 15)->nullable();

            $table->string('status', 15)->default('active');
            $table->boolean('auto_renew')->default(false);

            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->cascadeOnDelete();

            $table->foreign('zone_id')
                ->references('id')
                ->on('zones')
                ->nullOnDelete();

            $table->foreign('spot_id')
                ->references('id')
                ->on('spots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permits');
    }
};