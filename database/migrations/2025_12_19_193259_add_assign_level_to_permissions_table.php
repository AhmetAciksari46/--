<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'assign_level')) {
                $table->string('assign_level')
                    ->default('system')
                    ->after('is_assignable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'assign_level')) {
                $table->dropColumn('assign_level');
            }
        });
    }
};
