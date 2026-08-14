<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\DTOs\AidDistributionData;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidDistribution;
use App\Domains\AidRequests\Models\AidRequest;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeliverAidRequestAction
{
    public function execute(AidRequest $aidRequest, AidDistributionData $data, User $distributor): AidDistribution
    {
        if (! $distributor->can('markDelivered', $aidRequest)) {
            throw new AuthorizationException('غير مصرح لك بتسجيل عمليات التسليم.');
        }

        return DB::transaction(function () use ($aidRequest, $data, $distributor) {
            if ($aidRequest->status !== AidRequestStatus::APPROVED) {
                throw new DomainException(
                    'لا يمكن تسليم طلب بحالة: ' . $aidRequest->status->label()
                );
            }

            if ($aidRequest->distribution()->exists()) {
                throw new DomainException('تم تسجيل تسليم لهذا الطلب مسبقًا.');
            }

            $distribution = AidDistribution::create([
                'aid_request_id' => $aidRequest->id,
                'distributed_by' => $distributor->id,
                'delivered_to_name' => $data->deliveredToName,
                'delivered_to_national_id' => $data->deliveredToNationalId,
                'delivery_method' => $data->deliveryMethod,
                'amount' => $data->amount,
                'delivered_at' => $data->deliveredAt,
                'receipt_number' => $data->receiptNumber,
                'notes' => $data->notes,
            ]);

            $aidRequest->update([
                'status' => AidRequestStatus::DELIVERED,
                'delivered_at' => $data->deliveredAt,
            ]);

            return $distribution;
        });
    }
}
