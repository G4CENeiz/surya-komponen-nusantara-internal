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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->enum('type', ['leave', 'sick', 'overtime']);
            // Leave/Sick fields
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            // Sick-specific fields
            $table->string('doctor_letter_path')->nullable();
            $table->text('sick_notes')->nullable();
            // Overtime fields
            $table->date('overtime_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('overtime_notes')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            // Approval fields
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index(['type', 'status']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
