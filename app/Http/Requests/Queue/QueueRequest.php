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
            "complaint" =>  ['required', 'string', 'max:255'],
            "patient_id" =>  ['required', 'exists:patients,id'],
            "doctor_id" =>  ['nullable', 'exists:doctors,id'],
            "blood_pressure" =>  ['nullable', 'string', 'max:255'],
            "height" =>  ['nullable'],
            "weight" =>  ['nullable'],
            "temperature" =>  ['nullable'],
            "status" =>  ['nullable', 'string', 'max:255'],
            "note" =>  ['nullable', 'string'],
            "tindakan" =>  ['nullable', 'string'],
        ];
    }

    /**
     * Message
     */
    public function messages(): array
    {
        return [
            'complaint.required' => 'Form keluhan wajib diisi!',
            'patient_id.required' => 'Pilihan Keluarga wajib diisi!',
            'complaint.string' => 'Form keluhan harus berupa teks!',
            'complaint.max' => 'Panjang teks keluhan hanya 255 karakter!',
        ];
    }

    /**
     * Message
     */
    public function attributes(): array
    {
        return [
            "complaint" => "keluhan"
        ];
    }
}
