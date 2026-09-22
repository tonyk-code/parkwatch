<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('site_id');
            $table->unsignedInteger('reported_by');

            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedInteger('spot_id')->nullable();

            $table->unsignedInteger('resolved_by')->nullable();

            $table->string('category', 20);
            $table->string('severity', 10)->nullable();
            $table->string('description', 1000)->nullable();

            $table->string('status', 20)->default('open');

            $table->dateTime('resolved_at')->nullable();
            $table->string('resolution', 1000)->nullable();

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();

            $table->foreign('session_id')
                ->references('id')
                ->on('parking_sessions')
                ->nullOnDelete();

            $table->foreign('spot_id')
                ->references('id')
                ->on('spots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};