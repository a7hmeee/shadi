<?php

namespace Database\Seeders;

use App\Domains\AidRequests\Actions\ApproveAidRequestAction;
use App\Domains\AidRequests\Actions\DeliverAidRequestAction;
use App\Domains\AidRequests\Actions\RejectAidRequestAction;
use App\Domains\AidRequests\Actions\RequestMoreInformationAction;
use App\Domains\AidRequests\Actions\StartAidRequestReviewAction;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\DocumentType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\AidRequests\Models\AidDistribution;
use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Eligibility\Actions\CalculateAidRequestPriorityAction;
use App\Domains\Eligibility\Actions\DetectAidRequestDuplicateAction;
use App\Domains\Identity\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RealisticHumanitarianDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    private int $receiptCounter = 1;
    private array $demoRequestNumbers = [];
    private array $demoBeneficiaries = [];
    private array $maleFirstNames = array (
  0 => 'محمود',
  1 => 'أحمد',
  2 => 'محمد',
  3 => 'خالد',
  4 => 'يوسف',
  5 => 'سامي',
  6 => 'زاهر',
  7 => 'ناصر',
  8 => 'عمر',
  9 => 'زياد',
  10 => 'سعيد',
  11 => 'عبد الله',
  12 => 'عبد الرحمن',
  13 => 'عبد الرزاق',
  14 => 'معتصم',
  15 => 'بلال',
  16 => 'مازن',
  17 => 'طلال',
  18 => 'حاتم',
  19 => 'بكر',
  20 => 'جابر',
  21 => 'سامر',
  22 => 'ماجد',
  23 => 'وسيم',
  24 => 'هاني',
  25 => 'مصطفى',
  26 => 'ياسر',
  27 => 'عاطف',
  28 => 'صالح',
  29 => 'إبراهيم',
  30 => 'إياد',
  31 => 'مجدي',
  32 => 'نبيل',
  33 => 'رائد',
  34 => 'ضياء',
  35 => 'فهد',
  36 => 'علي',
  37 => 'حسن',
  38 => 'حسين',
  39 => 'تمام',
);

    private array $femaleFirstNames = array (
  0 => 'سمر',
  1 => 'نسرين',
  2 => 'إيمان',
  3 => 'فاطمة',
  4 => 'هدى',
  5 => 'ليلى',
  6 => 'مريم',
  7 => 'رنا',
  8 => 'غادة',
  9 => 'أسماء',
  10 => 'ميساء',
  11 => 'لمى',
  12 => 'آلاء',
  13 => 'أروى',
  14 => 'بثينة',
  15 => 'إخلاص',
  16 => 'سناء',
  17 => 'هبة',
  18 => 'ندى',
  19 => 'غلا',
  20 => 'ميرا',
  21 => 'ريتا',
  22 => 'آية',
  23 => 'دينا',
  24 => 'شهد',
  25 => 'جنى',
  26 => 'سلمى',
  27 => 'أمل',
  28 => 'بشرى',
  29 => 'رحمة',
);

    private array $lastNames = array (
  0 => 'أبو عرام',
  1 => 'أبو شرار',
  2 => 'العواودة',
  3 => 'العوا',
  4 => 'أبو عمر',
  5 => 'القواس',
  6 => 'أبو حسن',
  7 => 'الزياني',
  8 => 'أبو خوصة',
  9 => 'المقوسي',
  10 => 'أبو غالية',
  11 => 'النشاشيبي',
  12 => 'أبو ستة',
  13 => 'الأغا',
  14 => 'أبو علي',
  15 => 'البرغوثي',
  16 => 'أبو الرب',
  17 => 'العسالي',
  18 => 'أبو جازر',
  19 => 'الخطيب',
  20 => 'الزعبي',
  21 => 'أبو ريدة',
  22 => 'الحسيني',
  23 => 'الجرال',
  24 => 'الضبيات',
  25 => 'الكردي',
  26 => 'أبو سلامة',
  27 => 'العمري',
  28 => 'أبو حمد',
  29 => 'العبدالله',
  30 => 'الزهور',
  31 => 'أبو عيسى',
  32 => 'الحوراني',
  33 => 'النور',
  34 => 'أبو زيد',
  35 => 'العمور',
  36 => 'الشاويش',
  37 => 'أبو حمد',
  38 => 'الجابري',
  39 => 'أبو صقر',
);

    private array $cities = array (
  0 => 'الخليل',
  1 => 'دورا',
  2 => 'إذنا',
  3 => 'بيت أولا',
  4 => 'ترقوميا',
  5 => 'حلحول',
  6 => 'يطا',
  7 => 'الظاهرية',
  8 => 'بيت أمر',
  9 => 'سعير',
);

    private array $areas = array (
  0 => 'وسط المدينة',
  1 => 'الحي الشرقي',
  2 => 'الحي الغربي',
  3 => 'منطقة المدارس',
  4 => 'قرب المركز الصحي',
  5 => 'حي البلدية',
  6 => 'منطقة السوق',
  7 => 'المنطقة الجنوبية',
  8 => 'الحي الشمالي',
  9 => 'الحي الجنوبي',
  10 => 'شارع الخليل',
  11 => 'شارع إذنا',
  12 => 'قريب المدرسة',
  13 => 'قرب المستشفى',
  14 => 'شارع المدخل',
);

    private array $disabilities = array (
  0 => 'إعاقة حركية',
  1 => 'إعاقة بصرية',
  2 => 'إعاقة سمعية',
  3 => 'إعاقة دائمة نتيجة إصابة',
);

    private array $chronicDiseases = array (
  0 => 'مرض السكري',
  1 => 'ارتفاع ضغط الدم',
  2 => 'أمراض القلب',
  3 => 'فشل كلوي',
  4 => 'مرض مزمن يحتاج علاجا شهريا',
);

    private array $requestDescriptions = array (
  'food' => 
  array (
    0 => 'تحتاج الأسرة إلى مساعدة غذائية شهرية بسبب انخفاض دخل الأسرة وعدم وجود مصدر دخل ثابت.',
    1 => 'الأسرة في حاجة إلى حقائق غذائية أسبوعية لتموين المستلزمات الغذائية الأساسية.',
    2 => 'الطلب هو مساعدة غذائية طارئة للأسرة بسبب صعوبة الوضع المعيشي الحالي.',
  ),
  'cash' => 
  array (
    0 => 'الأسرة بحاجة إلى مساعدة نقدية لتغطية الاحتياجات الأساسية خلال الفترة الحالية.',
    1 => 'طلب مساعدة نقدية لدفع الإيجار والمصاريف الشهرية.',
    2 => 'الحاجة إلى دعم نقدي لشراء السلع الأساسية والوقود.',
  ),
  'medical' => 
  array (
    0 => 'طلب مساعدة طبية لتغطية تكاليف العلاج والأدوية الشهرية.',
    1 => 'الأسرة بحاجة إلى تغطية تكاليف جراحة طارئة ومرافقتها.',
    2 => 'مساعدة طبية لشراء أدوية مزمنة وفحوصات دورية.',
  ),
  'housing' => 
  array (
    0 => 'السكن بحاجة إلى إصلاحات عاجلة بسبب وجود أضرار تؤثر على سلامة أفراد الأسرة.',
    1 => 'الأسرة تعيش في ظروف سكنية صعبة وتحتاج إلى تحسين البيئة.',
    2 => 'طلب مساعدة سكن لتأمين مسكن آمن.',
  ),
  'education' => 
  array (
    0 => 'طلب مساعدة تعليمية لتغطية رسوم ومستلزمات الدراسة للأطفال.',
    1 => 'الأسرة بحاجة إلى دعم تعليمي لابنها الجامعي لإكمال دراسته.',
    2 => 'مساعدة لشراء أدوات مدرسية وكتب للطلاب في العائلة.',
  ),
  'clothing' => 
  array (
    0 => 'الأسرة بحاجة إلى ملابس أساسية للأطفال وكبار السن خلال فصل الشتاء.',
    1 => 'طلب ملابس طارئة بعد تلف الملابس السابقة بسبب الأمطار.',
    2 => 'مساعدة بشراء ملابس عيدية للعائلة.',
  ),
  'emergency' => 
  array (
    0 => 'طلب مساعدة طارئة للأسرة التي فقدت مصدر دخلها الوحيد.',
    1 => 'دعم طارئ للأسرة المتأثرة بأعمال عنف في المنطقة.',
    2 => 'مساعدة طارئة للوفدة الواحدة التي تريد إجراظة.',
  ),
  'other' => 
  array (
    0 => 'طلب مساعدة لتغطية نفقات غير مصنفة بنوع آخر.',
    1 => 'طلب دعم بغيرتها للحاجات اليومية غير المغطاة.',
  ),
);

    private array $rejectionReasons = array (
  0 => 'عدم اكتمال المستندات المطلوبة.',
  1 => 'عدم انطباق معايير الاستحقاق الحالية.',
  2 => 'وجود طلب آخر فعال لنفس نوع المساعدة.',
  3 => 'الحاجة إلى تحديث البيانات المالية للأسرة.',
  4 => 'تمت الاستفادة من نفس نوع المساعدة خلال فترة قريبة.',
);

    private array $infoNotes = array (
  0 => 'يرجى إرفاق إثبات دخل محدث.',
  1 => 'يرجى تحديث عدد أفراد الأسرة.',
  2 => 'يرجى إرفاق تقرير طبي حديث.',
  3 => 'يرجى توضيح بيانات السكن الحالية.',
  4 => 'يرجى إرفاق صورة من الهوية الوطنية.',
);

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('RealisticHumanitarianDemoSeeder is not intended for production.');
            return;
        }

        // Ensure roles exist; call RolesAndPermissionsSeeder only if roles are missing
        if (DB::table('roles')->where('name', UserRole::SUPER_ADMIN->value)->doesntExist()) {
            $this->call(RolesAndPermissionsSeeder::class);
        }

        // Clean up old demo data (preserve admin@aid.test and roles)
        $this->cleanupOldData();

        $this->command->info('إنشاء موظفين...');
        $staff = $this->createStaffUsers();

        $this->command->info('إنشاء مستفيدين...');
        $beneficiaries = $this->createBeneficiaries(40);

        $this->command->info('إنشاء طلبات مساعدة...');
        $this->createRequests($beneficiaries, $staff);

        $this->command->info('حساب الأولويات عبر النظام...');
        $this->computePriorities();

        $this->command->info('كشف التكرار عبر النظام...');
        $this->detectDuplicates();

        $this->command->info('إنشاء مرفقات...');
        $this->createAttachments();

        $this->command->info('إنشاء عمليات تسليم...');
        $this->createDistributions($staff);

        $this->command->info('تم إكمال البيانات التجريبية بنجاح.');
    }

    private function cleanupOldData(): void
    {
        // Delete ALL demo data to ensure clean state on re-run
        // Preserve admin@aid.test and roles
        AidRequestAttachment::query()->forceDelete();
        AidDistribution::query()->delete();
        AidRequest::query()->forceDelete();
        Beneficiary::query()->forceDelete();

        // Delete staff users (preserve admin@aid.test)
        User::where('email', '!=', 'admin@aid.test')->whereIn('email', [
            'ahmad.staff@aid.test', 'reem.staff@aid.test', 'manager@aid.test',
        ])->each(function ($u) {
            $u->delete();
        });

        // Delete demo beneficiary users
        User::where('email', 'like', 'ben_%@aid.test')->each(function ($u) {
            $u->delete();
        });

        $this->receiptCounter = 1;
    }

    private function createStaffUsers(): array
    {
        $admin = User::where('email', 'admin@aid.test')->firstOrFail();
        $admin->assignRole(UserRole::SUPER_ADMIN->value);

        $reviewer = User::updateOrCreate(
            ['email' => 'ahmad.staff@aid.test'],
            [
                'name' => 'أحمد الخطيب',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $reviewer->assignRole(UserRole::EMPLOYEE->value);

        $approver = User::updateOrCreate(
            ['email' => 'reem.staff@aid.test'],
            [
                'name' => 'ريم الجعبري',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $approver->assignRole(UserRole::ADMIN->value);

        $distributor = User::updateOrCreate(
            ['email' => 'manager@aid.test'],
            [
                'name' => 'محمد أبو سنينة',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $distributor->assignRole(UserRole::ADMIN->value);

        return [
            'admin' => $admin,
            'reviewer' => $reviewer,
            'approver' => $approver,
            'distributor' => $distributor,
        ];
    }
    private function createBeneficiaries(int $count): array
    {
        $beneficiaries = [];
        $usedNationalIds = [];
        $usedPhones = [];

        $demoSpecs = [
            ['name' => 'سامر محمود أبو عرام', 'gender' => 'male', 'city' => 'الخليل', 'demo' => 'critical'],
            ['name' => 'ليلى أحمد الجعبري', 'gender' => 'female', 'city' => 'دورا', 'demo' => 'duplicate'],
            ['name' => 'خالد يوسف الحروب', 'gender' => 'male', 'city' => 'إذنا', 'demo' => 'delivered'],
        ];

        for ($i = 0; $i < $count; $i++) {
            if ($i < 3) {
                $parts = explode(' ', $demoSpecs[$i]['name'], 2);
                $firstName = $parts[0];
                $lastName = $parts[1];
                $gender = $demoSpecs[$i]['gender'];
                $city = $demoSpecs[$i]['city'];
                $demoFlag = $demoSpecs[$i]['demo'];
            } else {
                $gender = fake()->boolean(50) ? 'male' : 'female';
                $firstName = ($gender === 'male')
                    ? $this->maleFirstNames[array_rand($this->maleFirstNames)]
                    : $this->femaleFirstNames[array_rand($this->femaleFirstNames)];
                $lastName = $this->lastNames[array_rand($this->lastNames)];
                $city = $this->cities[array_rand($this->cities)];
                $demoFlag = null;
            }

            $fullName = $firstName . ' ' . $lastName;

            $user = User::create([
                'name' => $fullName,
                'email' => 'ben_' . $i . '_' . time() . '@aid.test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole(UserRole::BENEFICIARY->value);

            do {
                $nationalId = (string) random_int(100000000, 999999999);
            } while (in_array($nationalId, $usedNationalIds));
            $usedNationalIds[] = $nationalId;

            do {
                $prefix = ['059', '056', '052', '054'][array_rand(['059', '056', '052', '054'])];
                $phone = $prefix . random_int(1000000, 9999999);
            } while (in_array($phone, $usedPhones));
            $usedPhones[] = $phone;

            $familyMembers = match ($i % 8) {
                0 => fake()->numberBetween(5, 7),
                1 => fake()->numberBetween(8, 12),
                2 => fake()->numberBetween(3, 5),
                3 => fake()->numberBetween(4, 6),
                4 => fake()->numberBetween(2, 3),
                5 => fake()->numberBetween(7, 9),
                6 => fake()->numberBetween(4, 8),
                7 => fake()->numberBetween(3, 10),
            };
            $children = fake()->numberBetween(0, max(1, $familyMembers - 2));
            $dependents = fake()->numberBetween(0, max(0, $familyMembers - $children - 1));
            $incomeBrackets = [0, 300, 800, 1200, 2000, 3000, 4500];
            $income = $incomeBrackets[array_rand($incomeBrackets)] + fake()->numberBetween(-50, 300);
            $income = (int) max(0, min(4500, $income));

            $statusPool = [
                SocialStatus::MARRIED->value, SocialStatus::MARRIED->value,
                SocialStatus::MARRIED->value, SocialStatus::MARRIED->value,
                SocialStatus::WIDOWED->value, SocialStatus::MARRIED->value,
                SocialStatus::DIVORCED->value, SocialStatus::MARRIED->value,
                SocialStatus::SINGLE->value, SocialStatus::SEPARATED->value,
            ];
            $socialStatus = $statusPool[array_rand($statusPool)];

            $needPool = [
                NeedLevel::HIGH->value, NeedLevel::HIGH->value, NeedLevel::CRITICAL->value,
                NeedLevel::MEDIUM->value, NeedLevel::HIGH->value, NeedLevel::CRITICAL->value,
                NeedLevel::LOW->value, NeedLevel::MEDIUM->value,
            ];
            $needLevel = $needPool[array_rand($needPool)];

            $housingPool = [
                HousingStatus::RENTED->value, HousingStatus::RENTED->value,
                HousingStatus::OWNED->value, HousingStatus::HOSTED->value,
                HousingStatus::RENTED->value, HousingStatus::UNSAFE->value,
                HousingStatus::TEMPORARY->value, HousingStatus::RENTED->value,
                HousingStatus::HOMELESS->value, HousingStatus::OWNED->value,
            ];

            $hasDisability = fake()->boolean(20);
            $hasChronic = fake()->boolean(25);

            if ($demoFlag === 'critical') {
                $familyMembers = fake()->numberBetween(8, 12);
                $children = fake()->numberBetween(3, 6);
                $dependents = fake()->numberBetween(2, 5);
                $income = fake()->numberBetween(0, 300);
                $hasDisability = true;
                $hasChronic = true;
                $housingStatus = HousingStatus::UNSAFE->value;
                $needLevel = NeedLevel::CRITICAL->value;
            }

            if ($income <= 800 && $demoFlag !== 'critical') {
                $housingPool = [HousingStatus::HOSTED->value, HousingStatus::RENTED->value, HousingStatus::TEMPORARY->value];
            }

            // For low income families, prefer hosted/temporary
            if ($income <= 500 && $demoFlag !== 'critical') {
                $housingPool = [HousingStatus::HOSTED->value, HousingStatus::HOMELESS->value, HousingStatus::UNSAFE->value, HousingStatus::TEMPORARY->value];
            }
            $housingStatus = $housingPool[array_rand($housingPool)];

            $beneficiary = Beneficiary::create([
                'user_id' => $user->id,
                'national_id' => $nationalId,
                'phone' => $phone,
                'alternative_phone' => fake()->boolean(60) ? $phone : null,
                'date_of_birth' => fake()->dateTimeBetween('-65 years', '-18 years'),
                'gender' => $gender,
                'social_status' => $socialStatus,
                'family_members_count' => $familyMembers,
                'children_count' => $children,
                'dependents_count' => $dependents,
                'monthly_income' => $income,
                'has_disability' => $hasDisability,
                'disability_details' => $hasDisability ? $this->disabilities[array_rand($this->disabilities)] : null,
                'has_chronic_disease' => $hasChronic,
                'chronic_disease_details' => $hasChronic ? $this->chronicDiseases[array_rand($this->chronicDiseases)] : null,
                'housing_status' => $housingStatus,
                'need_level' => $needLevel,
                'governorate' => 'الخليل',
                'city' => $city,
                'address' => $this->areas[array_rand($this->areas)],
                'notes' => fake()->boolean(30)
                    ? 'ملاحظة: ' . $this->infoNotes[array_rand($this->infoNotes)]
                    : null,
                'is_active' => true,
            ]);

            $beneficiary->demo_flag = $demoFlag ?? 'normal';
            $beneficiaries[] = $beneficiary;
        }

        return $beneficiaries;
    }
    private function createRequests(array $beneficiaries, array $staff): void
    {
        $reviewer = $staff['reviewer'];
        $approver = $staff['approver'];

        $numGen = app(\App\Domains\AidRequests\Actions\GenerateAidRequestNumberAction::class);

        // Status targets for ~85 total requests (13+17+8+32+9+0+6=85)
        // Note: DELIVERED requests start as APPROVED then get distributed by createDistributions
        $statusTargets = [
            AidRequestStatus::SUBMITTED->value => 13,
            AidRequestStatus::UNDER_REVIEW->value => 17,
            AidRequestStatus::NEEDS_INFORMATION->value => 8,
            AidRequestStatus::APPROVED->value => 12 + 20,
            AidRequestStatus::REJECTED->value => 9,
            AidRequestStatus::CANCELLED->value => 6,
        ];

        $statusQueue = [];
        foreach ($statusTargets as $statusValue => $count) {
            for ($j = 0; $j < $count; $j++) {
                $statusQueue[] = AidRequestStatus::from($statusValue);
            }
        }
        shuffle($statusQueue);

        $aidTypeWeights = [
            AidType::FOOD->value, AidType::FOOD->value, AidType::FOOD->value, AidType::FOOD->value,
            AidType::CASH->value, AidType::CASH->value, AidType::CASH->value, AidType::CASH->value,
            AidType::MEDICAL->value, AidType::MEDICAL->value, AidType::MEDICAL->value,
            AidType::HOUSING->value, AidType::HOUSING->value, AidType::HOUSING->value,
            AidType::EDUCATION->value, AidType::EDUCATION->value, AidType::EDUCATION->value,
            AidType::EMERGENCY->value, AidType::EMERGENCY->value,
            AidType::CLOTHING->value, AidType::CLOTHING->value,
            AidType::OTHER->value,
        ];

        $aidRequests = [];
        $statusIndex = 0;
        $benCount = count($beneficiaries);

        // Ensure each beneficiary gets 1-4 requests
        $requestsPerBeneficiary = [];
        $totalTarget = array_sum($statusTargets);
        $base = (int) floor($totalTarget / $benCount);
        for ($i = 0; $i < $benCount; $i++) {
            $extra = ($i < ($totalTarget - $base * $benCount)) ? 1 : 0;
            $requestsPerBeneficiary[$i] = max(1, min(4, $base + $extra));
        }

        $idx = 0;
        foreach ($beneficiaries as $benIndex => $beneficiary) {
            $numRequests = $requestsPerBeneficiary[$benIndex] ?? 1;
            for ($r = 0; $r < $numRequests; $r++) {
                if ($statusIndex >= count($statusQueue)) {
                    break 2;
                }
                $status = $statusQueue[$statusIndex];
                $aidType = $aidTypeWeights[array_rand($aidTypeWeights)];
                $aidRequests[] = [$beneficiary, $status, $aidType, $benIndex];
                $statusIndex++;
            }
        }

        // Track dates per beneficiary
        $benDates = [];

        foreach ($aidRequests as $entry) {
            [$beneficiary, $status, $aidType, $benIndex] = $entry;
            $dayOffset = fake()->numberBetween(0, 89);
            $baseDate = Carbon::now()->subDays($dayOffset)->startOfDay();

            $submittedAt = $baseDate->copy()->addHours(fake()->numberBetween(8, 18));

            $requestData = [
                'beneficiary_id' => $beneficiary->id,
                'aid_type' => $aidType,
                'title' => $beneficiary->user->name . ' - ' . AidType::from($aidType)->label(),
                'description' => $this->requestDescriptions[$aidType][array_rand($this->requestDescriptions[$aidType])],
                'status' => $status->value,
                'submitted_at' => $submittedAt,
                'reviewed_by' => null,
                'decision_by' => null,
                'reviewed_at' => null,
                'approved_at' => null,
                'rejected_at' => null,
                'delivered_at' => null,
                'requested_amount' => in_array($aidType, [AidType::CASH->value, AidType::EMERGENCY->value])
                    ? fake()->randomElement([200, 300, 500, 750, 1000, 1500, 2000])
                    : null,
                'approved_amount' => null,
                'rejection_reason' => null,
                'reviewer_notes' => null,
                'beneficiary_notes' => null,
                'priority_score' => null,
                'priority_level' => PriorityLevel::MEDIUM->value,
                'possible_duplicate' => false,
                'duplicate_of_request_id' => null,
            ];

            // Set timestamps based on status workflow
            if (in_array($status, [AidRequestStatus::UNDER_REVIEW, AidRequestStatus::APPROVED, AidRequestStatus::REJECTED, AidRequestStatus::DELIVERED])) {
                $reviewer = fake()->boolean(70) ? $staff['reviewer'] : $staff['approver'];
                $requestData['reviewed_by'] = $reviewer->id;
                $requestData['reviewed_at'] = $submittedAt->copy()->addHours(fake()->numberBetween(2, 48));
            }
            if (in_array($status, [AidRequestStatus::APPROVED, AidRequestStatus::DELIVERED])) {
                $decisionMaker = fake()->boolean(50) ? $staff['approver'] : $staff['admin'];
                $requestData['decision_by'] = $decisionMaker->id;
                $requestData['approved_at'] = $requestData['reviewed_at']->copy()->addHours(fake()->numberBetween(2, 24));
                $requestData['approved_amount'] = $requestData['requested_amount']
                    ?? fake()->randomElement([100, 200, 300, 500, 750, 1000, 1500, 2000]);
            }
            if ($status === AidRequestStatus::REJECTED) {
                $decisionMaker = fake()->boolean(60) ? $staff['approver'] : $staff['admin'];
                $requestData['decision_by'] = $decisionMaker->id;
                $requestData['rejected_at'] = $requestData['reviewed_at']->copy()->addHours(fake()->numberBetween(1, 12));
                $requestData['rejection_reason'] = $this->rejectionReasons[array_rand($this->rejectionReasons)];
            }
            if ($status === AidRequestStatus::NEEDS_INFORMATION) {
                $requestData['reviewer_notes'] = $this->infoNotes[array_rand($this->infoNotes)];
            }

            $requestNumber = $numGen->execute();
            $request = AidRequest::create(array_merge($requestData, ['request_number' => $requestNumber]));

            // Track for duplicate logic
            if (!isset($benDates[$benIndex])) {
                $benDates[$benIndex] = [];
            }
            $benDates[$benIndex][] = $request;
        }

        // Create deliberate duplicates: add a second request for duplicate-flag beneficiaries
        // with same aid_type within 30 days (to trigger duplicate detection)
        $duplicateRequestNumber = null;
        foreach ($beneficiaries as $benIndex => $beneficiary) {
            if ($beneficiary->demo_flag === 'duplicate') {
                $existing = collect($aidRequests)->filter(fn($e) => $e[3] === $benIndex)->first();
                if ($existing) {
                    $aidType = $existing[2];
                    $requestNumber2 = $numGen->execute();
                    $submittedAt2 = Carbon::now()->subDays(fake()->numberBetween(5, 25))->startOfDay()->addHours(10);

                    AidRequest::create([
                        'beneficiary_id' => $beneficiary->id,
                        'request_number' => $requestNumber2,
                        'aid_type' => $aidType,
                        'title' => $beneficiary->user->name . ' - ' . AidType::from($aidType)->label(),
                        'description' => $this->requestDescriptions[$aidType][array_rand($this->requestDescriptions[$aidType])],
                        'status' => AidRequestStatus::SUBMITTED->value,
                        'submitted_at' => $submittedAt2,
                        'reviewed_by' => null,
                        'decision_by' => null,
                        'requested_amount' => null,
                        'priority_score' => null,
                        'priority_level' => PriorityLevel::MEDIUM->value,
                        'possible_duplicate' => false,
                        'duplicate_of_request_id' => null,
                    ]);
                    $duplicateRequestNumber = $requestNumber2;
                }
            }
        }

        // Store demo reference for reporting
        $this->demoRequestNumbers = [
            'duplicate' => $duplicateRequestNumber,
        ];
    }
    private function computePriorities(): void
    {
        $calcAction = app(CalculateAidRequestPriorityAction::class);
        $requests = AidRequest::with("beneficiary")->get();
        foreach ($requests as $request) {
            if ($request->beneficiary) {
                $calcAction->execute($request);
            }
        }
    }

    private function detectDuplicates(): void
    {
        $detectAction = app(DetectAidRequestDuplicateAction::class);
        $requests = AidRequest::query()->with("beneficiary")->get();
        foreach ($requests as $request) {
            try {
                $detectAction->execute($request);
            } catch (\Throwable $e) {
                // skip on error
            }
        }
    }
    private function createAttachments(): void
    {
        $staff = User::whereIn("email", ["admin@aid.test", "ahmad.staff@aid.test", "reem.staff@aid.test"])->get()->all();

        $docTypes = [DocumentType::IDENTITY, DocumentType::INCOME_PROOF, DocumentType::MEDICAL_REPORT, DocumentType::HOUSING_PROOF, DocumentType::FAMILY_DOCUMENT];
        $descriptions = [
            DocumentType::IDENTITY->value => "صورة من الهوية الوطنية",
            DocumentType::INCOME_PROOF->value => "إثبات دخل أسبوعي",
            DocumentType::MEDICAL_REPORT->value => "تقرير طبي حديث",
            DocumentType::HOUSING_PROOF->value => "إثبات سكن",
            DocumentType::FAMILY_DOCUMENT->value => "وثيقة عائلية",
        ];

        $requests = AidRequest::inRandomOrder()->limit(15)->get();

        foreach ($requests as $request) {
            $uploader = $staff[array_rand($staff)];
            $docType = $docTypes[array_rand($docTypes)];
            $counter = $this->receiptCounter;
            $this->receiptCounter++;

            AidRequestAttachment::create([
                "aid_request_id" => $request->id,
                "uploaded_by" => $uploader->id,
                "original_name" => "مستند_" . $counter . ".pdf",
                "file_name" => \Illuminate\Support\Str::uuid()->toString() . ".pdf",
                "file_path" => "aid-requests/" . $request->id . "/attachments/" . \Illuminate\Support\Str::uuid()->toString() . ".pdf",
                "disk" => "public",
                "mime_type" => "application/pdf",
                "file_size" => fake()->numberBetween(1024, 2000000),
                "document_type" => $docType->value,
                "description" => $descriptions[$docType->value],
                "is_verified" => $isVerified = fake()->boolean(70),
                "verified_by" => $isVerified ? $uploader->id : null,
                "verified_at" => $isVerified ? now()->subDays(fake()->numberBetween(0, 30)) : null,
            ]);
        }
    }
    private function createDistributions(array $staff): void
    {
        $distributor = $staff["distributor"];

        $methods = ["in_person", "transfer", "field_delivery", "quota"];
        $methodLabels = [
            "in_person" => "تل التسليل بنجاح",
            "transfer" => "تل التسليل بنجاح",
            "field_delivery" => "تل التسليل بنجاح",
            "quota" => "تل التسليل بنجاح",
        ];

        // Create distributions for APPROVED requests that don't have one yet
        // Target: 20 delivered out of 32 approved (leave 12 as approved)
        $approvedRequests = AidRequest::where("status", AidRequestStatus::APPROVED)
            ->whereDoesntHave("distribution")
            ->with("beneficiary.user")
            ->inRandomOrder()
            ->get();

        $deliverCount = 0;
        $targetDeliver = 20;

        foreach ($approvedRequests as $request) {
            if ($deliverCount >= $targetDeliver) {
                continue;
            }
            $deliverCount++;
            $method = $methods[array_rand($methods)];
            $receiptNumber = "REC-2026-" . str_pad($this->receiptCounter, 4, "0", STR_PAD_LEFT);
            $this->receiptCounter++;

            $deliveredAt = Carbon::parse($request->approved_at)->addHours(fake()->numberBetween(1, 72));

            $amount = null;
            if (in_array($request->aid_type, [AidType::CASH->value, AidType::EMERGENCY->value])) {
                $amount = $request->approved_amount ?? $request->requested_amount ?? fake()->randomElement([100, 200, 500, 1000]);
            }

            AidDistribution::create([
                "aid_request_id" => $request->id,
                "distributed_by" => $distributor->id,
                "delivered_to_name" => "المستفيد " . $request->beneficiary->user->name,
                "delivered_to_national_id" => $request->beneficiary->national_id,
                "delivery_method" => $method,
                "amount" => $amount,
                "delivered_at" => $deliveredAt,
                "receipt_number" => $receiptNumber,
                "notes" => fake()->boolean(60) ? "ملاحظة تسليم" : null,
            ]);

            // Update status to delivered
            $request->update([
                "status" => AidRequestStatus::DELIVERED->value,
                "delivered_at" => $deliveredAt,
            ]);
        }
    }
}