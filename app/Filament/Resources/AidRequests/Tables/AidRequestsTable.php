<?php

namespace App\Filament\Resources\AidRequests\Tables;

use App\Domains\AidRequests\Actions\DeliverAidRequestAction;
use App\Domains\AidRequests\DTOs\AidDistributionData;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Eligibility\Actions\CalculateAidRequestPriorityAction;
use App\Domains\Eligibility\Actions\DetectAidRequestDuplicateAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class AidRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('beneficiary.user.name')
                    ->label('المستفيد')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('beneficiary.national_id')
                    ->label('رقم الهوية')
                    ->searchable()
                    ->toggleable(),
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
                TextColumn::make('priority_score')
                    ->label('درجة الأولوية')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'danger',
                        $state >= 60 => 'warning',
                        $state >= 40 => 'info',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                TextColumn::make('priority_level')
                    ->label('الأولوية')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PriorityLevel ? $state->label() : $state)
                    ->color(fn ($state) => match ($state) {
                        PriorityLevel::LOW => 'gray',
                        PriorityLevel::MEDIUM => 'info',
                        PriorityLevel::HIGH => 'warning',
                        PriorityLevel::CRITICAL => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('requested_amount')
                    ->label('المبلغ المطلوب')
                    ->sortable()
                    ->money('USD'),
                TextColumn::make('submitted_at')
                    ->label('تاريخ التقديم')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('possible_duplicate')
                    ->label('التكرار')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'احتمال تكرار' : 'لا يوجد تكرار')
                    ->color(fn (bool $state) => $state ? 'warning' : 'gray')
                    ->icon(fn (bool $state) => $state ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(
                        collect(AidRequestStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    ),
                SelectFilter::make('aid_type')
                    ->label('نوع المساعدة')
                    ->options(
                        collect(AidType::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    ),
                SelectFilter::make('priority_level')
                    ->label('مستوى الأولوية')
                    ->options(
                        collect(PriorityLevel::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    ),
                Filter::make('possible_duplicate')
                    ->label('الطلبات المكررة المحتملة')
                    ->query(fn ($query) => $query->where('possible_duplicate', true)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('detectDuplicate')
                    ->label('فحص التكرار')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->action(function (AidRequest $record) {
                        $result = app(DetectAidRequestDuplicateAction::class)->execute($record);

                        if ($result->isDuplicate) {
                            Notification::make()
                                ->title('تم اكتشاف احتمال تكرار')
                                ->body('درجة التشابه: ' . $result->score . '%')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('لم يتم اكتشاف طلب مكرر')
                            ->success()
                            ->send();
                    }),
                Action::make('recalculatePriority')
                    ->label('إعادة حساب الأولوية')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->action(function (AidRequest $record) {
                        $result = app(CalculateAidRequestPriorityAction::class)->execute($record);

                        Notification::make()
                            ->title('تم تحديث أولوية الطلب بنجاح')
                            ->body('الدرجة: ' . $result->score . ' — الأولوية: ' . $result->level->label())
                            ->success()
                            ->send();
                    }),
                Action::make('deliver')
                    ->label('تسجيل التسليم')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn (AidRequest $record) => $record->status === AidRequestStatus::APPROVED
                        && ! $record->distribution()->exists())
                    ->authorize(fn (AidRequest $record) => auth()->user()->can('markDelivered', $record))
                    ->form([
                        TextInput::make('delivered_to_name')
                            ->label('اسم المستلم')
                            ->required(),
                        TextInput::make('delivered_to_national_id')
                            ->label('رقم هوية المستلم'),
                        TextInput::make('delivery_method')
                            ->label('طريقة التسليم'),
                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->minValue(0),
                        DatePicker::make('delivered_at')
                            ->label('تاريخ التسليم')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                        TextInput::make('receipt_number')
                            ->label('رقم الإيصال'),
                        Textarea::make('notes')
                            ->label('ملاحظات'),
                    ])
                    ->action(function (AidRequest $record, array $data) {
                        $dto = AidDistributionData::fromArray($data);

                        app(DeliverAidRequestAction::class)->execute($record, $dto, auth()->user());

                        Notification::make()
                            ->title('تم تسجيل تسليم المساعدة بنجاح')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('detectDuplicates')
                        ->label('فحص التكرار للطلبات المحددة')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $duplicates = 0;

                            foreach ($records as $record) {
                                $result = app(DetectAidRequestDuplicateAction::class)->execute($record);

                                if ($result->isDuplicate) {
                                    $duplicates++;
                                }
                            }

                            Notification::make()
                                ->title('اكتمل الفحص: ' . $duplicates . ' طلب مكرر محتمل من أصل ' . $records->count())
                                ->when($duplicates > 0, fn ($n) => $n->warning(), fn ($n) => $n->success())
                                ->send();
                        }),
                    BulkAction::make('recalculatePriority')
                        ->label('إعادة حساب الأولوية')
                        ->icon('heroicon-o-calculator')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                app(CalculateAidRequestPriorityAction::class)->execute($record);
                            }

                            Notification::make()
                                ->title('تم تحديث أولوية ' . $records->count() . ' طلب بنجاح')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}