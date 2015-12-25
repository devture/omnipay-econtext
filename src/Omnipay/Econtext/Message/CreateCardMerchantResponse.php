<?php
namespace Omnipay\Econtext\Message;

class CreateCardMerchantResponse extends BaseMerchantResponse {

	public function getCardReference() {
		if (!$this->isSuccessful()) {
			return null;
		}
		return $this->getRequest()->getGeneratedCardReference();
	}

	/**
	 * @return \Omnipay\Econtext\Message\CreateCardMerchantRequest
	 */
	public function getRequest() {
		return $this->request;
	}

}