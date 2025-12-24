<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'is_default')) {
                $table->boolean('is_default')
                    ->default(false)
                    ->after('guard_name');
            }

            if (!Schema::hasColumn('permissions', 'is_assignable')) {
                $table->boolean('is_assignable')
                    ->default(false)
                    ->after('is_default');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'is_default')) {
                $table->dropColumn('is_default');
            }

            if (Schema::hasColumn('permissions', 'is_assignable')) {
                $table->dropColumn('is_assignable');
            }
        });
    }
};
