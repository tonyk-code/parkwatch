<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('site_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('closed_by')->nullable();

            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();

            $table->bigInteger('opening_float_minor')->default(0);
            $table->bigInteger('expected_cash_minor')->default(0);
            $table->bigInteger('counted_cash_minor')->nullable();
            $table->bigInteger('variance_minor')->nullable();

            $table->string('status', 15)->default('open');
            $table->string('notes', 500)->nullable();

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};