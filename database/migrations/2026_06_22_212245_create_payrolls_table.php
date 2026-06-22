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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_salary', 12, 2);
            $table->decimal('allowance', 12, 2)->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('bpjs_health', 12, 2)->default(0);
            $table->decimal('bpjs_employment', 12, 2)->default(0);
            $table->decimal('pph21', 12, 2)->default(0);
            $table->decimal('tardiness_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('take_home_pay', 12, 2);
            $table->string('status')->default('draft'); // draft, finalized
            $table->timestamps();

            $table->unique(['user_id', 'payroll_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
