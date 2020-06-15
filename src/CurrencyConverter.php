<?php

namespace WebTorque\CurrencyConverter;

use Exception;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBCurrency;

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
class CurrencyConverter
{
    use Configurable, Injectable;

    private static $converter = EuropaXMLCurrencyConverter::class;

    private $cache = null;

    protected $currencies = [];

    /**
     * @param string $converter
     * @return CurrencyConverter
     */
    public static function getConverter($converter = '')
    {
        if (!$converter) {
            $converter = static::config()->get('converter');
        }

        return Injector::inst()->create($converter);
    }

    public function getCurrencies()
    {
        return $this->currencies;
    }

    public function setCurrencies($currencies)
    {
        $this->currencies = $currencies;
    }

    /**
     * @return CacheInterface
     */
    public function getCache()
    {
        if (!$this->cache) {
            $this->cache = Injector::inst()->get(CacheInterface::class . '.CurrencyConverterFactory');
        }

        return $this->cache;
    }

    /**
     * Load currencies from Cache
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function loadFromCache()
    {
        return ($cached = $this->getCache()->get($this->getCacheKey()))
            ? unserialize($cached)
            : null;
    }

    /**
     * Save currencies to cache
     *
     * @param $currencies
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function saveToCache($currencies)
    {
        $currencies = serialize($currencies);

        return $this->getCache()->set($this->getCacheKey(), $currencies);
    }

    private function getCacheKey()
    {
        return basename(__CLASS__) . 'Currencies';
    }

    /**
     * Convert a value from one currency to another
     *
     * @param $value float Monetary value to convert
     * @param $base float Base currency the value is in
     * @param $new float Currency to convert value to
     * @return DBCurrency|\SilverStripe\ORM\FieldType\DBDecimal
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function convert($value, $base, $new)
    {
        if (empty($this->currencies)) {
            $this->loadCurrencies();
        }

        //calculate rates
        $conversionRate = $this->getExchangeRate($base, $new);

        //do conversion
        return DBCurrency::create()->setValue($value * $conversionRate);
    }

    /**
     * Get the exchange rate for converting from one currency to another
     *
     * @param $base float Base currency to convert from
     * @param $new float Currency to convert to
     * @return float
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getExchangeRate($base, $new)
    {
        $baseRate = $this->rateForCurrency($base);
        $newRate = $this->rateForCurrency($new);
        return 1 / $baseRate * $newRate;
    }

    /**
     * Load currencies, checks cache first, otherwise calls retreiveCurrencies
     *
     * @param bool $force
     * @throws InvalidArgumentException
     */
    public function loadCurrencies($force = false)
    {
        if ($force || !($currencies = $this->loadFromCache())) {
            $currencies = static::getConverter()->retrieveCurrencies();
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
     * @throws InvalidArgumentException
     */
    public function rateForCurrency($currency)
    {
        if ($currency === static::getConverter()->getBaseCurrency()) {
            return 1;
        }

        if (empty($this->currencies)) {
            $this->loadCurrencies();
        };

        if (!$this->currencies[$currency]) {
            throw new \Exception("Currency {$currency} not supported");
        }

        return $this->currencies[$currency];
    }

    /**
     * Load currencies from source
     *
     * @return mixed
     */
    public function retrieveCurrencies()
    {
        throw new \RuntimeException('You must override this method in a converter');
    }

    /**
     * The base currency for the returned currencies
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        throw new \RuntimeException('You must override this method in a converter');
    }
}
