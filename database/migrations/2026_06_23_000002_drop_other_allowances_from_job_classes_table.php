<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_classes', function (Blueprint $table) {
            $table->dropColumn('other_allowances');
        });
    }

    public function down(): void
    {
        Schema::table('job_classes', function (Blueprint $table) {
            $table->decimal('other_allowances', 15, 2)->default(0);
        });
    }
};
