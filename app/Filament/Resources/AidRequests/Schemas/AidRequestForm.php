<?php

namespace App\Filament\Resources\AidRequests\Schemas;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\Beneficiaries\Models\Beneficiary;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AidRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المستفيد')
                    ->schema([
                        Select::make('beneficiary_id')
                            ->label('المستفيد')
                            ->relationship('beneficiary', 'national_id')
                            ->getOptionLabelFromRecordUsing(
                                fn (Beneficiary $record) => $record->user->name . ' — ' . $record->national_id
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                    ]),

                Section::make('تفاصيل الطلب')
                    ->schema([
                        TextInput::make('request_number')
                            ->label('رقم الطلب')
                            ->hidden()
                            ->dehydratedWhenHidden()
                            ->unique('aid_requests', 'request_number', ignoreRecord: true),
                        Select::make('aid_type')
                            ->label('نوع المساعدة')
                            ->required()
                            ->options(
                                collect(AidType::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray()
                            ),
                        TextInput::make('title')
                            ->label('العنوان')
                            ->required(),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->required(),
                        TextInput::make('requested_amount')
                            ->label('المبلغ المطلوب')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('ر.س')
                            ->nullable(),
                        Textarea::make('beneficiary_notes')
                            ->label('ملاحظات المستفيد')
                            ->nullable(),
                    ]),

                Section::make('حالة الطلب')
                    ->schema([
                        Select::make('status')
                            ->label('الحالة')
                            ->disabled()
                            ->options(
                                collect(AidRequestStatus::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray()
                            ),
                        Select::make('priority_level')
                            ->label('مستوى الأولوية')
                            ->required()
                            ->default(PriorityLevel::MEDIUM->value)
                            ->options(
                                collect(PriorityLevel::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray()
                            ),
                    ]),

                Section::make('المراجعة')
                    ->schema([
                        Textarea::make('reviewer_notes')
                            ->label('ملاحظات المراجع')
                            ->nullable()
                            ->disabled(),
                    ]),

                Section::make('القرار')
                    ->schema([
                        TextInput::make('approved_amount')
                            ->label('المبلغ الموافق عليه')
                            ->numeric()
                            ->prefix('ر.س')
                            ->visible(fn ($get) => $get('status') === AidRequestStatus::APPROVED->value)
                            ->disabled(),
                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->visible(fn ($get) => $get('status') === AidRequestStatus::REJECTED->value)
                            ->disabled(),
                    ]),
            ]);
    }
}