<?php

namespace App\Http\Requests;

use App\Enums\TalentRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class TalentRequest
 *
 * @package App\Http\Requests
 */
class TalentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var int|null $talentId */
        $talentId = $this->route('talent') ?? null;

        if ($this->has('ayon_sync_status')) {
            return [
                'ayon_sync_status' => 'required|in:inactive,pending,synced,error',
            ];
        }

        return [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => [
                'required',
                'email',
                Rule::unique('talents', 'email')->ignore($talentId),
            ],
            'role' => ['required', Rule::in(array_column(TalentRole::cases(), 'value'))],
            'ayon_sync_status' => 'sometimes|in:inactive,pending,synced,error',
        ];
    }
}
