<?php
namespace Omnipay\Econtext\GuzzlePlugin;

use Guzzle\Common\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Some Econtext API responses do not return valid XML.
 * One instance that it happens is when the shopID parameter's value is incorrect.
 *
 * When it happens, the header tag is missing (not a big deal).
 * But more importantly, multiple tags are returned at the top-level, instead of being wrapped
 * in an object. This breaks XML parsing.
 */
class FixInvalidXml implements EventSubscriberInterface {

	public static function getSubscribedEvents() {
		return array(
			'request.complete' => 'onRequestComplete'
		);
	}

	public function onRequestComplete(Event $event) {
		/* @var $response \Guzzle\Http\Message\Response */
		$httpResponse = $event['response'];

		$bodyOriginal = $httpResponse->getBody(true);

		if (strpos($bodyOriginal, '<') === false) {
			//Doesn't look like XML. Nothing to fix.
			return;
		}

		if (strpos($bodyOriginal, '<?xml') !== false) {
			//Looks like this is a proper response. Nothing to fix.
			return;
		}

		$xmlHeader = sprintf('<?xml version="1.0" encoding="%s"?>', mb_detect_encoding($bodyOriginal));
		$bodyFinal = $xmlHeader . "\n" . '<result>' . $bodyOriginal . '</result>';

		$httpResponse->setBody($bodyFinal);
	}

}