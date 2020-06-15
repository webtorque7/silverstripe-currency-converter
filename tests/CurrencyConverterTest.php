<?php

namespace Zanderwar\CurrencyConverter\Tests;

use Zanderwar\CurrencyConverter\CurrencyConverter;

/**
 * Possible actions:
 * - action_save
 * - action_publish
 * - action_unpublish
 * - action_delete
 * - action_deletefromlive
 * - action_rollback
 * - action_revert
 *
 * @package cms
 * @subpackage tests
 */
class CurrencyConverterTest extends \SilverStripe\Dev\FunctionalTest
{
    public function testCurrencyLoading()
    {
        $converter = \Zanderwar\CurrencyConverter\CurrencyConverter::getConverter();

        $rate = $converter->rateForCurrency('NZD');

        $this->assertNotNull($rate);
    }

    public function testCurrencyConversion()
    {
        $converter = CurrencyConverter::getConverter();

        $converter->setCurrencies(array(
            'NZD' => 2,
            'USD' => 5
        ));

        $convertedAmount = $converter->convert(1, 'NZD', 'USD');

        $this->assertEquals($convertedAmount, 2.5);
    }

    public function testNotSupported()
    {
        $this->expectException('\Exception');

        $converter = CurrencyConverter::getConverter();

        $converter->setCurrencies(array(
            'NZD' => 2,
            'USD' => 5
        ));

        $converter->rateForCurrency('YEN');
    }
}
