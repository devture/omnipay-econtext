<?php
namespace Omnipay\Econtext\Message;

use Omnipay\Common\Message\AbstractRequest;
use Guzzle\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CreateCardMerchantRequest extends BaseMerchantRequest {

	/**
	 * @var string - the `cardReference` string that we'll attempt to create
	 *
	 * That is to say, `cardReference` generation happens here (not on the API server).
	 * We should be careful not to generate duplicates. Especially since values cannot be reused.
	 * (creating and deleting a card leaves a permanent trace on the server and makes said card reference non-reusable).
	 */
	private $generatedCardReference;

	public function __construct(ClientInterface $httpClient, HttpRequest $httpRequest) {
		parent::__construct($httpClient, $httpRequest);

		$this->generatedCardReference = (string) \Ramsey\Uuid\Uuid::uuid1();
	}

	/**
	 * Returns the here-generated card reference id that we attempt to create on the server
	 *
	 * @return string
	 */
	public function getGeneratedCardReference() {
		return $this->generatedCardReference;
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Econtext\Message\BaseMerchantRequest::getResponseClass()
	 */
	public function getResponseClass() {
		return '\Omnipay\Econtext\Message\CreateCardMerchantResponse';
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Common\Message\MessageInterface::getData()
	 * @throws \Omnipay\Common\Exception\InvalidRequestException - when the credit card information is missing
	 * @throws \Omnipay\Common\Exception\InvalidCreditCardException - when the credit card information is invalid
	 */
	public function getData() {
		//Checks if a card parameter is provided.
		//May throw \Omnipay\Common\Exception\InvalidRequestException
		$this->validate('card');

		if (!($this->getCard() instanceof \Omnipay\Common\CreditCard)) {
			throw new \Omnipay\Common\Exception\InvalidRequestException('Invalid credit card object.');
		}

		if (!$this->getTestMode()) {
			//May throw \Omnipay\Common\Exception\InvalidCreditCardException
			$this->getCard()->validate();
		}

		$data = array();
		$data['paymtCode'] = 'C20';
		$data['fncCode'] = '01';
		$data['cduserID'] = $this->generatedCardReference;
		$data = array_merge($data, \Omnipay\Econtext\Util\Helper::cardToApiParameters($this->getCard()));

		return $data;
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Common\Message\AbstractRequest::send()
	 * @throws \Omnipay\Common\Exception\InvalidRequestException - when the credit card information is missing
	 * @throws \Omnipay\Common\Exception\InvalidCreditCardException - when the credit card information is invalid
	 * @throws \Omnipay\Common\Exception\InvalidResponseException - when the API call fails or returns bad data
	 * @throws \Omnipay\Econtext\Exception\InvalidCredentialsException - if invalid gateway credentials were used (fragile)
	 * @return \Omnipay\Econtext\Message\CreateCardMerchantResponse
	 */
	public function send() {
		return parent::send();
	}

}
