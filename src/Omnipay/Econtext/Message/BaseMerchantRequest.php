<?php
namespace Omnipay\Econtext\Message;

use Omnipay\Common\Message\AbstractRequest;

abstract class BaseMerchantRequest extends AbstractRequest {

	private $testEndpoint = 'https://test.econ.ne.jp/odr/rcv/rcv_odr.aspx';

	private $liveEndpoint = 'https://www.econ.ne.jp/odr/rcv/rcv_odr.aspx';

	/**
	 * Returns the PHP class name that wraps the response. Must inherit from BaseMerchantResponse.
	 *
	 * @return string
	 */
	abstract protected function getResponseClass();

	public function setSiteId($value) {
		$this->parameters->set('siteId', $value);
	}

	public function getSiteId() {
		return $this->getParameter('siteId');
	}

	public function setSiteCheckCode($value) {
		$this->parameters->set('siteCheckCode', $value);
	}

	public function getSiteCheckCode() {
		return $this->parameters->get('siteCheckCode');
	}

	public function getEndpoint() {
		return ($this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint);
	}

	public function sendData($data) {
		$data['shopID'] = $this->getSiteId();
		$data['chkCode'] = $this->getSiteCheckCode();

		//Who the hell uses shift-jis in today's day and age? Shame.
		$this->httpClient->addSubscriber(new \Omnipay\Econtext\GuzzlePlugin\ForceCharsetChange('shift_jis', 'utf8'));

		//Who the hell provides an XML API, but sometimes returns broken XML responses? Shame.
		$this->httpClient->addSubscriber(new \Omnipay\Econtext\GuzzlePlugin\FixInvalidXml());

		$httpRequest = $this->httpClient->createRequest(
			'POST',
			$this->getEndpoint(),
			array(
				'User-Agent' => 'Omnipay Econtext Gateway/1.0',
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
			http_build_query($data)
		);

		try {
			$httpResponse = $httpRequest->send();
		} catch (\Guzzle\Http\Exception\RequestException $e) {
			throw new \Omnipay\Common\Exception\InvalidResponseException(sprintf('Gateway HTTP call failure: %s', $e->getMessage()));
		} catch (\Omnipay\Econtext\Exception\CharsetChangeException $e) {
			throw new \Omnipay\Common\Exception\InvalidResponseException($e->getMessage());
		}

		try {
			$xml = $httpResponse->xml();
		} catch (\RuntimeException $e) {
			throw new \Omnipay\Common\Exception\InvalidResponseException(sprintf(
				'Gateway returned non-XML response: %s: [HTTP %d]: %s',
				$e->getMessage(),
				$httpResponse->getStatusCode(),
				$httpResponse->getBody(true)
			));
		}

		if ($httpResponse->getStatusCode() !== 200) {
			throw new \Omnipay\Common\Exception\InvalidResponseException(sprintf(
				'Gateway returned non-OK HTTP response code: [HTTP %d]: %s',
				$httpResponse->getStatusCode(),
				$httpResponse->getBody(true)
			));
		}

		return $this->response = $this->createResponseOrThrow($xml);
	}

	/**
	 * @param \SimpleXMLElement $xml
	 * @throws \Omnipay\Econtext\Exception\InvalidCredentialsException - if invalid gateway credentials were used (fragile)
	 * @return BaseMerchantResponse
	 */
	private function createResponseOrThrow(\SimpleXMLElement $xml) {
		//Let's try to catch certain important responses and convert them to exceptions.

		//If an invalid shopID is provided, `info` would be: パラメータチェックエラー「shopID:123456」
		//If an empty shopID is provided, `info` would be: 「shopID」パラメータ値無し。
		//Unfortunately, `infoCode` is empty, so we have to rely on string matching, which is idiotic.
		//This detection code is fragile and prone to breakage. Shame. Blame uptream.
		if ((string) $xml->status === '-2' && (string) $xml->infoCode === '' && strpos((string) $xml->info, 'shopID')) {
			throw new \Omnipay\Econtext\Exception\InvalidCredentialsException(sprintf(
				'Invalid siteId: %s (original error: %s)',
				$this->getSiteId(),
				(string) $xml->info
			));
		}

		if ((string) $xml->infoCode === BaseMerchantResponse::INFO_CODE_MISSING_SITE_CHECK_CODE) {
			throw new \Omnipay\Econtext\Exception\InvalidCredentialsException(sprintf(
				'Empty siteCheckCode (original error: %s)',
				(string) $xml->info
			));
		}

		if ((string) $xml->infoCode === BaseMerchantResponse::INFO_CODE_INVALID_SITE_CHECK_CODE) {
			throw new \Omnipay\Econtext\Exception\InvalidCredentialsException(sprintf(
				'Invalid siteCheckCode: %s (original error: %s)',
				$this->getSiteCheckCode(),
				(string) $xml->info
			));
		}

		$responseClass = $this->getResponseClass();
		return new $responseClass($this, $xml);
	}

}