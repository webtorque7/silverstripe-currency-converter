<?php

namespace WebTorque\CurrencyConverter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class EuropaXMLCurrencyConverter extends CurrencyConverter
{
    private static $base_url = 'http://www.ecb.europa.eu';
    private static $path = '/stats/eurofxref/eurofxref-daily.xml';

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function retrieveCurrencies()
    {
        $client = new Client(['base_uri' => $this->config()->get('base_url')]);

        try {
            $response = $client->request('GET', $this->config()->get('path'));
        } catch (GuzzleException $e) {
            $this->currencies = [];
            throw new \Exception($e->getMessage());
        }

        $xml = $response->getBody();
        $xml = simplexml_load_string($xml);

        $currencies = [];
        foreach ($xml->Cube->Cube->Cube as $currency) {
            $currencies[(string)$currency['currency']] = (float)$currency['rate'];
        }

        return $currencies;
    }

    /**
     * This is the base currency respective of the service, not your base currency
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        return 'EUR';
    }
}
