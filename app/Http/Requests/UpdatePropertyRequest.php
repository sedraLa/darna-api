<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\UserRole;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $this->user() && $this->user()->can('update', $property);
    }

    public function rules(): array
    {
        $user     = $this->user();
        $property = $this->route('property');
        $officeId = $property->office_id;

        $statusRules = [
            'sometimes',
            Rule::enum(PropertyStatus::class),
        ];

        if ($user->role !== UserRole::Admin) {
            // Office users can only move status to available or reserved;
            // pending/rejected/sold transitions are admin-only.
            $statusRules[] = Rule::in([
                PropertyStatus::Available->value,
                PropertyStatus::Reserved->value,
            ]);
        }

        return [
            'title'          => ['sometimes', 'string', 'max:255'],
            'description'    => ['sometimes', 'nullable', 'string', 'max:5000'],
            'price'          => ['sometimes', 'numeric', 'min:0'],
            'currency'       => ['sometimes', Rule::enum(Currency::class)],
            'purpose'        => ['sometimes', Rule::enum(PropertyPurpose::class)],
            'type'           => ['sometimes', Rule::enum(PropertyType::class)],
            'status'         => $statusRules,
            'area'           => ['sometimes', 'numeric', 'min:1'],
            'governorate_id' => ['sometimes', 'integer', 'exists:governorates,id'],
            'owner_id'       => [
                'sometimes',
                'integer',
                Rule::exists('owners', 'id')->where('office_id', $officeId),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->request->remove('office_id');
    }

    public function attributes(): array
    {
        return [
            'title'          => 'عنوان العقار',
            'description'    => 'وصف العقار',
            'price'          => 'السعر',
            'currency'       => 'العملة',
            'purpose'        => 'غرض العقار',
            'type'           => 'نوع العقار',
            'status'         => 'حالة العقار',
            'area'           => 'المساحة',
            'governorate_id' => 'المحافظة',
            'owner_id'       => 'المالك',
        ];
    }

    public function messages(): array
    {
        return [
            'string'  => 'يجب أن يكون :attribute نصاً.',
            'max'     => 'يجب ألا يتجاوز :attribute :max حرفاً.',
            'numeric' => 'يجب أن يكون :attribute رقماً.',
            'min'     => 'يجب أن يكون :attribute على الأقل :min.',
            'integer' => 'يجب أن يكون :attribute رقماً صحيحاً.',
            'exists'  => ':attribute غير موجود أو غير مخصص لهذا العقار.',
            'in'      => 'القيمة المختارة لـ :attribute غير مسموح بها.',
        ];
    }
}
