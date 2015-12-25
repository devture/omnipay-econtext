<?php
namespace Omnipay\Econtext\Message;

use Guzzle\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class PurchaseMerchantRequest extends BaseMerchantRequest {

	/**
	 * @var string - the `orderID` string that we'll attempt to create/purchase
	 *
	 * That is to say, `orderID` generation happens here (not on the API server).
	 * We should be careful not to generate duplicates. Especially since values cannot be reused.
	 * (creating and deleting an order leaves a permanent trace on the server and makes said orderID non-reusable).
	 */
	private $generatedOrderId;

	/**
	 * @var string - the `cardReference` string that we'll attempt to create
	 */
	private $generatedCardReference;

	public function __construct(ClientInterface $httpClient, HttpRequest $httpRequest) {
		parent::__construct($httpClient, $httpRequest);

		$this->generatedOrderId = (string) \Ramsey\Uuid\Uuid::uuid1();

		//May or may not be used below, depending on whether `card` charging is done.
		//Not needed for `cardReference` charges.
		$this->generatedCardReference = (string) \Ramsey\Uuid\Uuid::uuid1();
	}

	/**
	 * Returns the here-generated card reference id that we attempt to create on the server
	 *
	 * @return string
	 */
	public function getGeneratedOrderId() {
		return $this->generatedOrderId;
	}

	/**
	 * Returns the here-generated card reference id that we may use for direct Card object charging.
	 * We use this when we charge a `card` directly.
	 * If a `cardReference` purchase is made, it's unnecessary.
	 *
	 * @return string
	 */
	public function getGeneratedCardReference() {
		return $this->generatedCardReference;
	}

	/**
	 * Tells whether a direct `card` purchase is performer.
	 * (as opposed to a server-persisted card purchase, represented by a `cardReference` parameter)
	 *
	 * @return boolean
	 */
	public function isNonPersistedCardPurchase() {
		return (!$this->getCardReference());
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Econtext\Message\BaseMerchantRequest::getResponseClass()
	 */
	public function getResponseClass() {
		return '\Omnipay\Econtext\Message\PurchaseMerchantResponse';
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Common\Message\MessageInterface::getData()
	 * @throws \Omnipay\Common\Exception\InvalidRequestException - when either the cardReference or card parameter is missing
	 */
	public function getData() {
		$this->validate('amount');

		$data = array();
		$data['paymtCode'] = 'C20';
		$data['orderID'] = $this->getGeneratedOrderId();
		$data['ordAmount'] = $this->getParameter('amount');
		$data['ordAmountTax'] = ($this->getParameter('amountTax') ?: 0);
		$data['itemName'] = $this->getDescription();

		if ($this->getCardReference()) {
			//Purchase using an already server-persisted card
			$data['fncCode'] = '10';
			$data['cduserID'] = $this->getCardReference();
		} else if ($this->getCard() instanceof \Omnipay\Common\CreditCard) {
			//Purchase using an as-of-yet non-server-persisted card

			//Effectively, behind the scenes, the server will persist the card (whose `cardReference` we have prepared)
			//and would also execute the purchase.
			$data['fncCode'] = '22'; //This is equivalent to code 01 (CreateCardMerchantRequest) + 10 (charge existing card, like above)
			$data['cduserID'] = $this->getGeneratedCardReference();

			$data = array_merge($data, \Omnipay\Econtext\Util\Helper::cardToApiParameters($this->getCard()));
			//No idea why this is a required argument in this case and not for fncCode=10 (CreateCardMerchantRequest).
			$data['cd3secFlg'] = 0;
		} else {
			throw new \Omnipay\Common\Exception\InvalidRequestException('A `cardReference` or a `card` key needs to be provided.');
		}

		return $data;
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Common\Message\AbstractRequest::send()
	 * @throws \Omnipay\Common\Exception\InvalidRequestException - when either the cardReference or card parameter is missing
	 * @throws \Omnipay\Common\Exception\InvalidResponseException - when the API call fails or returns bad data
	 * @throws \Omnipay\Econtext\Exception\InvalidCredentialsException - if invalid gateway credentials were used (fragile)
	 * @return \Omnipay\Econtext\Message\PurchaseMerchantResponse
	 */
	public function send() {
		return parent::send();
	}

}
