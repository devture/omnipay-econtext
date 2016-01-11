<?php
namespace Omnipay\Econtext\Message;

use Omnipay\Common\Message\AbstractResponse;

abstract class BaseMerchantResponse extends AbstractResponse {

	const INFO_CODE_MISSING_SITE_CHECK_CODE = 'E0004';
	const INFO_CODE_INVALID_SITE_CHECK_CODE = 'E1004';
	const INFO_CODE_BAD_ORDER_NUMBER = 'E1111';
	const INFO_CODE_CARD_ALREADY_DELETED = 'C2102';

	public function isSuccessful() {
		if ((string) $this->data->status !== '1') {
			return false;
		}

		return true;
	}

	public function getMessage() {
		return (string) $this->data->info;
	}

}