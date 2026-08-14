<?php

namespace App\Filament\Resources\AidRequests;

use App\Domains\AidRequests\Models\AidRequest;
use App\Filament\Resources\AidRequests\Pages\CreateAidRequest;
use App\Filament\Resources\AidRequests\Pages\EditAidRequest;
use App\Filament\Resources\AidRequests\Pages\ListAidRequests;
use App\Filament\Resources\AidRequests\Pages\ViewAidRequest;
use App\Filament\Resources\AidRequests\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\AidRequests\Schemas\AidRequestForm;
use App\Filament\Resources\AidRequests\Schemas\AidRequestInfolist;
use App\Filament\Resources\AidRequests\Tables\AidRequestsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AidRequestResource extends Resource
{
    protected static ?string $model = AidRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'request_number';

    protected static ?string $navigationLabel = 'طلبات المساعدة';

    protected static ?string $modelLabel = 'طلب مساعدة';

    protected static ?string $pluralModelLabel = 'طلبات المساعدة';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المساعدات';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return AidRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AidRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AidRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAidRequests::route('/'),
            'create' => CreateAidRequest::route('/create'),
            'view' => ViewAidRequest::route('/{record}'),
            'edit' => EditAidRequest::route('/{record}/edit'),
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
