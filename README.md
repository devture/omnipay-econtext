# Omnipay: Econtext

**Econtext driver for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements [Econtext](http://www.econtext.jp) support for Omnipay.


## Preface

This driver is still in early development.
**Do not use in production (yet).**


## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "devture/omnipay-econtext": "@dev"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update


## Basic Usage

The following gateways are provided by this package:

* Econtext_Merchant (Econtext Merchant API)

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay) repository.


### Initializing the gateway

```php
$gateway = \Omnipay\Omnipay::create('Econtext_Merchant');
$gateway->initialize(array(
	'siteId' => 'Econtext-provided shopId',
	'siteCheckCode' => 'Econtext-provided chkCode',
	'testMode' => true, //or set to true for production
));
```


### Create a Card (stores it on the Econtext server)

```php
$creditCard = new \Omnipay\Common\CreditCard(array(
	'firstName' => '寛',
	'lastName' => '山田',
	'number' => '4980111111111111',
	'cvv' => '123',
	'expiryMonth' => '1',
	'expiryYear' => '2017',
	'email' => 'testcard@example.com',
));

$transaction = $gateway->createCard(array('card' => $creditCard));

//Don't forget to catch some exceptions here
$transactionResponse = $transaction->send();

var_dump($transactionResponse->isSuccessful());
var_dump($transactionResponse->getCardReference());
```


### Retrieve a stored (partial) Card from the Econtext server

```php
$cardReference = 'from createCard / $transactionResponse->getCardReference()';
$transaction = $gateway->retrieveCard(array('cardReference' => $cardReference));

//Don't forget to catch some exceptions here
$transactionResponse = $transaction->send();

var_dump($transactionResponse->isSuccessful());

//You don't really have the full credit card information.
//Pretty much just the last 4 digits of the number are exposed to you.
var_dump($transactionResponse->getCard()->getNumberLast4());
```


### Delete a Card stored on the Econtext server

```php
$cardReference = 'from createCard / $transactionResponse->getCardReference()';
$transaction = $gateway->deleteCard(array('cardReference' => $cardReference));

//Don't forget to catch some exceptions here
//Idempotent - feel free to delete as many times as you wish!
$transactionResponse = $transaction->send();

var_dump($transactionResponse->isSuccessful());
```


### Purchase using an inline-provided Card

```php
$creditCard = new \Omnipay\Common\CreditCard(array(
	'firstName' => '寛',
	'lastName' => '山田',
	'number' => '4980111111111111',
	'cvv' => '123',
	'expiryMonth' => '1',
	'expiryYear' => '2017',
	'email' => 'testcard@example.com',
));

$transaction = $gateway->purchase(array(
	'card' => $creditCard,
	'amount' => 500,
	'description' => 'Noodles',
));

//Don't forget to catch some exceptions here
$transactionResponse = $transaction->send();

var_dump($transactionResponse->isSuccessful());

//Keep your transaction reference if you want to perform refunds later
var_dump($transactionResponse->getTransactionReference());

//As a side-effect, the card gets stored on the Econtext server for you.
var_dump($transactionResponse->getCardReference());
```


### Purchase using a previously stored Card

```php
$cardReference = 'from createCard / $transactionResponse->getCardReference()';

$transaction = $gateway->purchase(array(
	'cardReference' => $cardReference,
	'amount' => 500,
	'description' => 'Noodles',
));

//Don't forget to catch some exceptions here
$transactionResponse = $transaction->send();

var_dump($transactionResponse->isSuccessful());

//Keep your transaction reference if you want to perform refunds later
var_dump($transactionResponse->getTransactionReference());
```


### Refund a purchase

```php
$transactionReference = 'from purchase / $transactionResponse->getTransactionReference()';

$transaction = $gateway->refund(array(
	'transactionReference' => $transactionReference,
));

//Don't forget to catch some exceptions here
//NOT idempotent - subsequent refund() calls will fail
$transactionResponse = $transaction->send();

var_dump($transactionResponse->isSuccessful());
```


## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/devture/omnipay-econtext/issues),
or better yet, fork the library and submit a pull request.
