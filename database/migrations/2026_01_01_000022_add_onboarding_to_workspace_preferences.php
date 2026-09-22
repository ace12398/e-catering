<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workspace_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('workspace_preferences', 'onboarding_complete')) {
                $table->boolean('onboarding_complete')->default(false)->after('auto_save');
            }
            if (!Schema::hasColumn('workspace_preferences', 'density')) {
                $table->string('density', 20)->default('comfortable')->after('onboarding_complete');
            }
            if (!Schema::hasColumn('workspace_preferences', 'goal')) {
                $table->string('goal', 20)->nullable()->after('density');
            }
        });
    }
    public function down(): void
    {
        Schema::table('workspace_preferences', function (Blueprint $table) {
            $table->dropColumn(['onboarding_complete', 'density', 'goal']);
        });
    }
};
