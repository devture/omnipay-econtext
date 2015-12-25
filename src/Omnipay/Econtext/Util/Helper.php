<?php
namespace Omnipay\Econtext\Util;

class Helper {

	static public function cardToApiParameters(\Omnipay\Common\CreditCard $card) {
		$data = array();
		$data['econCardno'] = $card->getNumber();
		$data['cardExpdate'] = $card->getExpiryDate('Ym');
		$data['CVV2'] = $card->getCvv();
		$data['kanjiName1_1'] = $card->getLastName();
		$data['kanjiName1_2'] = $card->getFirstName();
		return $data;
	}

}