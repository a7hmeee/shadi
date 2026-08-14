<?php

namespace App\Filament\Resources\AidRequests\RelationManagers;

use App\Domains\AidRequests\Actions\DeleteAidRequestAttachmentAction;
use App\Domains\AidRequests\Actions\UnverifyAidRequestAttachmentAction;
use App\Domains\AidRequests\Actions\UploadAidRequestAttachmentAction;
use App\Domains\AidRequests\Actions\VerifyAidRequestAttachmentAction;
use App\Domains\AidRequests\Enums\DocumentType;
use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Domains\AidRequests\Rules\AttachmentValidationRules;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'المرفقات';

    protected static ?string $modelLabel = 'مرفق';

    protected static ?string $pluralModelLabel = 'المرفقات';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_type')
                    ->label('نوع المستند')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof DocumentType ? $state->label() : $state),
                TextColumn::make('original_name')
                    ->label('اسم الملف')
                    ->limit(35)
                    ->copyable(),
                TextColumn::make('mime_type')
                    ->label('نوع الملف')
                    ->toggleable(),
                TextColumn::make('file_size')
                    ->label('الحجم')
                    ->formatStateUsing(
                        fn ($state) => $state >= 1048576
                            ? number_format($state / 1048576, 2) . ' MB'
                            : number_format($state / 1024, 1) . ' KB'
                    ),
                IconColumn::make('is_verified')
                    ->label('موثق')
                    ->boolean(),
                TextColumn::make('verifier.name')
                    ->label('الموثّق')
                    ->placeholder('—'),
                TextColumn::make('verified_at')
                    ->label('وقت التوثيق')
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('تاريخ الرفع')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('تنزيل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Model $record) => Storage::disk($record->disk)->url($record->file_path))
                    ->openUrlInNewTab(),

                Action::make('verify')
                    ->label('توثيق المرفق')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (AidRequestAttachment $record) => ! $record->is_verified)
                    ->authorize(fn (AidRequestAttachment $record) => auth()->user()->can('verify', $record))
                    ->action(function (AidRequestAttachment $record) {
                        app(VerifyAidRequestAttachmentAction::class)->execute($record, auth()->user());
                        Notification::make()->title('تم توثيق المرفق')->success()->send();
                    }),

                Action::make('unverify')
                    ->label('إلغاء التوثيق')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (AidRequestAttachment $record) => $record->is_verified)
                    ->authorize(fn (AidRequestAttachment $record) => auth()->user()->can('verify', $record))
                    ->action(function (AidRequestAttachment $record) {
                        app(UnverifyAidRequestAttachmentAction::class)->execute($record, auth()->user());
                        Notification::make()->title('تم إلغاء توثيق المرفق')->success()->send();
                    }),

                Action::make('delete')
                    ->label('حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->authorize(fn (AidRequestAttachment $record) => auth()->user()->can('delete', $record))
                    ->action(function (AidRequestAttachment $record) {
                        app(DeleteAidRequestAttachmentAction::class)->execute($record, auth()->user());
                        Notification::make()->title('تم حذف المرفق')->success()->send();
                    }),
            ])
            ->headerActions([
                Action::make('upload')
                    ->label('رفع مرفق')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->authorize(fn () => auth()->user()->can('create', AidRequestAttachment::class))
                    ->form([
                        Select::make('document_type')
                            ->label('نوع المستند')
                            ->required()
                            ->options(
                                collect(DocumentType::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray()
                            ),
                        FileUpload::make('file')
                            ->label('الملف')
                            ->required()
                            ->disk('public')
                            ->directory('livewire-tmp')
                            ->storeFileNamesIn('original_name')
                            ->rules(AttachmentValidationRules::fileRules()),
                        Textarea::make('description')
                            ->label('وصف المرفق')
                            ->nullable(),
                    ])
                    ->action(function (array $data) {
                        $disk = Storage::disk('public');
                        $storedPath = $data['file'][0];

                        $file = new UploadedFile(
                            $disk->path($storedPath),
                            $data['original_name'] ?? basename($storedPath),
                            $disk->mimeType($storedPath) ?: null,
                            null,
                            true,
                        );

                        try {
                            app(UploadAidRequestAttachmentAction::class)->execute(
                                $this->getOwnerRecord(),
                                $file,
                                DocumentType::from($data['document_type']),
                                auth()->user(),
                                $data['description'] ?? null,
                            );
                        } finally {
                            $disk->delete($storedPath);
                        }

                        Notification::make()->title('تم رفع المرفق بنجاح')->success()->send();
                    }),
            ]);
    }
}
