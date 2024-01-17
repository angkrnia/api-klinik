<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRequest extends FormRequest
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
            'fullname'     => ['required', 'string', 'max:255'],
            'phone'        => ['required', 'string', 'max:20'],
            'description'  => ['nullable', 'string', 'max:255'],
            'user_id'      => ['nullable', 'exists:users,id'],
            'schedule'     => ['nullable', 'array'],
            'schedule.*.day'    => ['required', 'string'],
            'schedule.*.start'  => ['required', 'string'],
            'schedule.*.end'    => ['required', 'string'],
        ];
    }
}
