<?php
namespace Omnipay\Econtext;

use Omnipay\Tests\GatewayTestCase;
use Omnipay\Common\CreditCard;

class MerchantGatewayTest extends GatewayTestCase {

	/** @var MerchantGateway */
	public $gateway;

	/** @var CreditCard */
	private $card;

	public function setUp() {
		parent::setUp();

		$this->gateway = new MerchantGateway($this->getHttpClient(), $this->getHttpRequest());
		$this->gateway->setSiteId('123456');
		$this->gateway->setSiteCheckCode('12345678910');
		$this->gateway->setTestMode(true);

		$this->card = new CreditCard(array(
			'firstName' => '寛',
			'lastName' => '山田',
			'number' => '4980111111111111',
			'cvv' => '123',
			'expiryMonth' => '1',
			'expiryYear' => '2017',
			'email' => 'testcard@example.com',
		));
	}

	public function testInvalidCredentialsAreDetectedAndThrown() {
		$this->setMockHttpResponse('ShopIdMissing.txt');
		$this->setExpectedException('\Omnipay\Econtext\Exception\InvalidCredentialsException');
		$response = $this->gateway->purchase(array('cardReference' => 'abcd', 'amount' => 500))->send();
	}

	public function testCreateCard() {
		$this->setMockHttpResponse('CreateCardSuccess.txt');
		$response = $this->gateway->createCard(array('card' => $this->card))->send();
		$this->assertTrue($response->isSuccessful());
		$this->assertSame('正常', $response->getMessage());
		$this->assertNotNull($response->getCardReference());
		$this->assertSame($response->getRequest()->getGeneratedCardReference(), $response->getCardReference());
	}

	public function testCreateCardSelfGeneratesDifferentCardReferences() {
		$this->setMockHttpResponse('CreateCardSuccess.txt');
		$response1 = $this->gateway->createCard(array('card' => $this->card))->send();

		$this->setMockHttpResponse('CreateCardSuccess.txt');
		$response2 = $this->gateway->createCard(array('card' => $this->card))->send();

		$this->assertNotSame($response1->getCardReference(), $response2->getCardReference());
	}

	public function testInvalidXmlResponsesAreAlsoHandled() {
		$this->setMockHttpResponse('CreateCardSuccessInvalidXMLResponse.txt');
		$response = $this->gateway->createCard(array('card' => $this->card))->send();
		$this->assertTrue($response->isSuccessful());
		$this->assertSame('正常', $response->getMessage());
		$this->assertNotNull($response->getCardReference());
		$this->assertSame($response->getRequest()->getGeneratedCardReference(), $response->getCardReference());
	}

}
