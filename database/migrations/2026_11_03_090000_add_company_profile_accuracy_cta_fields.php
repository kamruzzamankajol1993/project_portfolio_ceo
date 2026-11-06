<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profile_details', function (Blueprint $table): void {
            $table->boolean('accuracy_cta_enabled')->default(true);
            $table->string('accuracy_cta_title', 190)->nullable();
            $table->text('accuracy_cta_description')->nullable();
            $table->string('accuracy_cta_button_label', 120)->nullable();
            $table->string('accuracy_cta_button_url', 500)->nullable();
            $table->string('accuracy_cta_secondary_label', 120)->nullable();
            $table->string('accuracy_cta_secondary_url', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('company_profile_details', function (Blueprint $table): void {
            $table->dropColumn([
                'accuracy_cta_enabled',
                'accuracy_cta_title',
                'accuracy_cta_description',
                'accuracy_cta_button_label',
                'accuracy_cta_button_url',
                'accuracy_cta_secondary_label',
                'accuracy_cta_secondary_url',
            ]);
        });
    }
};
