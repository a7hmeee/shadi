<?php

namespace Database\Factories;

use App\Domains\AidRequests\Enums\DocumentType;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AidRequestAttachment>
 */
class AidRequestAttachmentFactory extends Factory
{
    protected $model = AidRequestAttachment::class;

    public function definition(): array
    {
        return [
            'aid_request_id' => AidRequest::factory(),
            'uploaded_by' => User::factory(),
            'original_name' => fake()->words(3, true) . '.pdf',
            'file_name' => fake()->uuid() . '.pdf',
            'file_path' => 'aid-requests/1/attachments/' . fake()->uuid() . '.pdf',
            'disk' => 'public',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 2 * 1024 * 1024),
            'document_type' => fake()->randomElement(DocumentType::cases())->value,
            'description' => fake()->optional()->sentence(),
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'verified_by' => User::factory(),
            'verified_at' => now(),
        ]);
    }
}
