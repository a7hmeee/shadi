<?php

namespace App\Filament\Resources\Beneficiaries\Schemas;

use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeneficiaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->description('البيانات الشخصية والهوية')
                    ->schema([
                        Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->getOptionLabelFromRecordUsing(fn (User $user) => $user->name . ' (' . $user->email . ')')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        TextInput::make('national_id')
                            ->label('الرقم الوطني')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->label('الهاتف')
                            ->required(),

                        TextInput::make('alternative_phone')
                            ->label('الهاتف البديل')
                            ->nullable(),

                        DatePicker::make('date_of_birth')
                            ->label('تاريخ الميلاد')
                            ->nullable(),

                        TextInput::make('gender')
                            ->label('النوع')
                            ->nullable(),

                        Select::make('social_status')
                            ->label('الحالة الاجتماعية')
                            ->required()
                            ->options(
                                collect(SocialStatus::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray()
                            ),
                    ]),

                Section::make('بيانات الأسرة')
                    ->description('تركيبة العائلة والدخل')
                    ->schema([
                        TextInput::make('family_members_count')
                            ->label('عدد أفراد الأسرة')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->live(),

                        TextInput::make('children_count')
                            ->label('عدد الأطفال')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn ($get) => $get('family_members_count')),

                        TextInput::make('dependents_count')
                            ->label('عدد الرهين')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn ($get) => $get('family_members_count')),

                        TextInput::make('monthly_income')
                            ->label('الدخل الشهري')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('ر.س'),
                    ]),

                Section::make('الحالة الصحية والاجتماعية')
                    ->description('الإعاقة والأمراض المزمنة والإسكان')
                    ->schema([
                        Toggle::make('has_disability')
                            ->label('لديه إعاقة؟')
                            ->live(),

                        Textarea::make('disability_details')
                            ->label('تفاصيل الإعاقة')
                            ->visible(fn ($get) => $get('has_disability'))
                            ->required(fn ($get) => $get('has_disability')),

                        Toggle::make('has_chronic_disease')
                            ->label('مرض مزمن؟')
                            ->live(),

                        Textarea::make('chronic_disease_details')
                            ->label('تفاصيل المرض المزمن')
                            ->visible(fn ($get) => $get('has_chronic_disease')),

                        Select::make('housing_status')
                            ->label('حالة المسكن')
                            ->options(
                                collect(HousingStatus::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray()
                            )
                            ->nullable(),

                        Select::make('need_level')
                            ->label('مستوى الحاجة')
                            ->required()
                            ->options(
                                collect(NeedLevel::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray()
                            ),
                    ]),

                Section::make('العنوان')
                    ->schema([
                        TextInput::make('governorate')
                            ->label('المحافظة')
                            ->nullable(),

                        TextInput::make('city')
                            ->label('المدينة')
                            ->nullable(),

                        Textarea::make('address')
                            ->label('العنوان')
                            ->nullable(),
                    ]),

                Section::make('بيانات إضافية')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ]),
            ]);
    }
}