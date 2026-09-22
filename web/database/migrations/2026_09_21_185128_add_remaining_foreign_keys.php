<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // SpotState → ParkingSession / Reservation
        Schema::table('spot_states', function (Blueprint $table) {
            $table->foreign('session_id')
                ->references('id')
                ->on('parking_sessions')
                ->nullOnDelete();

            $table->foreign('reservation_id')
                ->references('id')
                ->on('reservations')
                ->nullOnDelete();
        });

        // ParkingSession → RatePlan / Permit / MediaAsset
        Schema::table('parking_sessions', function (Blueprint $table) {
            $table->foreign('rate_plan_id')
                ->references('id')
                ->on('rate_plans')
                ->nullOnDelete();

            $table->foreign('permit_id')
                ->references('id')
                ->on('permits')
                ->nullOnDelete();

            $table->foreign('entry_media_id')
                ->references('id')
                ->on('media_assets')
                ->nullOnDelete();

            $table->foreign('exit_media_id')
                ->references('id')
                ->on('media_assets')
                ->nullOnDelete();
        });

        // PlateRead → MediaAsset
        Schema::table('plate_reads', function (Blueprint $table) {
            $table->foreign('media_id')
                ->references('id')
                ->on('media_assets')
                ->nullOnDelete();
        });

        // Payment → Shift
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('shift_id')
                ->references('id')
                ->on('shifts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        Schema::table('plate_reads', function (Blueprint $table) {
            $table->dropForeign(['media_id']);
        });

        Schema::table('parking_sessions', function (Blueprint $table) {
            $table->dropForeign(['rate_plan_id']);
            $table->dropForeign(['permit_id']);
            $table->dropForeign(['entry_media_id']);
            $table->dropForeign(['exit_media_id']);
        });

        Schema::table('spot_states', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropForeign(['reservation_id']);
        });
    }
};