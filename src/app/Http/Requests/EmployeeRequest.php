<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Data preparation before validation.
     */
    protected function prepareForValidation(): void
    {
        // Set user_id based on the current user making the request
        $this->merge(['stakeholder_id' => Auth::id()]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {

        if ($this->method() == 'PUT') {
            return [
                'route_id' => ['sometimes', 'required', 'uuid', 'exists:entities,id'],
                'department_id' => ['sometimes', 'required', 'uuid', 'exists:entities,id'],
                'station_id' => ['sometimes', 'required', 'uuid', 'exists:entities,id'],
                'public_id' => ['sometimes', 'required', 'string', 'max::255', 'unique:employees,public_id'],
                'is_leader_shop' => ['sometimes', 'required', 'boolean'],
                'phone_number' => ['sometimes', 'required', 'string', 'unique:employees,phone_number'],
            ];

        }
        return [
            'stakeholder_id' => ['required', 'uuid', 'exists:stakeholders,id'],
            'route_id' => ['required', 'uuid', 'exists:entities,id'],
            'department_id' => ['required', 'uuid', 'exists:entities,id'],
            'station_id' => ['required', 'uuid', 'exists:entities,id'],
            'public_id' => ['required', 'string', 'max::255', 'unique:employees,public_id'],
            'is_leader_shop' => ['required', 'boolean'],
            'phone_number' => ['required', 'string', 'unique:employees,phone_number'],
        ];

    }


    /*
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * @param array
     */
    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'stakeholder_id' => 'Stakeholder',
            'route_id' => 'Route',
            'department_id' => 'Department',
            'station_id' => 'Station',
            'public_id' => 'Public',
            'is_leader_shop' => 'leader shop',
            'slug' => 'Slug',
            'phone_number' => 'Phone number'
        ];
    }
}