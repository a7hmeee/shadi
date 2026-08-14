<?php

namespace Tests\Feature\AidRequests;

use App\Domains\AidRequests\Actions\DeleteAidRequestAttachmentAction;
use App\Domains\AidRequests\Actions\UnverifyAidRequestAttachmentAction;
use App\Domains\AidRequests\Actions\UploadAidRequestAttachmentAction;
use App\Domains\AidRequests\Actions\VerifyAidRequestAttachmentAction;
use App\Domains\AidRequests\Enums\DocumentType;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function superAdmin(): User
    {
        return User::where('email', 'admin@aid.test')->first();
    }

    public function test_upload_stores_file_and_creates_record(): void
    {
        Storage::fake('public');

        $request = AidRequest::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $attachment = app(UploadAidRequestAttachmentAction::class)->execute(
            $request,
            $file,
            DocumentType::IDENTITY,
            $this->superAdmin(),
            'هوية المستفيد',
        );

        Storage::disk('public')->assertExists($attachment->file_path);

        $this->assertDatabaseHas('aid_request_attachments', [
            'id' => $attachment->id,
            'aid_request_id' => $request->id,
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'document_type' => DocumentType::IDENTITY->value,
            'uploaded_by' => $this->superAdmin()->id,
        ]);

        $this->assertSame('application/pdf', $attachment->mime_type);
        $this->assertSame(100 * 1024, $attachment->file_size);
        $this->assertStringContainsString('aid-requests/' . $request->id . '/attachments', $attachment->file_path);
    }

    public function test_upload_rejects_disallowed_file_types(): void
    {
        Storage::fake('public');

        $request = AidRequest::factory()->create();

        foreach (['test.php', 'test.exe'] as $name) {
            try {
                app(UploadAidRequestAttachmentAction::class)->execute(
                    $request,
                    UploadedFile::fake()->create($name, 10),
                    DocumentType::OTHER,
                    $this->superAdmin(),
                );

                $this->fail("File {$name} should have been rejected.");
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }

        $this->assertDatabaseCount('aid_request_attachments', 0);
        Storage::disk('public')->assertDirectoryEmpty('aid-requests/' . $request->id);
    }

    public function test_upload_rejects_file_larger_than_5mb(): void
    {
        Storage::fake('public');

        $request = AidRequest::factory()->create();

        $this->expectException(ValidationException::class);

        app(UploadAidRequestAttachmentAction::class)->execute(
            $request,
            UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf'),
            DocumentType::OTHER,
            $this->superAdmin(),
        );
    }

    public function test_verify_sets_verification_fields(): void
    {
        $attachment = AidRequestAttachment::factory()->create();

        app(VerifyAidRequestAttachmentAction::class)->execute($attachment, $this->superAdmin());

        $attachment->refresh();

        $this->assertTrue($attachment->is_verified);
        $this->assertEquals($this->superAdmin()->id, $attachment->verified_by);
        $this->assertNotNull($attachment->verified_at);
    }

    public function test_unverify_clears_verification_fields(): void
    {
        $attachment = AidRequestAttachment::factory()->verified()->create();

        app(UnverifyAidRequestAttachmentAction::class)->execute($attachment, $this->superAdmin());

        $attachment->refresh();

        $this->assertFalse($attachment->is_verified);
        $this->assertNull($attachment->verified_by);
        $this->assertNull($attachment->verified_at);
    }

    public function test_delete_removes_file_and_record(): void
    {
        Storage::fake('public');

        $request = AidRequest::factory()->create();
        $attachment = app(UploadAidRequestAttachmentAction::class)->execute(
            $request,
            UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
            DocumentType::OTHER,
            $this->superAdmin(),
        );

        Storage::disk('public')->assertExists($attachment->file_path);

        app(DeleteAidRequestAttachmentAction::class)->execute($attachment, $this->superAdmin());

        Storage::disk('public')->assertMissing($attachment->file_path);
        $this->assertSoftDeleted('aid_request_attachments', ['id' => $attachment->id]);
    }

    public function test_employee_cannot_delete_attachment(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole(\App\Domains\Identity\Enums\UserRole::EMPLOYEE->value);

        $attachment = AidRequestAttachment::factory()->create();

        $this->assertFalse($employee->can('delete', $attachment));
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        app(DeleteAidRequestAttachmentAction::class)->execute($attachment, $employee);
    }
}
