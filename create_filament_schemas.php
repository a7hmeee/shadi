<?php

// Helper function to write PHP files
function writeFile(string $path, string $content): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($path, $content);
}

// 1. Fix BeneficiaryResource
writeFile('C:\\Users\\ahmed\\shadi\\app\\Filament\\Resources\\Beneficiaries\\BeneficiaryResource.php', <<<'PHP'
<?php

namespace App\Filament\Resources\Beneficiaries;

use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Filament\Resources\Beneficiaries\Pages\CreateBeneficiary;
use App\Filament\Resources\Beneficiaries\Pages\EditBeneficiary;
use App\Filament\Resources\Beneficiaries\Pages\ListBeneficiaries;
use App\Filament\Resources\Beneficiaries\Pages\ViewBeneficiary;
use App\Filament\Resources\Beneficiaries\Schemas\BeneficiaryForm;
use App\Filament\Resources\Beneficiaries\Schemas\BeneficiaryInfolist;
use App\Filament\Resources\Beneficiaries\Tables\BeneficiariesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BeneficiaryResource extends Resource
{
    protected static ?string $model = Beneficiary::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'national_id';

    protected static ?string $navigationLabel = 'المستفيدون';

    protected static ?string $modelLabel = 'مستفيد';

    protected static ?string $pluralModelLabel = 'المستفيدون';

    protected static ?string $navigationGroup = 'إدارة المساعدات';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return BeneficiaryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BeneficiaryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeneficiariesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBeneficiaries::route('/'),
            'create' => CreateBeneficiary::route('/create'),
            'view' => ViewBeneficiary::route('/{record}'),
            'edit' => EditBeneficiary::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
PHP);
echo "Written: BeneficiaryResource.php\n";

// 2. BeneficiaryForm Schema
writeFile('C:\\Users\\ahmed\\shadi\\app\\Filament\\Resources\\Beneficiaries\\Schemas\\BeneficiaryForm.php', <<<'PHP'
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
use Filament\Schemas\Components\Split;
use Filament\Schemas\Schema;

class BeneficiaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->schema([
                        Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->options(
                                User::whereDoesntHave('beneficiary')
                                    ->pluck('name', 'id')
                            )
                            ->getOptionLabelsUsing(function (User $user) {
                                return $user->name . ' (' . $user->email . ')';
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

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
                            ->minValue(0),
                    ]),

                Section::make('الحالة الصحية والاجتماعية')
                    ->schema([
                        Toggle::make('has_disability')
                            ->label('إعاقة؟')
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
PHP);
echo "Written: BeneficiaryForm.php\n";

// 3. BeneficiaryInfolist Schema
writeFile('C:\\Users\\ahmed\\shadi\\app\\Filament\\Resources\\Beneficiaries\\Schemas\\BeneficiaryInfolist.php', <<<'PHP'
<?php

namespace App\Filament\Resources\Beneficiaries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeneficiaryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('اسم المستخدم'),
                        TextEntry::make('user.email')
                            ->label('البريد الإلكتروني'),
                        TextEntry::make('national_id')
                            ->label('الرقم الوطني'),
                        TextEntry::make('phone')
                            ->label('الهاتف'),
                        TextEntry::make('alternative_phone')
                            ->label('الهاتف البديل')
                            ->badge(),
                        TextEntry::make('date_of_birth')
                            ->label('تاريخ الميلاد')
                            ->date(),
                        TextEntry::make('gender')
                            ->label('النوع'),
                        TextEntry::make('social_status')
                            ->label('الحالة الاجتماعية')
                            ->badge(),
                    ]),

                Section::make('بيانات الأسرة')
                    ->schema([
                        TextEntry::make('family_members_count')
                            ->label('عدد أفراد الأسرة'),
                        TextEntry::make('children_count')
                            ->label('عدد الأطفال'),
                        TextEntry::make('dependents_count')
                            ->label('عدد الرهين'),
                        TextEntry::make('monthly_income')
                            ->label('الدخل الشهري')
                            ->money('USD'),
                    ]),

                Section::make('الحالة الصحية والاجتماعية')
                    ->schema([
                        TextEntry::make('has_disability')
                            ->label('إعاقة؟')
                            ->bool(),
                        TextEntry::make('disability_details')
                            ->label('تفاصيل الإعاقة'),
                        TextEntry::make('has_chronic_disease')
                            ->label('مرض مزمن؟')
                            ->bool(),
                        TextEntry::make('chronic_disease_details')
                            ->label('تفاصيل المرض المزمن'),
                        TextEntry::make('housing_status')
                            ->label('حالة المسكن')
                            ->badge(),
                        TextEntry::make('need_level')
                            ->label('مستوى الحاجة')
                            ->badge(),
                    ]),

                Section::make('العنوان')
                    ->schema([
                        TextEntry::make('governorate')
                            ->label('المحافظة'),
                        TextEntry::make('city')
                            ->label('المدينة'),
                        TextEntry::make('address')
                            ->label('العنوان'),
                    ]),

                Section::make('بيانات إضافية')
                    ->schema([
                        TextEntry::make('is_active')
                            ->label('نشط')
                            ->badge(),
                        TextEntry::make('notes')
                            ->label('ملاحظات'),
                    ]),

                Section::make('معلومات النظام')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ التسجيل')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime(),
                    ]),

                Section::make('طلبات المساعدة')
                    ->schema([
                        TextEntry::make('aid_requests_count')
                            ->label('عدد طلبات المساعدة')
                            ->badge(),
                    ]),
            ]);
    }
}
PHP);
echo "Written: BeneficiaryInfolist.php\n";

echo "\nAll Filament schema files written successfully.\n";
