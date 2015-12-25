<?php
namespace Omnipay\Econtext\Message;

class RefundMerchantRequest extends BaseMerchantRequest {

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Econtext\Message\BaseMerchantRequest::getResponseClass()
	 */
	public function getResponseClass() {
		return '\Omnipay\Econtext\Message\RefundMerchantResponse';
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Common\Message\MessageInterface::getData()
	 * @throws \Omnipay\Common\Exception\InvalidRequestException - when the transactionReference parameter is missing
	 */
	public function getData() {
		//Checks if a `transactionReference` parameter is provided.
		//May throw \Omnipay\Common\Exception\InvalidRequestException
		$this->validate('transactionReference');

		$data = array();
		$data['paymtCode'] = 'C20';
		$data['fncCode'] = '19';
		$data['orderID'] = $this->getTransactionReference();
		$data['ordAmount'] = 0;
		$data['ordAmountTax'] = 0;

		return $data;
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Common\Message\AbstractRequest::send()
	 * @throws \Omnipay\Common\Exception\InvalidRequestException - when the transactionReference parameter is missing
	 * @throws \Omnipay\Common\Exception\InvalidResponseException - when the API call fails or returns bad data
	 * @throws \Omnipay\Econtext\Exception\InvalidCredentialsException - if invalid gateway credentials were used (fragile)
	 * @return \Omnipay\Econtext\Message\RefundMerchantResponse
	 */
	public function send() {
		return parent::send();
	}

}