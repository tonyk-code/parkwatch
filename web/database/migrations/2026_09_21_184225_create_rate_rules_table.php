<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rate_rules', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('rate_plan_id');

            $table->unsignedInteger('sequence');
            $table->unsignedInteger('from_minute');
            $table->unsignedInteger('to_minute')->nullable();

            $table->string('unit', 10);
            $table->bigInteger('price_minor');

            $table->unsignedSmallInteger('day_of_week_mask')->default(127);

            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();

            $table->string('vehicle_type', 20)->nullable();
            $table->string('spot_type', 20)->nullable();

            $table->foreign('rate_plan_id')
                ->references('id')
                ->on('rate_plans')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_rules');
    }
};