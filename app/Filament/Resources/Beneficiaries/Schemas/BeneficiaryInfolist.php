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
                            ->label('الرقم الوطني')
                            ->badge(),
                        TextEntry::make('phone')
                            ->label('الهاتف'),
                        TextEntry::make('alternative_phone')
                            ->label('الهاتف البديل')
                            ->badge()
                            ->visible(fn ($record) => $record->alternative_phone !== null),
                        TextEntry::make('date_of_birth')
                            ->label('تاريخ الميلاد')
                            ->date(),
                        TextEntry::make('gender')
                            ->label('النوع'),
                        TextEntry::make('social_status')
                            ->label('الحالة الاجتماعية')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->social_status?->label()),
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
                            ->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا')
                            ->badge()
                            ->color(fn ($state) => $state ? 'warning' : 'gray'),
                        TextEntry::make('disability_details')
                            ->label('تفاصيل الإعاقة')
                            ->visible(fn ($record) => $record->has_disability),
                        TextEntry::make('has_chronic_disease')
                            ->label('مرض مزمن؟')
                            ->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا')
                            ->badge()
                            ->color(fn ($state) => $state ? 'warning' : 'gray'),
                        TextEntry::make('chronic_disease_details')
                            ->label('تفاصيل المرض المزمن')
                            ->visible(fn ($record) => $record->has_chronic_disease),
                        TextEntry::make('housing_status')
                            ->label('حالة المسكن')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->housing_status?->label()),
                        TextEntry::make('need_level')
                            ->label('مستوى الحاجة')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->need_level?->label()),
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
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->visible(fn ($record) => $record->notes !== null),
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
                            ->badge()
                            ->state(fn ($record) => $record->aidRequests()->count()),
                        TextEntry::make('approved_requests_count')
                            ->label('المقبولة')
                            ->badge()
                            ->color('success')
                            ->state(fn ($record) => $record->aidRequests()->where('status', \App\Domains\AidRequests\Enums\AidRequestStatus::APPROVED->value)->count()),
                        TextEntry::make('rejected_requests_count')
                            ->label('المرفوضة')
                            ->badge()
                            ->color('danger')
                            ->state(fn ($record) => $record->aidRequests()->where('status', \App\Domains\AidRequests\Enums\AidRequestStatus::REJECTED->value)->count()),
                        TextEntry::make('delivered_requests_count')
                            ->label('المسلمة')
                            ->badge()
                            ->color('success')
                            ->state(fn ($record) => $record->aidRequests()->where('status', \App\Domains\AidRequests\Enums\AidRequestStatus::DELIVERED->value)->count()),
                    ]),
            ]);
    }
}
