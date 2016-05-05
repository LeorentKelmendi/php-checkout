<?php

require_once '../vendor/autoload.php';

// - order ID sample
$data = 20;

try {
    $merchantId = '1';
    $sharedSecret = 'sharedSecret';
    $baseUrl = \Svea\Checkout\Transport\Connector::TEST_BASE_URL;

    $conn = new \Svea\Checkout\Transport\Connector($merchantId, $sharedSecret, $baseUrl);

    $checkoutClient = new \Svea\Checkout\CheckoutClient($conn);
    $response = $checkoutClient->get($data);

    print_r($response['Gui']['Snippet']);
} catch (\Svea\Checkout\Exception\SveaApiException $ex) {
    var_dump("--------- Api errors ---------");
    var_dump($ex->getMessage());
} catch (\Svea\Checkout\Exception\SveaConnectorException $ex) {
    var_dump("--------- Conn errors ---------");
    var_dump($ex->getMessage());
} catch (\Svea\Checkout\Exception\SveaInputValidationException $ex) {
    var_dump("--------- Input data errors ---------");
    var_dump($ex->getMessage());
} catch (Exception $ex) {
    var_dump("--------- General errors ---------");
    var_dump($ex->getMessage());
}
