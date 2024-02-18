<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'   => ['nullable', 'integer', 'exists:users,id'],
            'record_no' => ['nullable', 'string', 'max:50',],
            'fullname'  => ['required', 'string', 'max:255'],
            'gender'    => ['required', 'string', 'max:20'],
            'birthday'  => ['nullable', 'date'],
            'age'       => ['nullable', 'integer',],
            'phone'     => ['nullable', 'string', 'max:20'],
            'address'   => ['required', 'string', 'max:255'],
        ];
    }
}
