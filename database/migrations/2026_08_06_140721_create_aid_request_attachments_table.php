<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aid_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aid_request_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('original_name');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('disk')
                ->default('public');
            $table->string('mime_type')
                ->nullable();
            $table->unsignedBigInteger('file_size')
                ->nullable();
            $table->string('document_type')
                ->nullable();
            $table->string('description')
                ->nullable();
            $table->boolean('is_verified')
                ->default(false);
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('verified_at')
                ->nullable();
            $table->timestamps();

            $table->index(['aid_request_id']);
            $table->index(['document_type']);
            $table->index(['is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_request_attachments');
    }
};