<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dams_notification_settings', function (Blueprint $table) {
            $table->string('id')->unique()->primary();
            $table->string('dam_id');
            $table->string('notification_setting_id');
            $table->foreign('dam_id')->references('id')->on('dams')->onDelete('cascade');
            $table->foreign('notification_setting_id')->references('id')->on('notifications_settings')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dam_notification_settings');
    }
};