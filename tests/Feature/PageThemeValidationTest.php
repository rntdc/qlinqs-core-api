<?php

use App\Http\Requests\ValidatePageThemeRequest;
use App\Validation\PageThemeRules;
use Database\Factories\PageFactory;
use Database\Factories\TemplateFactory;
use Illuminate\Support\Facades\Validator;

function validPageTheme(): array
{
    return [
        'page' => [
            'header' => ['layout' => 'classic'],
            'background' => ['type' => 'solid'],
            'profilePicture' => [],
        ],
        'blockDefaults' => [
            'tactile' => 'flat',
        ],
        'fonts' => [
            'titleFont' => 'Inter',
            'textFont' => 'Inter',
        ],
        'palette' => [
            'background' => '#FFFFFF',
            'text' => '#111111',
            'surface' => '#F5F5F5',
            'onSurface' => '#111111',
            'accent' => '#6D28D9',
            'onAccent' => '#FFFFFF',
        ],
    ];
}

it('passes a valid minimal theme payload', function () {
    expect(Validator::make(validPageTheme(), PageThemeRules::rules())->passes())->toBeTrue();
});

it('passes when blockDefaults, fonts and profilePicture are empty objects', function () {
    $data = validPageTheme();
    $data['blockDefaults'] = [];
    $data['fonts'] = [];
    $data['page']['profilePicture'] = [];

    expect(Validator::make($data, PageThemeRules::rules())->passes())->toBeTrue();
});

it('fails when a required palette key is missing', function () {
    $data = validPageTheme();
    unset($data['palette']['accent']);

    $validator = Validator::make($data, PageThemeRules::rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('palette.accent'))->toBeTrue();
});

it('fails when page.background is entirely missing', function () {
    $data = validPageTheme();
    unset($data['page']['background']);

    $validator = Validator::make($data, PageThemeRules::rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('page.background'))->toBeTrue();
});

it('rejects align and size inside blockDefaults', function (string $field) {
    $data = validPageTheme();
    $data['blockDefaults'][$field] = 'left';

    $validator = Validator::make($data, PageThemeRules::rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has("blockDefaults.$field"))->toBeTrue();
})->with(['align', 'size']);

it('rejects an out-of-range corner value in blockDefaults', function () {
    $data = validPageTheme();
    $data['blockDefaults']['corner'] = 150;

    $validator = Validator::make($data, PageThemeRules::rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('blockDefaults.corner'))->toBeTrue();
});

it('rejects an invalid tactile value in blockDefaults', function () {
    $data = validPageTheme();
    $data['blockDefaults']['tactile'] = 'chrome';

    expect(Validator::make($data, PageThemeRules::rules())->fails())->toBeTrue();
});

it('rejects background types outside the v1 enum', function (string $type) {
    $data = validPageTheme();
    $data['page']['background']['type'] = $type;

    $validator = Validator::make($data, PageThemeRules::rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('page.background.type'))->toBeTrue();
})->with(['split', 'image', 'animated', 'nonsense']);

it('rejects an invalid header layout', function () {
    $data = validPageTheme();
    $data['page']['header']['layout'] = 'banner';

    $validator = Validator::make($data, PageThemeRules::rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('page.header.layout'))->toBeTrue();
});

it('validates the Page factory theme output', function () {
    $theme = PageFactory::defaultTheme();

    expect(Validator::make($theme, PageThemeRules::rules())->passes())->toBeTrue();
});

it('validates the Template factory theme output', function () {
    $theme = TemplateFactory::new()->definition()['theme'];

    expect(Validator::make($theme, PageThemeRules::rules())->passes())->toBeTrue();
});

it('the ValidatePageThemeRequest wrapper exposes equivalent rules to PageThemeRules', function () {
    $request = new ValidatePageThemeRequest;
    $request->replace(validPageTheme());

    expect($request->rules())->toEqual(PageThemeRules::rules());
});
