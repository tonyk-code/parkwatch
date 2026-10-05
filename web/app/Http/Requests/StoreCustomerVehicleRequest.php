<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true
            && $this->user()?->isCustomer();
    }

    public function rules(): array
    {
        return [
            'plate_display' => [
                'required',
                'string',
                'max:25',
            ],
            'plate_region' => [
                'nullable',
                'string',
                'max:10',
            ],
            'vehicle_type' => [
                'required',
                'string',
                'max:20',
            ],
            'make' => [
                'nullable',
                'string',
                'max:50',
            ],
            'model' => [
                'nullable',
                'string',
                'max:50',
            ],
            'colour' => [
                'nullable',
                'string',
                'max:30',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }
}