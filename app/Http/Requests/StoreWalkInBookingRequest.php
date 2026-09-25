<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalkInBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('owner');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parking_slot_id' => 'required|integer|exists:parking_slots,id',
            'customer_name'   => 'required|string|max:255',
            'customer_phone'  => 'required|string|max:20',
            'customer_email'  => 'nullable|email|max:255',
            'vehicle_number'  => 'required|string|max:20',
            'vehicle_type'    => 'nullable|in:car,bike,suv,bus',
            'duration_hours'  => 'nullable|numeric|min:0.5|max:168',
            'custom_amount'   => 'nullable|numeric|min:0',
            'payment_status'  => 'required|in:pending,paid,cash,waived',
            'notes'           => 'nullable|string|max:1000',
        ];
    }
}
