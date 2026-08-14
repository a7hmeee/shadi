<?php

namespace App\Domains\AidRequests\Rules;

use Illuminate\Validation\Rule;

class DeliveryValidationRules
{
    public static function rules(?int $excludeDistributionId = null): array
    {
        $receiptUnique = Rule::unique('aid_distributions', 'receipt_number');
        if ($excludeDistributionId !== null) {
            $receiptUnique->ignore($excludeDistributionId);
        }

        return [
            'delivered_to_name' => ['required', 'string', 'max:255'],
            'delivered_to_national_id' => ['nullable', 'string', 'max:255'],
            'delivery_method' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'delivered_at' => ['required', 'date'],
            'receipt_number' => ['nullable', 'string', 'max:255', $receiptUnique],
            'notes' => ['nullable', 'string'],
        ];
    }
}
