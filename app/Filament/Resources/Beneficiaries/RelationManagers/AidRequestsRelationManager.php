<?php

namespace App\Filament\Resources\Beneficiaries\RelationManagers;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Filament\Resources\AidRequests\AidRequestResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AidRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'aidRequests';

    protected static ?string $title = 'طلبات المساعدة';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('aid_type')
                    ->label('نوع المساعدة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof AidType ? $state->label() : $state),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof AidRequestStatus ? $state->label() : $state)
                    ->color(fn ($state) => match ($state) {
                        AidRequestStatus::DRAFT => 'gray',
                        AidRequestStatus::SUBMITTED => 'info',
                        AidRequestStatus::UNDER_REVIEW => 'warning',
                        AidRequestStatus::NEEDS_INFORMATION => 'warning',
                        AidRequestStatus::APPROVED => 'success',
                        AidRequestStatus::REJECTED => 'danger',
                        AidRequestStatus::DELIVERED => 'success',
                        AidRequestStatus::CANCELLED => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('priority_level')
                    ->label('الأولوية')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PriorityLevel ? $state->label() : $state),
                TextColumn::make('submitted_at')
                    ->label('تاريخ التقديم')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('عرض الطلب')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => AidRequestResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);
    }
}
