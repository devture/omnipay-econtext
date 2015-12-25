<?php
namespace Omnipay\Econtext\Message;

class RetrieveCardMerchantRequest extends BaseMerchantRequest {

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Econtext\Message\BaseMerchantRequest::getResponseClass()
	 */
	public function getResponseClass() {
		return '\Omnipay\Econtext\Message\RetrieveCardMerchantResponse';
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Common\Message\MessageInterface::getData()
	 * @throws \Omnipay\Common\Exception\InvalidRequestException - when the cardReference parameter is missing
	 */
	public function getData() {
		//Checks if a `cardReference` parameter is provided.
		//May throw \Omnipay\Common\Exception\InvalidRequestException
		$this->validate('cardReference');

		$data = array();
		$data['paymtCode'] = 'C20';
		$data['fncCode'] = '04';
		$data['cduserID'] = $this->getCardReference();

		return $data;
	}

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Common\Message\AbstractRequest::send()
	 * @throws \Omnipay\Common\Exception\InvalidRequestException - when the cardReference parameter is missing
	 * @throws \Omnipay\Common\Exception\InvalidResponseException - when the API call fails or returns bad data
	 * @throws \Omnipay\Econtext\Exception\InvalidCredentialsException - if invalid gateway credentials were used (fragile)
	 * @return \Omnipay\Econtext\Message\RetrieveCardMerchantResponse
	 */
	public function send() {
		return parent::send();
	}

}