<?php
namespace Omnipay\Econtext\Model;

class RetrievedPartialCreditCard extends \Omnipay\Common\CreditCard {

	private $numberLast4;

	public function getNumberLastFour() {
		return $this->numberLast4;
	}

	public function setNumberLastFour($value) {
		$this->numberLast4 = $value;
	}

}