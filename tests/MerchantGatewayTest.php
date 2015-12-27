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

	public function testMissingShopIdIsDetectedAndThrows() {
		$this->setMockHttpResponse('ShopIdMissing.txt');
		$this->setExpectedException('\Omnipay\Econtext\Exception\InvalidCredentialsException');
		$response = $this->gateway->purchase(array('cardReference' => 'abcd', 'amount' => 500))->send();
	}

	public function testIncorrectShopIdIsDetectedAndThrows() {
		$this->setMockHttpResponse('ShopIdIncorrect.txt');
		$this->setExpectedException('\Omnipay\Econtext\Exception\InvalidCredentialsException');
		$response = $this->gateway->purchase(array('cardReference' => 'abcd', 'amount' => 500))->send();
	}

	public function testMissingCheckCodeIsDetectedAndThrows() {
		$this->setMockHttpResponse('ChkCodeMissing.txt');
		$this->setExpectedException('\Omnipay\Econtext\Exception\InvalidCredentialsException');
		$response = $this->gateway->purchase(array('cardReference' => 'abcd', 'amount' => 500))->send();
	}

	public function testIncorrectCheckCodeIsDetectedAndThrows() {
		$this->setMockHttpResponse('ChkCodeIncorrect.txt');
		$this->setExpectedException('\Omnipay\Econtext\Exception\InvalidCredentialsException');
		$response = $this->gateway->purchase(array('cardReference' => 'abcd', 'amount' => 500))->send();
	}

	public function testUniqueCardReferencesAreGeneratedOnEachCreateCard() {
		$request1 = $this->gateway->createCard(array('card' => $this->card));
		$request2 = $this->gateway->createCard(array('card' => $this->card));

		//Ensure it doesn't change between calls.
		$request1CardReference = $request1->getGeneratedCardReference();
		$this->assertSame($request1CardReference, $request1->getGeneratedCardReference());

		$this->assertNotNull($request1->getGeneratedCardReference());
		$this->assertNotNull($request2->getGeneratedCardReference());

		$this->assertNotSame($request1->getGeneratedCardReference(), $request2->getGeneratedCardReference());
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

	public function testCreateCardDoesNotLocallyValidateCardsDuringTestMode() {
		$this->card->setNumber('1');
		$this->gateway->setTestMode(true);
		$this->setMockHttpResponse('CreateCardSuccess.txt');
		$response = $this->gateway->createCard(array('card' => $this->card))->send();
		$this->assertTrue($response->isSuccessful());
	}

	public function testCreateCardLocallyValidatesCardsDuringNonTestMode() {
		$this->card->setNumber('1');
		$this->gateway->setTestMode(false);
		$this->setMockHttpResponse('CreateCardSuccess.txt');
		$transaction = $this->gateway->createCard(array('card' => $this->card));

		$this->setExpectedException('\Omnipay\Common\Exception\InvalidCreditCardException');
		$transaction->send();
	}

	public function testCreateCardThrowsOnInvalidCards() {
		$transaction = $this->gateway->createCard(array('card' => null));
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidRequestException');
		$transaction->send();
	}

	public function testCreateCardThrowsOnMissingParameters() {
		$transaction = $this->gateway->createCard();
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidRequestException');
		$transaction->send();
	}

	public function testCreateCardThrowsOnMajorlyInvalidXmlResponses() {
		$this->setMockHttpResponse('NonXmlResponse200.txt');
		$transaction = $this->gateway->createCard(array('card' => $this->card));
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidResponseException');
		$transaction->send();
	}

	public function testCreateCardThrowsOnNon200Responses() {
		$this->setMockHttpResponse('Non200Response.txt');
		$transaction = $this->gateway->createCard(array('card' => $this->card));
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidResponseException');
		$transaction->send();
	}

	public function testCreateCardDuplicateIsHandled() {
		$this->setMockHttpResponse('CreateCardDuplicate.txt');
		$response = $this->gateway->createCard(array('card' => $this->card))->send();
		$this->assertFalse($response->isSuccessful());
		$this->assertNull($response->getCardReference());
	}

	public function testCreateCardDuplicateWhichHadBeenDeletedIsHandled() {
		$this->setMockHttpResponse('CreateCardDuplicateButDeleted.txt');
		$response = $this->gateway->createCard(array('card' => $this->card))->send();
		$this->assertFalse($response->isSuccessful());
		$this->assertNull($response->getCardReference());
	}

	public function testInvalidXmlResponsesAreAlsoHandled() {
		$this->setMockHttpResponse('CreateCardSuccessInvalidXMLResponse.txt');
		$response = $this->gateway->createCard(array('card' => $this->card))->send();
		$this->assertTrue($response->isSuccessful());
		$this->assertSame('正常', $response->getMessage());
		$this->assertNotNull($response->getCardReference());
		$this->assertSame($response->getRequest()->getGeneratedCardReference(), $response->getCardReference());
	}

	public function testRetrieveCardRequiresCardReference() {
		$transaction = $this->gateway->retrieveCard();
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidRequestException');
		$transaction->send();
	}

	public function testRetrieveCardRequiresNonNullCardReference() {
		$transaction = $this->gateway->retrieveCard(array('cardReference' => null));
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidRequestException');
		$transaction->send();
	}

	public function testRetrieveCard() {
		$this->setMockHttpResponse('RetrieveCardSuccess.txt');
		$response = $this->gateway->retrieveCard(array('cardReference' => 'abcd'))->send();
		$this->assertTrue($response->isSuccessful());
		$this->assertSame('abcd', $response->getCardReference());
		$this->assertInstanceOf('\Omnipay\Econtext\Model\RetrievedPartialCreditCard', $response->getCard());
		$this->assertSame('1111', $response->getCard()->getNumberLastFour());
		$this->assertSame(2016, $response->getCard()->getExpiryYear());
		$this->assertSame(10, $response->getCard()->getExpiryMonth());
	}

	public function testRetrieveCardWithInvalidExpDateThrows() {
		$this->setMockHttpResponse('RetrieveCardSuccessInvalidExpDate.txt');
		$response = $this->gateway->retrieveCard(array('cardReference' => 'abcd'))->send();
		$this->assertTrue($response->isSuccessful());
		$this->assertSame('abcd', $response->getCardReference());

		$this->setExpectedException('\RuntimeException');
		$this->assertInstanceOf('\Omnipay\Econtext\Model\RetrievedPartialCreditCard', $response->getCard());
	}

	public function testRetrieveCardForUnknownCardReferenceFails() {
		$this->setMockHttpResponse('RetrieveCardForUnknownCardReference.txt');
		$response = $this->gateway->retrieveCard(array('cardReference' => 'abcd'))->send();
		$this->assertFalse($response->isSuccessful());
		$this->assertSame('abcd', $response->getCardReference());
		$this->assertNull($response->getCard());
	}

	public function testDeleteCardRequiresCardReference() {
		$transaction = $this->gateway->deleteCard();
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidRequestException');
		$transaction->send();
	}

	public function testDeleteCardRequiresNonNullCardReference() {
		$transaction = $this->gateway->deleteCard(array('cardReference' => null));
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidRequestException');
		$transaction->send();
	}

	public function testDeleteCard() {
		$this->setMockHttpResponse('DeleteCardSuccess.txt');
		$response = $this->gateway->deleteCard(array('cardReference' => 'abcd'))->send();
		$this->assertTrue($response->isSuccessful());
		$this->assertSame('abcd', $response->getCardReference());
	}

	public function testDeleteCardIsIdempotent() {
		$this->setMockHttpResponse('DeleteCardButDeleted.txt');
		$response = $this->gateway->deleteCard(array('cardReference' => 'abcd'))->send();
		$this->assertTrue($response->isSuccessful());
		$this->assertSame('abcd', $response->getCardReference());
	}

	public function testDeleteCardForUnknownCardReferenceFails() {
		$this->setMockHttpResponse('DeleteCardForUnknownCardReference.txt');
		$response = $this->gateway->deleteCard(array('cardReference' => 'abcd'))->send();
		$this->assertFalse($response->isSuccessful());
		$this->assertSame(null, $response->getCardReference());
	}

	public function testPurchaseRequiresAndWorksWithIntegerAmounts() {
		$transaction = $this->gateway->purchase();
		try {
			$transaction->send();
			$this->fail();
		} catch (\Omnipay\Common\Exception\InvalidRequestException $e) {

		}

		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$response = $this->gateway->purchase(array('amount' => 500, 'cardReference' => 'abcd'))->send();
		$this->assertTrue($response->isSuccessful());
		$this->assertSame($response->getCardReference(), 'abcd');
	}

	public function testPurchaseRequiresCardOrCardReference() {
		$transaction = $this->gateway->purchase(array('amount' => 500));
		try {
			$transaction->send();
			$this->fail();
		} catch (\Omnipay\Common\Exception\InvalidRequestException $e) {

		}

		$transaction = $this->gateway->purchase(array('cardReference' => null, 'amount' => 500));
		try {
			$transaction->send();
			$this->fail();
		} catch (\Omnipay\Common\Exception\InvalidRequestException $e) {

		}

		$transaction = $this->gateway->purchase(array('card' => null, 'amount' => 500));
		try {
			$transaction->send();
			$this->fail();
		} catch (\Omnipay\Common\Exception\InvalidRequestException $e) {

		}

		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$transaction = $this->gateway->purchase(array('cardReference' => 'abcd', 'amount' => 500));
		$transaction->send();

		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$transaction = $this->gateway->purchase(array('card' => $this->card, 'amount' => 500));
		$transaction->send();
	}

	public function testPurchaseWithExistingCard() {
		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$response = $this->gateway->purchase(array('amount' => 500, 'cardReference' => 'abcd'))->send();
		$this->assertTrue($response->isSuccessful());
		$this->assertSame($response->getCardReference(), 'abcd');
		$this->assertNotNull($response->getTransactionReference());
		$this->assertSame($response->getRequest()->getGeneratedOrderId(), $response->getTransactionReference());
	}

	public function testPurchaseSelfGeneratesDifferentTransactionReferences() {
		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$response1 = $this->gateway->purchase(array('amount' => 500, 'cardReference' => 'abcd'))->send();

		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$response2 = $this->gateway->purchase(array('amount' => 500, 'cardReference' => 'abcd'))->send();

		$this->assertSame($response1->getCardReference(), $response2->getCardReference());
		$this->assertNotSame($response1->getTransactionReference(), $response2->getTransactionReference());
	}

	public function testPurchaseWithExistingOrNonExistingCardsCallsDifferentAPIs() {
		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$transaction1 = $this->gateway->purchase(array('amount' => 500, 'cardReference' => 'abcd'));
		$this->assertArrayHasKey('fncCode', $transaction1->getData());
		$this->assertSame('10', $transaction1->getData()['fncCode']);

		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$transaction2 = $this->gateway->purchase(array('amount' => 500, 'card' => $this->card));
		$this->assertArrayHasKey('fncCode', $transaction2->getData());
		$this->assertSame('22', $transaction2->getData()['fncCode']);
	}

	public function testPurchaseWithInlineCardForceDisables3dSecure() {
		//The API, surprisingly, requires the cd3secFlg flag for that kind of purchase.
		//We need to make sure we send it.
		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$transaction2 = $this->gateway->purchase(array('amount' => 500, 'card' => $this->card));
		$this->assertArrayHasKey('cd3secFlg', $transaction2->getData());
		$this->assertSame(0, $transaction2->getData()['cd3secFlg']);
	}

	public function testPurchaseWithInlineCardAutoPeristsTheCard() {
		$this->setMockHttpResponse('PurchaseWithExistingCardSuccess.txt');
		$transaction = $this->gateway->purchase(array('amount' => 500, 'card' => $this->card));
		$generatedCardReference = $transaction->getGeneratedCardReference();
		$response = $transaction->send();
		$this->assertNotNull($response->getCardReference());
		$this->assertSame($generatedCardReference, $response->getCardReference());
	}

	public function testPurchaseWithDuplicateOrderFailureIsCaught() {
		$this->setMockHttpResponse('PurchaseDuplicateOrder.txt');
		$transaction = $this->gateway->purchase(array('amount' => 500, 'card' => $this->card));
		$generatedCardReference = $transaction->getGeneratedCardReference();
		$response = $transaction->send();
		$this->assertFalse($response->isSuccessful());
		$this->assertNull($response->getCardReference());
		$this->assertNull($response->getTransactionReference());
	}

	public function testRefundRequiresTransactionReference() {
		$transaction = $this->gateway->refund();
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidRequestException');
		$transaction->send();
	}

	public function testRefundRequiresNonNullTransactionReference() {
		$transaction = $this->gateway->refund(array('transactionReference' => null));
		$this->setExpectedException('\Omnipay\Common\Exception\InvalidRequestException');
		$transaction->send();
	}

	public function testRefund() {
		$this->setMockHttpResponse('RefundSuccess.txt');
		$response = $this->gateway->refund(array('transactionReference' => 'abcd'))->send();
		$this->assertTrue($response->isSuccessful());
	}

	public function testRefundIsNotIdempotent() {
		$this->setMockHttpResponse('RefundAlreadyRefunded.txt');
		$response = $this->gateway->refund(array('transactionReference' => 'abcd'))->send();
		$this->assertFalse($response->isSuccessful());
	}

}
