<?php
namespace Omnipay\Econtext\GuzzlePlugin;

use Guzzle\Common\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Converts Shift-JIS API responses to utf-8.
 *
 * Normally `$response->xml()` would obey the `encoding` attribute specified inside the response
 * and read it correctly.
 *
 * However, we want to do it ourselves early on and know that everything is Unicode in any case.
 */
class ForceCharsetChange implements EventSubscriberInterface {

	private $fromEncoding;
	private $toEncoding;

	public function __construct($fromEncoding, $toEncoding) {
		$this->fromEncoding = $fromEncoding;
		$this->toEncoding = $toEncoding;
	}

	public static function getSubscribedEvents() {
		return array(
			'request.complete' => 'onRequestComplete'
		);
	}

	public function onRequestComplete(Event $event) {
		/* @var $response \Guzzle\Http\Message\Response */
		$httpResponse = $event['response'];

		$bodyOriginal = $httpResponse->getBody(true);
		$bodyFinal = @mb_convert_encoding($bodyOriginal, $this->toEncoding, $this->fromEncoding);
		if (!$bodyFinal) {
			throw new \Omnipay\Econtext\Exception\CharsetChangeException(sprintf(
				'Cannot change charset: %s -> %s',
				$this->fromEncoding,
				$this->toEncoding
			));
		}

		//Adjust the XML declaration header, which may now incorrectly-contain the old encoding value.
		$bodyFinal = str_replace('encoding="' . $this->fromEncoding . '"', 'encoding="' . $this->toEncoding . '"', $bodyFinal);

		$httpResponse->setBody($bodyFinal);
	}

}