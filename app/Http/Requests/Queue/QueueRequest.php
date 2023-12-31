<?php

namespace App\Http\Requests\Queue;

use Illuminate\Foundation\Http\FormRequest;

class QueueRequest extends FormRequest
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
            "patient_id" =>  ['required', 'exists:patients,id'],
            "doctor_id" =>  ['required', 'exists:doctors,id'],
            "complaint" =>  ['required', 'string', 'max:255'],
            "status" =>  ['nullable', 'string', 'max:255'],
        ];
    }
}
