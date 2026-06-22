<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('shift_name')->nullable()->after('status');
            $table->time('shift_start_time')->nullable()->after('shift_name');
            $table->time('shift_end_time')->nullable()->after('shift_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['shift_name', 'shift_start_time', 'shift_end_time']);
        });
    }
};
