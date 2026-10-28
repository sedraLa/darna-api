<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'governorate_id' => ['sometimes', 'integer', 'exists:governorates,id'],
            'purpose'        => ['sometimes', Rule::enum(PropertyPurpose::class)],
            'type'           => ['sometimes', Rule::enum(PropertyType::class)],
            'status'         => ['sometimes', Rule::enum(PropertyStatus::class)],
            'currency'       => ['sometimes', Rule::enum(Currency::class)],
            'price_min'      => ['sometimes', 'numeric', 'min:0'],
            'price_max'      => ['sometimes', 'numeric', 'min:0', 'gte:price_min'],
            'area_min'       => ['sometimes', 'numeric', 'min:0'],
            'area_max'       => ['sometimes', 'numeric', 'min:0', 'gte:area_min'],
            'office_id'      => ['sometimes', 'integer', 'exists:offices,id'],
            'per_page'       => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page'           => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'governorate_id' => 'المحافظة',
            'purpose'        => 'غرض العقار',
            'type'           => 'نوع العقار',
            'status'         => 'حالة العقار',
            'currency'       => 'العملة',
            'price_min'      => 'الحد الأدنى للسعر',
            'price_max'      => 'الحد الأقصى للسعر',
            'area_min'       => 'الحد الأدنى للمساحة',
            'area_max'       => 'الحد الأقصى للمساحة',
            'office_id'      => 'المكتب',
            'per_page'       => 'عدد النتائج في الصفحة',
            'page'           => 'رقم الصفحة',
        ];
    }

    public function messages(): array
    {
        return [
            'integer'        => 'يجب أن يكون :attribute رقماً صحيحاً.',
            'exists'         => ':attribute غير موجود.',
            'numeric'        => 'يجب أن يكون :attribute رقماً.',
            'min'            => 'يجب أن يكون :attribute على الأقل :min.',
            'max'            => 'يجب ألا يتجاوز :attribute :max.',
            'gte'            => 'يجب أن يكون :attribute أكبر من أو يساوي :value.',
            'per_page.max'   => 'لا يمكن أن يتجاوز عدد النتائج في الصفحة 100.',
        ];
    }
}
