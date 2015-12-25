<?php
namespace Omnipay\Econtext;

use Omnipay\Common\AbstractGateway;

class MerchantGateway extends AbstractGateway {

	public function getName() {
		return 'Econtext Merchant Gateway';
	}

	public function getDefaultParameters() {
		return array(
			'siteId' => '',
			'siteCheckCode' => '',
			'testMode' => false,
		);
	}

	public function setSiteId($value) {
		$this->parameters->set('siteId', $value);
		return $this;
	}

	public function getSiteId() {
		return $this->parameters->get('siteId');
	}

	public function setSiteCheckCode($value) {
		$this->parameters->set('siteCheckCode', $value);
		return $this;
	}

	public function getSiteCheckCode() {
		return $this->parameters->get('siteCheckCode');
	}

	/**
	 * Stores a credit card on the server that can be securely charged at a later time.
	 *
	 * @param array $parameters - could contain a 'card' key (\Omnipay\Common\CreditCard object)
	 * @return \Omnipay\Econtext\Message\CreateCardMerchantRequest
	 */
	public function createCard(array $parameters = array()) {
		return $this->createRequest('\Omnipay\Econtext\Message\CreateCardMerchantRequest', $parameters);
	}

	/**
	 * Retrieves partial stored-credit-card information.
	 * This is a custom method, not part of the Omnipay GatewayInterface API.
	 *
	 * @param array $parameters - could contain a 'cardReference' key (string)
	 * @return \Omnipay\Econtext\Message\RetrieveCardMerchantRequest
	 */
	public function retrieveCard(array $parameters = array()) {
		return $this->createRequest('\Omnipay\Econtext\Message\RetrieveCardMerchantRequest', $parameters);
	}

	/**
	 * Deletes the server-stored credit card.
	 * This operation is idempotent.
	 *
	 * @param array $parameters - could contain a 'cardReference' key (string)
	 * @return \Omnipay\Econtext\Message\DeleteCardMerchantRequest
	 */
	public function deleteCard(array $parameters = array()) {
		return $this->createRequest('\Omnipay\Econtext\Message\DeleteCardMerchantRequest', $parameters);
	}

	/**
	 * Performs a purchase immediately.
	 *
	 * @param array $parameters - could contain a 'cardReference' key (string) or a `card` key (\Omnipay\Common\CreditCard object)
	 * @return \Omnipay\Econtext\Message\PurchaseMerchantRequest
	 */
	public function purchase(array $parameters = array()) {
		return $this->createRequest('\Omnipay\Econtext\Message\PurchaseMerchantRequest', $parameters);
	}

	/**
	 * Performs a full refund for a purchase done by purchase().
	 *
	 * Partial refunds are not supported yet.
	 *
	 * This operation is not idempodent. Subsequent executions do not succeed.
	 *
	 * @param array $parameters - could contain a 'transactionReference' key (string).
	 * @return \Omnipay\Econtext\Message\RefundMerchantRequest
	 */
	public function refund(array $parameters = array()) {
		return $this->createRequest('\Omnipay\Econtext\Message\RefundMerchantRequest', $parameters);
	}

}