<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id');

            $table->string('token', 255)->unique();
            $table->string('platform', 10);
            $table->dateTime('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};