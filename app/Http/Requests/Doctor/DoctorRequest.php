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
            'avatar'       => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp'],
            'description'  => ['nullable', 'string', 'max:255'],
            'start_day'    => ['required', 'string', 'lowercase', 'max:20'],
            'end_day'      => ['required', 'string', 'lowercase', 'max:20'],
            'start_time'   => ['required', 'string', 'max:20'],
            'end_time'     => ['required', 'string', 'max:20'],
        ];
    }
}
