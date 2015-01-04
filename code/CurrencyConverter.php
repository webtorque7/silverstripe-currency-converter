<?php

/**
 * Base class for Currency Converter
 *
 * Currencies should be stored in associative array with the currency code as the key
 *
 * e.g.
 *
 * array(
 *  'NZD' => '1.5',
 *  'USD' => '1.2'
 * );
 */
abstract class CurrencyConverter extends Object
{
	private static $converter = 'EuropaXMLCurrencyConverter';
	private $cache = null;

	protected $currencies = array();

	/**
	 * @param string $converter
	 * @return CurrencyConverter
	 */
	public static function get_converter($converter = '') {
		if (!$converter) {
			$converter = self::config()->converter;
		}

		return Injector::inst()->create($converter);
	}

	public function getCurrencies() {
		return $this->currencies;
	}

	public function setCurrencies($currencies) {
		$this->currencies = $currencies;
	}

	/**
	 * @return Zend_Cache_Frontend
	 */
	public function getCache() {
		if (!$this->cache) {
			$this->cache = SS_Cache::factory('CurrencyConverter');
		}

		return $this->cache;
	}

	/**
	 * Load currencies from Cache
	 *
	 * @return mixed
	 */
	public function loadFromCache() {
		return ($cached = $this->getCache()->load($this->getCacheKey()))
			? unserialize($cached)
			: null;
	}

	/**
	 * Save currencies to cache
	 *
	 * @param $currencies
	 * @return mixed
	 */
	public function saveToCache($currencies) {
		return $this->getCache()->save(serialize($currencies), $this->getCacheKey());
	}

	private function getCacheKey() {
		return __CLASS__ . 'Currencies';
	}

	/**
	 * Convert a value from one currency to another
	 *
	 * @param $value float Monetary value to convert
	 * @param $base float Base currency the value is in
	 * @param $new float Currency to convert value to
	 * @return float
	 */
	public function convert($value, $base, $new) {
		if (empty($this->currencies)) {
			$this->loadCurrencies();
		}

		//calculate rates
		$conversionRate = $this->getExchangeRate($base, $new);

		//do conversion
		return $value * $conversionRate;
	}

	/**
	 * Get the exchange rate for converting from one currency to another
	 *
	 * @param $base float Base currency to convert from
	 * @param $new float Currency to convert to
	 * @return float
	 */
	public function getExchangeRate($base, $new) {
		$baseRate = $this->rateForCurrency($base);
		$newRate = $this->rateForCurrency($new);
		return 1 / $baseRate * $newRate;
	}

	/**
	 * Load currencies, checks cache first, otherwise calls retreiveCurrencies
	 */
	public function loadCurrencies($force = false) {
		if ($force || !($currencies = $this->loadFromCache())) {
			$currencies = $this->retrieveCurrencies();
			$this->saveToCache($currencies);
		}

		$this->currencies = $currencies;
	}

	/**
	 * Returns the rate for the passed currency
	 *
	 * @param $currency string Currency code to look up
	 * @return float
	 * @throws Exception
	 */
	public function rateForCurrency($currency) {
		if ($currency === $this->getBaseCurrency()) {
			return 1;
		}

		if (empty($this->currencies)) {
			$this->loadCurrencies();
		};
		if (!$this->currencies[$currency]) {
			throw new Exception("Currency {$currency} not supported");
		}

		return $this->currencies[$currency];
	}

	/**
	 * Load currencies from source
	 *
	 * @return mixed
	 */
	public abstract function retrieveCurrencies();

	/**
	 * The base currency for the returned currencies
	 *
	 * @return string
	 */
	public abstract function getBaseCurrency();
}