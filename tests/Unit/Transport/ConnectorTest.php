<?php

namespace Svea\Checkout\Tests\Unit\Transport;

use \Exception;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Exception\SveaApiException;

class ConnectorTest extends TestCase
{
    public function testCreateMatchesDataGiven()
    {
        $connector = new Connector(
            $this->apiClientMock,
            $this->merchantId,
            $this->sharedSecret,
            $this->apiUrl
        );

        $this->assertInstanceOf('\Svea\Checkout\Transport\ApiClient', $connector->getApiClient());
        $this->assertEquals($this->merchantId, $connector->getMerchantId());
        $this->assertEquals($this->sharedSecret, $connector->getSharedSecret());
        $this->assertEquals($this->apiUrl, $connector->getApiUrl());
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaConnectorException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::MISSING_MERCHANT_ID
     */
    public function testValidateMerchantIdWithInvalidMerchantId()
    {
        $this->merchantId = '';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateMerchantId');
    }

    public function testValidateMerchantIdWithValidMerchantId()
    {
        $this->merchantId = '123';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateMerchantId');
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaConnectorException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::MISSING_SHARED_SECRET
     */
    public function testValidateSharedSecretWithInvalidSharedSecret()
    {
        $this->sharedSecret = '';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateSharedSecret');
    }

    public function testValidateSharedSecretWithValidSharedSecret()
    {
        $this->sharedSecret = 'sharedSecret';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateSharedSecret');
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaConnectorException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::MISSING_API_BASE_URL
     */
    public function testValidateApiUrlWithoutApiUrl()
    {
        $this->apiUrl = '';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateApiUrl');
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaConnectorException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INCORRECT_API_BASE_URL
     */
    public function testValidateApiUrlWithBadApiUrl()
    {
        $this->apiUrl = 'http://invalid.url.svea.com';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateApiUrl');
    }

    public function testValidateApiUrlWithValidApiUrl()
    {
        $this->apiUrl = Connector::TEST_BASE_URL;
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateApiUrl');
    }

    public function testSendRequestAndReceiveSuccessfulResponse()
    {
        $responseContent = $this->apiResponse;
        $httpCode = 201;
        $responseHandler = $this->getMockBuilder('\Svea\Checkout\Transport\ResponseHandler')
            ->setConstructorArgs(array($responseContent, $httpCode))
            ->getMock();

        $responseHandler->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($responseContent));

        $this->apiClientMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($responseHandler));

        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);
        $response = $connector->sendRequest($this->requestModel);

        $this->assertEquals($responseContent, $response);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionMessage The input data was bad
     */
    public function testSendRequestSveaApiExceptionThrown()
    {
        $sveaApiException = new SveaApiException('The input data was bad', 1000);

        $this->apiClientMock->expects($this->once())
            ->method('sendRequest')
            ->with($this->identicalTo($this->requestModel))
            ->will($this->throwException($sveaApiException));

        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);
        $connector->sendRequest($this->requestModel);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionMessage API communication error
     */
    public function testSendRequestGeneralExceptionThrown()
    {
        $ex = new Exception('General error');

        $this->apiClientMock->expects($this->once())
            ->method('sendRequest')
            ->with($this->identicalTo($this->requestModel))
            ->will($this->throwException($ex));

        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);
        $connector->sendRequest($this->requestModel);
    }

    public function testCreateAuthorizationToken()
    {
        $expectedAuthToken = 'MTIzNDU2OjEyZGVkNGUxYzFhODY3Nzc5ZDVmMTRjMjU0YzRmMmYzYjM4NTE2';
        $expectedAuthToken .= 'MjZiNGI4MGJmOWVkYmJiMTliODdkMmZmMWZhODFiZTliYWNkNmI3ZTE3ZjJh';
        $expectedAuthToken .= 'ODllMGQzMmQwODE3Mjc0YmMzYjcwOTYzYTNmNGE5YzY0MWJiYWUzNmVkODc1';

        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);
        $this->invokeMethod($connector, 'createAuthorizationToken', array($this->requestModel));

        $this->assertEquals($expectedAuthToken, $this->requestModel->getAuthorizationToken());
    }
}
