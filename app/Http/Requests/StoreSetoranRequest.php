<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSetoranRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.waste_category_id'=> ['required', 'exists:waste_categories,id'],
            'items.*.weight'           => ['required', 'numeric', 'min:0.01'],
            'note'                     => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                    => 'Harus ada minimal satu jenis sampah.',
            'items.min'                         => 'Harus ada minimal satu jenis sampah.',
            'items.*.waste_category_id.required'=> 'Kategori sampah wajib dipilih.',
            'items.*.waste_category_id.exists'  => 'Kategori sampah tidak valid.',
            'items.*.weight.required'           => 'Berat timbangan wajib diisi.',
            'items.*.weight.numeric'            => 'Berat harus berupa angka.',
            'items.*.weight.min'                => 'Berat minimal 0.01 kg.',
        ];
    }
}
