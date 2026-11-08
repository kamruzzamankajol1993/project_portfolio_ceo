<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('personal_profile_details')) {
            Schema::table('personal_profile_details', function (Blueprint $table): void {
                foreach ([
                    'career_timeline',
                    'affiliations',
                    'education_items',
                    'achievement_items',
                    'media_items',
                ] as $column) {
                    if (!Schema::hasColumn('personal_profile_details', $column)) {
                        $table->json($column)->nullable();
                    }
                }
            });
        }

        if (Schema::hasTable('company_profile_details')) {
            Schema::table('company_profile_details', function (Blueprint $table): void {
                if (!Schema::hasColumn('company_profile_details', 'values_culture')) {
                    $table->longText('values_culture')->nullable();
                }

                foreach ([
                    'highlights',
                    'leadership_items',
                    'service_items',
                    'milestone_items',
                    'media_items',
                ] as $column) {
                    if (!Schema::hasColumn('company_profile_details', $column)) {
                        $table->json($column)->nullable();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('company_profile_details')) {
            Schema::table('company_profile_details', function (Blueprint $table): void {
                $columns = ['values_culture', 'highlights', 'leadership_items', 'service_items', 'milestone_items', 'media_items'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('company_profile_details', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('personal_profile_details')) {
            Schema::table('personal_profile_details', function (Blueprint $table): void {
                foreach (['career_timeline', 'affiliations', 'education_items', 'achievement_items', 'media_items'] as $column) {
                    if (Schema::hasColumn('personal_profile_details', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
