<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\JsonResponse;

/**
 * Public, unauthenticated read of a page by profile slug — this is the
 * qlinqs.com/{slug} render endpoint. No view tracking or caching here yet.
 */
class PublicPageController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        // slug is citext in Postgres, so this comparison is already
        // case-insensitive at the DB level — no app-side lowercasing.
        $profile = Profile::with('page')->where('slug', $slug)->first();

        abort_unless($profile?->page, 404, "No page found for slug \"{$slug}\".");

        return response()->json([
            'slug' => $profile->slug,
            'content' => $profile->page->content,
            'theme' => $profile->page->theme,
        ]);
    }
}
