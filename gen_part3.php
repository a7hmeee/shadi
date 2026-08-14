<?php
// Generator Part 3: run() method and createStaffUsers()
$code = <<<'PHP'
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('RealisticHumanitarianDemoSeeder is not intended for production.');
            return;
        }

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

PHP;
file_put_contents('database/seeders/RealisticHumanitarianDemoSeeder.php', $code, FILE_APPEND);
echo "Part 3 written. Bytes: " . strlen($code) . "\n";
