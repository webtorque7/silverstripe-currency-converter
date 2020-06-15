#SilverStripe Currency Converter

Simple service for converting currencies for SilverStripe 4+

Full credits go to: [webtorque7](https://github.com/webtorque7/silverstripe-currency-converter). I've
merely upgraded the module to be compatible with SilverStripe 4+ and made a few slight improvements.

## Installation

Composer

```
composer require webtorque/currency-converter
```

## Usage

To convert $99.99 NZD to USD

```php
$convertedAmount = CurrencyConverter::get_converter()->convert(99.99, 'NZD', 'USD');
```

## Exchange rates

Default implementation uses an xml feed at http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml. Other sources and be
created by inheriting from CurrencyConverter and implementing the retrieveCurrencies function.

Then change the `converter` config option on CurrencyConverter e.g.

```yml
Zanderwar\CurrencyConverter\CurrencyConverter:
  converter: MyCurrencyConverter
```

Alternatively, you can pass the name of the class into get_converter e.g.

```php
$convertedAmount = CurrencyConverter::getConverter(MyCurrencyConverter::class)->convert(9999, 'NZD', 'USD');
```
