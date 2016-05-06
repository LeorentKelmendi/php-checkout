<?php

namespace Svea\Checkout\Tests\Unit\Transport;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Transport\ApiClient;
use Svea\Checkout\Transport\ResponseHandler;

class ApiClientTest extends TestCase
{
    public function testSendRequestWithOkStatusResponse()
    {
        $httpCode = 200;
        $this->mockHttpClient($this->apiResponse, $httpCode);

        /**
         * @var ResponseHandler $responseHandler
         */
        $responseHandler = $this->apiClientMock->sendRequest($this->requestModel);

        $this->assertInstanceOf('Svea\Checkout\Transport\ResponseHandler', $responseHandler);
        $this->assertEquals($httpCode, $responseHandler->getHttpCode());
    }

    public function testSendRequestWithCreatedStatusResponse()
    {
        $httpCode = 201;
        $this->mockHttpClient($this->apiResponse, $httpCode);

        /**
         * @var ResponseHandler $responseHandler
         */
        $responseHandler = $this->apiClientMock->sendRequest($this->requestModel);

        $this->assertInstanceOf('Svea\Checkout\Transport\ResponseHandler', $responseHandler);
        $this->assertEquals($httpCode, $responseHandler->getHttpCode());
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionCode 400
     */
    public function testSendRequestWithBadRequestStatusResponse()
    {
        $this->mockHttpClient($this->apiResponse, 400);

        $this->apiClientMock->sendRequest($this->requestModel);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionCode 404
     */
    public function testSendRequestWithNotFoundStatusResponse()
    {
        $this->mockHttpClient($this->apiResponse, 404);

        $this->apiClientMock->sendRequest($this->requestModel);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionCode 401
     */
    public function testSendRequestWithUnauthorizedStatusResponse()
    {
        $this->mockHttpClient($this->apiResponse, 401);

        $this->apiClientMock->sendRequest($this->requestModel);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionCode 404
     */
    public function testSendRequestWith404StatusResponse()
    {
        $this->mockHttpClient($this->apiResponse, 404);

        $this->apiClientMock->sendRequest($this->requestModel);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Could not resolve host: rarafsafsafasfas.com
     */
    public function testSendRequestWithHttpClientError()
    {
        $error = 'Could not resolve host: rarafsafsafasfas.com';
        $this->mockHttpClient(null, null, $error, 2);

        $this->requestModel->setGetMethod();
        $this->apiClientMock->sendRequest($this->requestModel);
    }

    private function mockHttpClient($executeValue, $infoValue, $errorValue = null, $errorNumber = null)
    {
        if ($executeValue !== null) {
            $this->httpClientMock->expects($this->once())
                ->method('execute')
                ->will($this->returnValue($executeValue));
        }
        if ($infoValue !== null) {
            $this->httpClientMock->expects($this->once())
                ->method('getInfo')
                ->will($this->returnValue($infoValue));
        }
        if ($errorValue !== null) {
            $this->httpClientMock->expects($this->once())
                ->method('getError')
                ->will($this->returnValue($errorValue));
        }
        if ($errorNumber !== null) {
            $this->httpClientMock->expects($this->once())
                ->method('getErrorNumber')
                ->will($this->returnValue($errorNumber));
        }

        $this->setHttpClient();
    }

    private function setHttpClient()
    {
        $this->apiClientMock = new ApiClient($this->httpClientMock);
    }
}
