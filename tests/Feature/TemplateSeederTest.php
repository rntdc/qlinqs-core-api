<?php

use App\Models\Template;
use App\Validation\PageThemeRules;
use Database\Seeders\TemplateSeeder;
use Illuminate\Support\Facades\Validator;

it('creates the starter templates and is idempotent on re-run', function () {
    $this->seed(TemplateSeeder::class);
    $count = Template::count();

    expect($count)->toBeGreaterThanOrEqual(3)->toBeLessThanOrEqual(5);

    $this->seed(TemplateSeeder::class);

    expect(Template::count())->toBe($count);
});

it('seeds only unique template names', function () {
    $this->seed(TemplateSeeder::class);

    $names = Template::pluck('name');

    expect($names->unique())->toHaveCount($names->count());
});

it('seeds only themes that pass PageThemeRules', function () {
    $this->seed(TemplateSeeder::class);

    Template::all()->each(function (Template $template) {
        $validator = Validator::make($template->theme, PageThemeRules::rules());

        expect($validator->passes())->toBeTrue(
            "Template '{$template->name}' theme is invalid: ".$validator->errors(),
        );
    });
});
