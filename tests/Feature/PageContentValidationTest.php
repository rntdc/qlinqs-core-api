<?php

use App\Http\Requests\ValidatePageContentRequest;
use App\Validation\PageContentRules;
use Database\Factories\PageFactory;
use Illuminate\Support\Facades\Validator;

function validPageContent(): array
{
    return [
        'header' => ['name' => 'Ana', 'bio' => 'Studio owner'],
        'socialIcons' => [
            ['platform' => 'instagram', 'value' => 'ana.studio'],
        ],
        'blocks' => [
            [
                'id' => 'blk_1',
                'kind' => 'atomic',
                'type' => 'link',
                'layout' => 'button',
                'hidden' => false,
                'card' => [
                    'title' => 'Book now',
                    'link' => ['kind' => 'url', 'href' => 'https://example.com'],
                ],
            ],
        ],
    ];
}

it('passes a valid minimal content payload', function () {
    $data = validPageContent();

    expect(Validator::make($data, PageContentRules::rules($data))->passes())->toBeTrue();
});

it('passes when socialIcons and blocks are present but empty', function () {
    $data = [
        'header' => ['name' => 'Ana'],
        'socialIcons' => [],
        'blocks' => [],
    ];

    expect(Validator::make($data, PageContentRules::rules($data))->passes())->toBeTrue();
});

it('fails when socialIcons or blocks is entirely missing', function () {
    $data = ['header' => ['name' => 'Ana']];

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('socialIcons'))->toBeTrue();
    expect($validator->errors()->has('blocks'))->toBeTrue();
});

it('fails when header.name is missing', function () {
    $data = validPageContent();
    unset($data['header']['name']);

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('header.name'))->toBeTrue();
});

it('rejects a container block kind', function () {
    $data = validPageContent();
    $data['blocks'][0]['kind'] = 'container';

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('blocks.0.kind'))->toBeTrue();
});

it('rejects post-v1 container block types', function (string $type) {
    $data = validPageContent();
    $data['blocks'][0]['type'] = $type;

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('blocks.0.type'))->toBeTrue();
})->with(['carousel', 'grid']);

it('rejects an unknown block type rather than accepting it silently', function () {
    $data = validPageContent();
    $data['blocks'][0]['type'] = 'nonsense';

    expect(Validator::make($data, PageContentRules::rules($data))->fails())->toBeTrue();
});

it('enforces the layout enum only for link blocks', function () {
    $data = validPageContent();
    $data['blocks'][0]['layout'] = 'not-a-real-layout';

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('blocks.0.layout'))->toBeTrue();
});

it('accepts any non-empty layout string for non-link block types', function () {
    $data = validPageContent();
    $data['blocks'][0]['type'] = 'text';
    $data['blocks'][0]['layout'] = 'anything-goes';

    expect(Validator::make($data, PageContentRules::rules($data))->passes())->toBeTrue();
});

it('rejects hidden when it is not a boolean', function () {
    $data = validPageContent();
    $data['blocks'][0]['hidden'] = 'no';

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('blocks.0.hidden'))->toBeTrue();
});

it('requires link.href and link.kind when card.link is present', function () {
    $data = validPageContent();
    $data['blocks'][0]['card']['link'] = ['kind' => 'url'];

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('blocks.0.card.link.href'))->toBeTrue();
});

it('requires image.source and image.value when card.image is present', function () {
    $data = validPageContent();
    $data['blocks'][0]['card']['image'] = ['value' => 'asset-id'];

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('blocks.0.card.image.source'))->toBeTrue();
});

it('rejects an out-of-range corner override', function () {
    $data = validPageContent();
    $data['blocks'][0]['card']['overrides'] = ['corner' => 150];

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('blocks.0.card.overrides.corner'))->toBeTrue();
});

it('rejects an invalid tactile override value', function () {
    $data = validPageContent();
    $data['blocks'][0]['card']['overrides'] = ['tactile' => 'chrome'];

    $validator = Validator::make($data, PageContentRules::rules($data));

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('blocks.0.card.overrides.tactile'))->toBeTrue();
});

it('accepts align and size overrides on a card (block-only fields)', function () {
    $data = validPageContent();
    $data['blocks'][0]['card']['overrides'] = ['align' => 'center', 'size' => 'large'];

    expect(Validator::make($data, PageContentRules::rules($data))->passes())->toBeTrue();
});

it('validates the Page factory content output', function () {
    $content = PageFactory::new()->definition()['content'];

    expect(Validator::make($content, PageContentRules::rules($content))->passes())->toBeTrue();
});

it('the ValidatePageContentRequest wrapper exposes equivalent rules to PageContentRules', function () {
    $data = validPageContent();

    $request = new ValidatePageContentRequest;
    $request->replace($data);

    expect($request->rules())->toEqual(PageContentRules::rules($data));
});
