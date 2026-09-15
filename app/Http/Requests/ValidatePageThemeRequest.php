<?php

namespace App\Http\Requests;

use App\Validation\PageThemeRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Thin controller-facing wrapper around PageThemeRules — the reusable rule
 * set lives there so it can also be validated directly (e.g. in tests or
 * from a job) without an HTTP request.
 */
class ValidatePageThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return PageThemeRules::rules();
    }
}
