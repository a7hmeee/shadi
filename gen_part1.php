<?php
// Generator Part 1: class header, property arrays, and run() method
$x = <<<'PHP'
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
use Illuminate\Support\Facades\Hash;

class RealisticHumanitarianDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    private int $receiptCounter = 1;

PHP;
file_put_contents('database/seeders/RealisticHumanitarianDemoSeeder.php', $x);
echo "Part 1 written. Bytes: " . strlen($x) . "\n";
