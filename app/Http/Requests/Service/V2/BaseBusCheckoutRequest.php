<?php

namespace App\Http\Requests\Service\V2;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class BaseBusCheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        if (!$this->filled('total_price') && $this->filled('price')) {
            $this->merge([
                'total_price' => $this->input('price'),
            ]);
        }

        if ($this->has('phone_number')) {
            $this->merge([
                'phone_number' => collect((array) $this->input('phone_number', []))
                    ->map(function ($value) {
                        return $this->normalizeFrenchPhone($value);
                    })
                    ->values()
                    ->all(),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            foreach ((array) $this->input('identification_expiry_date', []) as $index => $value) {
                $date = $this->parseDateValue($value);

                if ($date && $date->lt(Carbon::today())) {
                    $validator->errors()->add('identification_expiry_date.' . $index, __('bus.validation.passport_expiry_future'));
                }
            }
        });
    }

    public function messages()
    {
        return [
            'required' => __('bus.validation.required_field'),
            'email' => __('bus.validation.valid_email'),
            'date_format' => __('bus.validation.invalid_date'),
            'array' => __('bus.validation.invalid_selection'),
            'in' => __('bus.validation.invalid_selection'),
            'regex' => __('bus.validation.valid_phone'),
        ];
    }

    public function attributes()
    {
        return [
            'reservation_token' => __('bus.attributes.booking'),
            'reservation_id' => __('bus.attributes.booking'),
            'booking_number' => __('bus.attributes.booking'),
            'booking_id' => __('bus.attributes.booking'),
            'sales_channel_code' => __('bus.attributes.sales_channel'),
            'passenger_id.*' => __('bus.attributes.passenger'),
            'segment_id.*' => __('bus.attributes.segment'),
            'price' => __('bus.attributes.total_price'),
            'total_price' => __('bus.attributes.total_price'),
            'currency' => __('bus.attributes.currency'),
            'firstname.*' => __('bus.attributes.first_name'),
            'lastname.*' => __('bus.attributes.last_name'),
            'birthdate.*' => __('bus.attributes.date_of_birth'),
            'email.*' => __('bus.attributes.email'),
            'phone_number.*' => __('bus.attributes.phone_number'),
            'gender.*' => __('bus.attributes.gender'),
            'citizenship.*' => __('bus.attributes.citizenship'),
            'identification_number.*' => __('bus.attributes.passport_number'),
            'identification_expiry_date.*' => __('bus.attributes.passport_expiry'),
            'visa_permit_type.*' => __('bus.attributes.visa_or_permit'),
            'identification_type.*' => __('bus.attributes.identification_type'),
            'identification_issuing_country.*' => __('bus.attributes.issuing_country'),
        ];
    }

    protected function normalizeFrenchPhone($value)
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return '';
        }

        if (strpos($digits, '33') === 0) {
            $digits = substr($digits, 2);
        }

        $digits = ltrim($digits, '0');

        return $digits === '' ? '' : '+33' . $digits;
    }

    protected function parseDateValue($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        foreach (['d.m.Y', 'Y-m-d', 'd.m.y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
            } catch (\Exception $e) {
                $date = null;
            }

            if ($date && $date->format($format) === $value) {
                return $date->startOfDay();
            }
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }
}
