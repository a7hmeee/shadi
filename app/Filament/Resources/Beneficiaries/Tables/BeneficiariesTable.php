<?php

namespace App\Filament\Resources\Beneficiaries\Tables;

use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BeneficiariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('national_id')
                    ->label('الرقم الوطني')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label('اسم المستخدم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),

                TextColumn::make('family_members_count')
                    ->label('أفراد الأسرة')
                    ->sortable(),

                TextColumn::make('monthly_income')
                    ->label('الدخل الشهري')
                    ->sortable()
                    ->money('USD'),

                TextColumn::make('social_status')
                    ->label('الحالة الاجتماعية')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof SocialStatus ? $state->label() : $state)
                    ->color(fn ($state) => match ($state) {
                        SocialStatus::SINGLE => 'info',
                        SocialStatus::MARRIED => 'success',
                        SocialStatus::DIVORCED => 'warning',
                        SocialStatus::WIDOWED => 'gray',
                        SocialStatus::SEPARATED => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),

                TextColumn::make('need_level')
                    ->label('مستوى الحاجة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof NeedLevel ? $state->label() : $state)
                    ->color(fn ($state) => match ($state) {
                        NeedLevel::LOW => 'gray',
                        NeedLevel::MEDIUM => 'warning',
                        NeedLevel::HIGH => 'danger',
                        NeedLevel::CRITICAL => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('city')
                    ->label('المدينة')
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('نشط'),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('need_level')
                    ->label('مستوى الحاجة')
                    ->options(
                        collect(NeedLevel::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    ),

                SelectFilter::make('social_status')
                    ->label('الحالة الاجتماعية')
                    ->options(
                        collect(SocialStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    ),

                SelectFilter::make('housing_status')
                    ->label('حالة المسكن')
                    ->options(
                        collect(HousingStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    ),

                SelectFilter::make('is_active')
                    ->label('النشاط')
                    ->options([
                        '1' => 'نشط',
                        '0' => 'غير نشط',
                    ]),

                SelectFilter::make('governorate')
                    ->label('المحافظة')
                    ->multiple()
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}