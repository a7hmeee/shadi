<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aid_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')
                ->constrained('beneficiaries')
                ->cascadeOnDelete();
            $table->string('request_number')
                ->unique();
            $table->string('aid_type');
            $table->string('title')
                ->nullable();
            $table->text('description');
            $table->string('status')
                ->default('submitted');
            $table->string('priority_level')
                ->default('medium');
            $table->unsignedTinyInteger('priority_score')
                ->nullable();
            $table->decimal('requested_amount', 12, 2)
                ->nullable();
            $table->decimal('approved_amount', 12, 2)
                ->nullable();
            $table->timestamp('submitted_at')
                ->nullable();
            $table->timestamp('reviewed_at')
                ->nullable();
            $table->timestamp('approved_at')
                ->nullable();
            $table->timestamp('rejected_at')
                ->nullable();
            $table->timestamp('delivered_at')
                ->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('decision_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('rejection_reason')
                ->nullable();
            $table->text('reviewer_notes')
                ->nullable();
            $table->text('beneficiary_notes')
                ->nullable();
            $table->boolean('possible_duplicate')
                ->default(false);
            $table->foreignId('duplicate_of_request_id')
                ->nullable()
                ->constrained('aid_requests')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['beneficiary_id']);
            $table->index(['request_number']);
            $table->index(['aid_type']);
            $table->index(['status']);
            $table->index(['priority_level']);
            $table->index(['possible_duplicate']);
            $table->index(['submitted_at']);
            $table->index(['reviewed_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_requests');
    }
};