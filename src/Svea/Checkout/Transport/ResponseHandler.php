<?php

namespace Svea\Checkout\Transport;

use Svea\Checkout\Exception\SveaApiException;

/**
 * Class ResponseHandler - HTTP response handler
 * @package Svea\Checkout\Transport
 */
class ResponseHandler
{

    /**
     * Svea Checkout Api response content.
     *
     * @var mixed $content
     */
    private $content;

    private $header;

    private $body;

    private $httpCode;


    public function __construct($content, $httpCode)
    {
        $this->content = $content;
        $this->httpCode = $httpCode;

        $this->setHeader($content);
        $this->setBody($content);
    }

    /**
     * Handle Svea Checkout API response
     *
     * @throws SveaApiException
     */
    public function handleClientResponse()
    {
        switch ($this->httpCode) {
            case 200:
            case 201:
            case 302:
                //$this->content = $content;
                break;
            default:
                $errorMessage = isset($this->header['http_code']) ? $this->header['http_code'] : 'Some message';
                if(isset($this->header['ErrorMessage']))
                    $errorMessage = $this->header['ErrorMessage'];
                throw new SveaApiException($errorMessage, $this->httpCode);
                break;
        }
    }

    /**
     * Return response content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->body;
    }

    public function setHeader($response)
    {
        $headers = array();

        /**
         * Split the string on every "double" new line.
         * First is header data, second is body content
         */
        $arrRequests = explode("\r\n\r\n", $response);
        $headerLines = explode("\r\n", $arrRequests[0]);
        $headers['http_code'] = $headerLines[0];

        foreach ($headerLines as $i => $line) {
            if ($i > 0) {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }

        $this->header = $headers;
    }

    public function setBody($response)
    {
        $arrRequests = explode("\r\n\r\n", $response);

        $this->body = $arrRequests[1];
    }
}
