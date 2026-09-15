<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidatePageContentRequest;
use App\Http\Requests\ValidatePageThemeRequest;
use App\Models\Page;
use App\Models\Profile;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

/**
 * Edits THE page's content/theme. There's no auth yet, so every request
 * acts on a single fixed profile (config('qlinqs.dev_profile_slug')) — a
 * temporary stand-in until real auth/ownership exists.
 */
class PageController extends Controller
{
    /**
     * Top-level content/theme keys per qlinqs-estrutura-de-dados.md §3/§7.
     * Persistence is restricted to these (dropping unknown top-level keys),
     * but nested data underneath each is saved as sent — the validators
     * deliberately don't enumerate every optional nested field (§7.1), so
     * using $request->validated() here would silently strip anything
     * allowed-but-unruled instead of just what's actually invalid.
     */
    private const CONTENT_KEYS = ['header', 'socialIcons', 'blocks'];

    private const THEME_KEYS = ['page', 'blockDefaults', 'fonts', 'palette'];

    public function updateContent(ValidatePageContentRequest $request): JsonResponse
    {
        $page = $this->demoPage();
        $page->update(['content' => Arr::only($request->all(), self::CONTENT_KEYS)]);

        return $this->respond($page->refresh());
    }

    public function updateTheme(ValidatePageThemeRequest $request): JsonResponse
    {
        $page = $this->demoPage();
        $page->update(['theme' => Arr::only($request->all(), self::THEME_KEYS)]);

        return $this->respond($page->refresh());
    }

    public function applyTemplate(Template $template): JsonResponse
    {
        $page = $this->demoPage();
        $page->update(['theme' => $template->theme]);

        return $this->respond($page->refresh());
    }

    protected function demoPage(): Page
    {
        $slug = config('qlinqs.dev_profile_slug', 'teste');

        $profile = Profile::where('slug', $slug)->first();

        abort_unless($profile?->page, 404, "No page found for profile slug \"{$slug}\". Run `php artisan db:seed` first.");

        return $profile->page;
    }

    protected function respond(Page $page): JsonResponse
    {
        return response()->json([
            'id' => $page->id,
            'content' => $page->content,
            'theme' => $page->theme,
        ]);
    }
}
