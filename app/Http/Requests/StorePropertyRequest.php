<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', \App\Models\Property::class);
    }

    public function rules(): array
    {
        $user      = $this->user();
        $officeId  = $user->offices()->value('id');

        return [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'price'          => ['required', 'numeric', 'min:0'],
            'currency'       => ['required', Rule::enum(Currency::class)],
            'purpose'        => ['required', Rule::enum(PropertyPurpose::class)],
            'type'           => ['required', Rule::enum(PropertyType::class)],
            'status'         => [
                'sometimes',
                Rule::enum(PropertyStatus::class),
                // Only admins can set status directly; office users always start at pending
                Rule::when(
                    $user->role !== UserRole::Admin,
                    [Rule::in([PropertyStatus::Pending->value])]
                ),
            ],
            'area'           => ['required', 'numeric', 'min:1'],
            'governorate_id' => ['required', 'integer', 'exists:governorates,id'],
            'owner_id'       => [
                'required',
                'integer',
                // Owner must belong to this user's office
                Rule::exists('owners', 'id')->where('office_id', $officeId),
            ],
        ];
    }

    // office_id is never accepted from the request body.
    // It is resolved from the authenticated user's office in the service.
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
            'required'       => 'حقل :attribute مطلوب.',
            'string'         => 'يجب أن يكون :attribute نصاً.',
            'max'            => 'يجب ألا يتجاوز :attribute :max حرفاً.',
            'numeric'        => 'يجب أن يكون :attribute رقماً.',
            'min'            => 'يجب أن يكون :attribute على الأقل :min.',
            'integer'        => 'يجب أن يكون :attribute رقماً صحيحاً.',
            'exists'         => ':attribute غير موجود أو غير مخصص لمكتبك.',
            'in'             => 'القيمة المختارة لـ :attribute غير مسموح بها.',
        ];
    }
}
