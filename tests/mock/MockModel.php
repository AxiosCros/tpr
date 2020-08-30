<?php

declare(strict_types=1);

namespace tpr\tests\mock;

use tpr\Model;

final class MockModel extends Model
{
    public string  $foo  = '';
    public ?int    $test = null;
    public array   $data = [];

    protected array $_rules = [
        'test' => 'required',
    ];

    protected array $_alias = [
        'data' => 'MockData',
    ];

    protected array $_messages = [
        'required' => 'required :attribute',
    ];
}
