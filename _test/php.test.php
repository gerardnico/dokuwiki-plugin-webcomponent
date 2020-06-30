<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

/**
 * Tests over the php configuration
 *
 * @group plugin_webcomponent
 * @group plugins
 */
final class PhpTest extends TestCase
{
    /**
     * There is no notice because of the bootstrap.php file of dokuwiki
     *
     * define('DOKU_E_LEVEL',E_ALL ^ E_NOTICE);
     * error_reporting(DOKU_E_LEVEL);
     *
     */
    public function testErrorLevel(): void
    {

        $value = E_ALL ^ E_NOTICE;
        /** @noinspection PhpUnitMisorderedAssertEqualsArgumentsInspection */
        $this->assertEquals($value, \error_reporting());

    }

}
