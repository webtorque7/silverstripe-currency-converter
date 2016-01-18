<?php
/**
 * Created by PhpStorm.
 * User: Conrad
 * Date: 2/01/15
 * Time: 2:02 PM
 */
use Guzzle\Http\Message\Request;

class EuropaXMLCurrencyConverter extends CurrencyConverter
{
    private static $base_url = 'http://www.ecb.europa.eu';
    private static $path = '/stats/eurofxref/eurofxref-daily.xml';

    public function retrieveCurrencies()
    {
        $client = new Guzzle\Http\Client($this->config()->base_url);

        try {
            $request = $client->get($this->config()->path);
            $response = $request->send();
        } catch (Guzzle\Http\RequestException $e) {
            $this->currencies = array();
            throw new Exception($e->getMessage());
        }

        $xml = $response->xml();

        $currencies = array();
        foreach ($xml->Cube->Cube->Cube as $currency) {
            $currencies[(string)$currency['currency']] = (float)$currency['rate'];
        }

        return $currencies;
    }

    public function getBaseCurrency()
    {
        return 'EUR';
    }
}
