<?php
namespace Omnipay\Econtext\Message;

class PurchaseMerchantResponse extends BaseMerchantResponse {

	/**
	 * @return \Omnipay\Econtext\Message\PurchaseMerchantRequest
	 */
	public function getRequest() {
		return $this->request;
	}

	public function getTransactionReference() {
		if (!$this->isSuccessful()) {
			return null;
		}
		return $this->getRequest()->getGeneratedOrderId();
	}

	/**
	 * Returns the `cardReference` that was charged.
	 *
	 * For non-persisted card purchases, this would be the automatic newly created `cardReference`.
	 * For existing and persisted cards, this would be the `cardReference` passed to us.
	 *
	 * @return string
	 */
	public function getCardReference() {
		if (!$this->isSuccessful()) {
			return null;
		}
		if ($this->getRequest()->isNonPersistedCardPurchase()) {
			return $this->getRequest()->getGeneratedCardReference();
		}
		return $this->getRequest()->getCardReference();
	}

}