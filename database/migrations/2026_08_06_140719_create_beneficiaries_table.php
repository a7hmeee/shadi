<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->unique()
                ->cascadeOnDelete();
            $table->string('national_id')
                ->unique();
            $table->string('phone');
            $table->string('alternative_phone')
                ->nullable();
            $table->date('date_of_birth')
                ->nullable();
            $table->string('gender')
                ->nullable();
            $table->string('social_status');
            $table->unsignedSmallInteger('family_members_count')
                ->default(1);
            $table->unsignedSmallInteger('children_count')
                ->default(0);
            $table->unsignedSmallInteger('dependents_count')
                ->default(0);
            $table->decimal('monthly_income', 12, 2)
                ->default(0);
            $table->boolean('has_disability')
                ->default(false);
            $table->text('disability_details')
                ->nullable();
            $table->boolean('has_chronic_disease')
                ->default(false);
            $table->text('chronic_disease_details')
                ->nullable();
            $table->string('housing_status')
                ->nullable();
            $table->string('need_level')
                ->default('medium');
            $table->string('governorate')
                ->nullable();
            $table->string('city')
                ->nullable();
            $table->text('address')
                ->nullable();
            $table->text('notes')
                ->nullable();
            $table->boolean('is_active')
                ->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['social_status']);
            $table->index(['need_level']);
            $table->index(['governorate']);
            $table->index(['city']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};