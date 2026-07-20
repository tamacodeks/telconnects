<?php

namespace App\Http\Requests\Service\V2;

class BlaCheckoutRequest extends BaseBusCheckoutRequest
{
    public function rules()
    {
        return [
            'booking_number' => 'required|string',
            'booking_id' => 'required|string',
            'sales_channel_code' => 'required|string',
            'price' => 'required',
            'total_price' => 'required',
            'currency' => 'required|string',
            'passenger_id' => 'required|array|min:1',
            'passenger_id.*' => 'required',
            'segment_id' => 'required|array|min:1',
            'segment_id.*' => 'required',
            'firstname' => 'required|array|min:1',
            'firstname.*' => 'required|string|max:80',
            'lastname' => 'required|array|min:1',
            'lastname.*' => 'required|string|max:80',
            'birthdate' => 'required|array|min:1',
            'birthdate.*' => 'required|date_format:d.m.Y',
            'email' => 'required|array|min:1',
            'email.*' => 'required|email',
            'phone_number' => 'required|array|min:1',
            'phone_number.*' => ['required', 'regex:/^\+33[67]\d{8}$/'],
            'gender' => 'required|array|min:1',
            'gender.*' => 'required|in:male,female',
            'citizenship' => 'required|array|min:1',
            'citizenship.*' => 'required|string',
            'identification_number' => 'required|array|min:1',
            'identification_number.*' => 'required|string|max:64',
            'identification_expiry_date' => 'required|array|min:1',
            'identification_expiry_date.*' => 'required|date_format:d.m.Y',
            'visa_permit_type' => 'required|array|min:1',
            'visa_permit_type.*' => 'required|string',
            'identification_type' => 'required|array|min:1',
            'identification_type.*' => 'required|string',
            'identification_issuing_country' => 'required|array|min:1',
            'identification_issuing_country.*' => 'required|string',
        ];
    }
}
