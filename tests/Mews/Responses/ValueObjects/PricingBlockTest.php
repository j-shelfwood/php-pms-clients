<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\PricingBlock;

it('maps pricing block from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/rates-getpricing.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $blockData = $mockData['CategoryPrices'][0];

    $block = PricingBlock::map($blockData);

    expect($block->resourceCategoryId)->toBe('44bd8ad0-e70b-4bd9-8445-ad7200d7c349')
        ->and($block->amountPrices)->toBeArray();
    if (!empty($block->amountPrices)) {
        expect($block->amountPrices[0])->toBeInstanceOf(\Shelfwood\PhpPms\Mews\Responses\ValueObjects\AmountPrice::class);
    }
});

it('throws exception on missing required field', function () {
    PricingBlock::map(['AmountPrices' => []]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);
