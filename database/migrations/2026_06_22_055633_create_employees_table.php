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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nik')->unique();
            $table->string('full_name');
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('office_email')->nullable();
            $table->foreignId('department_id')->constrained();
            $table->foreignId('job_class_id')->constrained('job_classes');
            $table->foreignId('workplace_id')->constrained('workplaces');
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'sick'])->default('active');
            $table->string('face_photo_path')->nullable();
            $table->decimal('base_salary', 15, 2)->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
