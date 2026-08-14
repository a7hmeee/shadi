<?php

namespace App\Filament\Resources\Beneficiaries;

use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Filament\Resources\Beneficiaries\Pages\CreateBeneficiary;
use App\Filament\Resources\Beneficiaries\Pages\EditBeneficiary;
use App\Filament\Resources\Beneficiaries\Pages\ListBeneficiaries;
use App\Filament\Resources\Beneficiaries\Pages\ViewBeneficiary;
use App\Filament\Resources\Beneficiaries\RelationManagers\AidRequestsRelationManager;
use App\Filament\Resources\Beneficiaries\Schemas\BeneficiaryForm;
use App\Filament\Resources\Beneficiaries\Schemas\BeneficiaryInfolist;
use App\Filament\Resources\Beneficiaries\Tables\BeneficiariesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class BeneficiaryResource extends Resource
{
    protected static ?string $model = Beneficiary::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'national_id';

    protected static ?string $navigationLabel = 'المستفيدون';

    protected static ?string $modelLabel = 'مستفيد';

    protected static ?string $pluralModelLabel = 'المستفيدون';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المساعدات';

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
        return [
            AidRequestsRelationManager::class,
        ];
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