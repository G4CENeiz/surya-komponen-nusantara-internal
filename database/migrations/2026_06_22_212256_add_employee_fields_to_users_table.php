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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('name')->unique();
            $table->string('department')->nullable()->after('nik');
            $table->foreignId('job_class_id')->nullable()->after('department')->constrained()->nullOnDelete();
            $table->foreignId('office_id')->nullable()->after('job_class_id')->constrained()->nullOnDelete();
            $table->string('employment_status')->default('active')->after('office_id'); // active, resigned, on_leave
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['job_class_id', 'office_id']);
            $table->dropColumn(['nik', 'department', 'job_class_id', 'office_id', 'employment_status']);
        });
    }
};
