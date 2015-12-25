<?php
namespace Omnipay\Econtext\Message;

class DeleteCardMerchantResponse extends BaseMerchantResponse {

	/**
	 * {@inheritDoc}
	 * @see \Omnipay\Econtext\Message\BaseMerchantResponse::isSuccessful()
	 * @return boolean
	 */
	public function isSuccessful() {
		//Let's make this idempotent. Catch the "already deleted" error and consider that a success.
		if ((string) $this->data->infoCode === BaseMerchantResponse::INFO_CODE_CARD_ALREADY_DELETED) {
			return true;
		}
		return parent::isSuccessful();
	}

	/**
	 * Returns the card reference that is being deleted.
	 * @return string|NULL
	 */
	public function getCardReference() {
		if (!$this->isSuccessful()) {
			return null;
		}
		return $this->getRequest()->getCardReference();
	}

	/**
	 * @return \Omnipay\Econtext\Message\DeleteCardMerchantRequest
	 */
	public function getRequest() {
		return $this->request;
	}

}