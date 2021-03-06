<?php

declare(strict_types=1);

namespace tpr\tests;

use PHPUnit\Framework\TestCase;
use tpr\App;
use tpr\Config;
use tpr\core\Dispatch;

/**
 * @internal
 * @coversNothing
 */
class DispatchTest extends TestCase
{
    protected function setUp(): void
    {
        App::drive()->getConfig()->controller_rule = '\\tpr\\tests\\DispatchTest';
        parent::setUp();
    }

    public function testExec()
    {
        $dispatch = new Dispatch('app');
        Config::set('app.route_class_name', self::class);
        $res = $dispatch->dispatch('', '', 'doExec');
        $this->assertEquals('exec result', $res);
    }

    public function doExec(): string
    {
        return 'exec result';
    }
}
