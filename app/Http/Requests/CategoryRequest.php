<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CategoryRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        $category = $this->route('category');
        return [
            'name' => ['required','string','max:255', Rule::unique('categories','name')->ignore($category?->id)],
            'description' => ['nullable','string','max:5000'],
        ];
    }
}
