<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('site_id');

            $table->string('disk', 30);
            $table->string('path', 500);
            $table->string('mime', 50)->nullable();
            $table->bigInteger('bytes')->nullable();

            $table->string('purpose', 25);

            $table->string('related_type', 40)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();

            $table->dateTime('captured_at')->nullable();
            $table->dateTime('expires_at')->nullable();

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};