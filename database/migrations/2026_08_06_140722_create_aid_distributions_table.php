<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aid_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aid_request_id')
                ->constrained()
                ->unique()
                ->cascadeOnDelete();
            $table->foreignId('distributed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('delivered_to_name');
            $table->string('delivered_to_national_id')
                ->nullable();
            $table->string('delivery_method')
                ->nullable();
            $table->decimal('amount', 12, 2)
                ->nullable();
            $table->timestamp('delivered_at');
            $table->string('receipt_number')
                ->nullable()
                ->unique();
            $table->text('notes')
                ->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_distributions');
    }
};