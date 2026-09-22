<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('session_validations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('session_id');
            $table->unsignedInteger('validation_id');

            $table->bigInteger('applied_minor');

            $table->unsignedInteger('applied_by')->nullable();
            $table->dateTime('applied_at');

            $table->foreign('session_id')
                ->references('id')
                ->on('parking_sessions')
                ->cascadeOnDelete();

            $table->foreign('validation_id')
                ->references('id')
                ->on('validations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_validations');
    }
};