<?php
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
class CurrencyConverterTest extends FunctionalTest {

	//protected static $fixture_file = 'SiteTreeActionsTest.yml';

	public function testCurrencyLoading() {

		$converter = CurrencyConverter::get_converter();

		$rate = $converter->rateForCurrency('NZD');

		$this->assertNotNull($rate);
	}

	public function testCurrencyConversion() {
		$converter = CurrencyConverter::get_converter();

		$converter->setCurrencies(array(
			'NZD' => 2,
			'USD' => 5
		));

		$convertedAmount = $converter->convert(1, 'NZD', 'USD');

		$this->assertEquals($convertedAmount, 2.5);
	}

	public function testNotSupported() {
		$this->setExpectedException('Exception');

		$converter = CurrencyConverter::get_converter();

		$converter->setCurrencies(array(
			'NZD' => 2,
			'USD' => 5
		));

		$converter->rateForCurrency('YEN');
	}
}

