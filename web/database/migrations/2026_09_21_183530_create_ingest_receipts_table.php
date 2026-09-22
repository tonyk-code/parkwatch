<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ingest_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->char('event_uuid', 36)->unique();
            $table->unsignedInteger('camera_id');
            $table->dateTime('received_at');

            $table->foreign('camera_id')
                ->references('id')
                ->on('cameras')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingest_receipts');
    }
};