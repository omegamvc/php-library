<?php

it('can render valid_array_size_lesser validation')
    ->expect(vr()->valid_array_size_lesser(1))
    ->toEqual('valid_array_size_lesser,1')
;

it('can render invert valid_array_size_lesser validation')
    ->expect(vr()->not()->valid_array_size_lesser(1))
    ->toEqual('invert_valid_array_size_lesser,1')
;

$correct   = [
    'test1' => [1, 2, 3],
    'test2' => '',
];
$incorrect = ['test' => [1, 2]];

// validate with correct input field

it('can validate valid_array_size_lesser with correct input', function () use ($correct) {
    $val = new Validator\Validator($correct);

    $val->test1->valid_array_size_lesser(3);
    $val->test1->valid_array_size_lesser(4);
    $val->test2->valid_array_size_lesser(3);

    expect($val->is_valid())->toBeTrue();
});

it('can validate valid_array_size_lesser (not) with correct input', function () use ($correct) {
    $val = new Validator\Validator($correct);

    $val->test1->not->valid_array_size_lesser(3);
    $val->test1->not->valid_array_size_lesser(4);
    $val->test2->not->valid_array_size_lesser(3);

    expect($val->is_valid())->toBeFalse();
});

// validate with incorrect input field

it('can validate valid_array_size_lesser with incorrect input', function () use ($incorrect) {
    $val = new Validator\Validator($incorrect);

    $val->test->valid_array_size_lesser(1);

    expect($val->is_valid())->toBeFalse();
});

it('can validate regex (not) with incorrect input', function () use ($incorrect) {
    $val = new Validator\Validator($incorrect);

    $val->test->not->valid_array_size_lesser(1);

    expect($val->is_valid())->toBeTrue();
});
