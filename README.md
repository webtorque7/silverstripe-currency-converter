[![Build Status](https://secure.travis-ci.org/webtorque7/silverstripe-currency-converter.svg?branch=master)](https://travis-ci.org/webtorque7/silverstripe-currency-converter)

#SilverStripe Currency Converter

Simple service for converting currencies. 0.1.* is for SilverStripe 3.0.*

## Installation

Install the module into a `currency-converter\` folder inside the webroot.

With composer - composer require webtorque7/inpage-modules

## Usage

To convert $9999 NZD to USD

```php
$convertedAmount = CurrencyConverter::get_converter()->convert(9999, 'NZD', 'USD');
```

## Exchange rates

Default implementation uses an xml feed at http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml. Other sources and be
created by inheriting from CurrencyConverter and implementing the retrieveCurrencies function.

Then change the `converter` config option on CurrencyConverter e.g.

```yml
CurrencyConverter:
  converter: MyCurrencyConverter
```

Alternatively, you can pass the name of the class into get_converter e.g.

```php
$convertedAmount = CurrencyConverter::get_converter('MyCurrencyConverter')->convert(9999, 'NZD', 'USD');
```
