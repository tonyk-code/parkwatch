<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gates', function (Blueprint $table) {
            $table->foreign('camera_id')
                ->references('id')
                ->on('cameras')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gates', function (Blueprint $table) {
            $table->dropForeign(['camera_id']);
        });
    }
};