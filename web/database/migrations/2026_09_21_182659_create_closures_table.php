<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('closures', function (Blueprint $table) {
            $table->increments('id');

            $table->string('closeable_type', 20);
            $table->unsignedInteger('closeable_id');

            $table->string('reason', 255)->nullable();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();

            $table->unsignedInteger('created_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closures');
    }
};