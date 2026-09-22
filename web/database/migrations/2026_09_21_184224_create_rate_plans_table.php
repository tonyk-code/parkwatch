<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('site_id');

            $table->string('name', 100);
            $table->char('currency', 3);

            $table->unsignedInteger('grace_minutes')->default(0);
            $table->bigInteger('daily_max_minor')->nullable();

            $table->string('rounding', 10)->default('up');
            $table->unsignedInteger('rounding_increment_minutes')->default(60);

            $table->integer('priority')->default(0);

            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();

            $table->boolean('is_active')->default(true);

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_plans');
    }
};