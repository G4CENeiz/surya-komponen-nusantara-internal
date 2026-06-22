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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workplace_id')->nullable()->constrained();
            $table->date('date');

            // Clock-in fields
            $table->timestamp('clock_in_at')->nullable();
            $table->decimal('clock_in_lat', 10, 7)->nullable();
            $table->decimal('clock_in_lng', 10, 7)->nullable();
            $table->string('clock_in_ip', 45)->nullable();
            $table->string('clock_in_photo_path')->nullable();
            $table->decimal('clock_in_face_confidence', 5, 4)->nullable();
            $table->boolean('clock_in_within_geofence')->nullable();
            $table->string('clock_in_method')->nullable(); // geofence, manual, face_recognition

            // Clock-out fields
            $table->timestamp('clock_out_at')->nullable();
            $table->decimal('clock_out_lat', 10, 7)->nullable();
            $table->decimal('clock_out_lng', 10, 7)->nullable();
            $table->string('clock_out_ip', 45)->nullable();
            $table->string('clock_out_photo_path')->nullable();
            $table->decimal('clock_out_face_confidence', 5, 4)->nullable();
            $table->boolean('clock_out_within_geofence')->nullable();
            $table->string('clock_out_method')->nullable();

            // HR verification
            $table->string('status')->default('pending_hr');
            $table->text('hr_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();

            // Computed fields
            $table->decimal('worked_hours', 5, 2)->nullable();
            $table->boolean('is_late')->default(false);
            $table->boolean('is_early_leave')->default(false);

            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index('status');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
