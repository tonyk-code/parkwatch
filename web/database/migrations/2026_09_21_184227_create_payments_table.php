<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedInteger('permit_id')->nullable();
            $table->unsignedInteger('site_id');

            $table->unsignedInteger('shift_id')->nullable();
            $table->unsignedInteger('collected_by')->nullable();

            $table->bigInteger('amount_minor');
            $table->char('currency', 3);

            $table->string('method', 20);
            $table->string('status', 15)->default('pending');

            $table->string('gateway_reference', 100)->nullable()->unique();
            $table->json('gateway_payload')->nullable();

            $table->dateTime('paid_at')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->string('refund_reason', 255)->nullable();

            $table->foreign('session_id')
                ->references('id')
                ->on('parking_sessions')
                ->nullOnDelete();

            $table->foreign('permit_id')
                ->references('id')
                ->on('permits')
                ->nullOnDelete();

            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();

            // shift_id gets its foreign key after shifts exists.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};