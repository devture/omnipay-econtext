<?php
namespace Omnipay\Econtext\Message;

class RetrieveCardMerchantResponse extends BaseMerchantResponse {

	/**
	 * Returns the card reference that is being retrieved.
	 * @return string|NULL
	 */
	public function getCardReference() {
		return $this->getRequest()->getCardReference();
	}

	/**
	 * @return \Omnipay\Econtext\Model\RetrievedPartialCreditCard|NULL
	 */
	public function getCard() {
		if (!$this->isSuccessful()) {
			return null;
		}

		$expirationDateYm = (string) $this->data->cardExpdate;

		if (strlen($expirationDateYm) !== 6) {
			throw new \RuntimeException('Expiration date format is unexpected: ' . $expirationDateYm);
		}

		$expiryYear = substr($expirationDateYm, 0, 4);
		$expiryMonth = substr($expirationDateYm, 4, 2);

		$partialCard = new \Omnipay\Econtext\Model\RetrievedPartialCreditCard(array(
			'expiryYear' => $expiryYear,
			'expiryMonth' => $expiryMonth,
		));

		$partialCard->setNumberLastFour((string) $this->data->econCardno4);

		return $partialCard;
	}

	/**
	 * @return \Omnipay\Econtext\Message\RetrieveCardMerchantRequest
	 */
	public function getRequest() {
		return $this->request;
	}

}