<?php
/**
 * WHMCS Sample Payment Callback File
 *
 * This sample file demonstrates how a payment gateway callback should be
 * handled within WHMCS.
 *
 * It demonstrates verifying that the payment gateway module is active,
 * validating an Invoice ID, checking for the existence of a Transaction ID,
 * Logging the Transaction for debugging and Adding Payment to an Invoice.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/callbacks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);
$publicKey = $gatewayParams["publicKey"];
$systemUrl = $gatewayParams["systemurl"];
// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}
// Retrieve data returned in payment gateway callback
// Varies per payment gateway
$signature = $_GET["signature"];
$transactionId = $_GET["tracking_id"];
$checkoutID = $_GET["checkout_id"];

//validate from intasend
$curl = curl_init();
$url = "https://api.intasend.com/api/v1/payment/status/";
$args = array(
    'invoice_id' => $tracking_id,
    // 'signature' => $signature,
    'checkout_id' => $checkout_id
);
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($args),
    CURLOPT_HTTPHEADER => [
        "X-IntaSend-Public-API-Key: $publicKey",
        "accept: application/json",
        "content-type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "Request error:" . $err;
    $redirectUrl = $systemUrl . '/error.php?error=' . $err;
    header("Location: $redirectUrl");
    exit;
}

$data = json_decode($response, true);
$state = $data['invoice']['state'];
$invoiceId = $data['invoice']['api_ref'];
$paymentAmount = $data['invoice']['value'];
$paymentFee = $data['invoice']['charges'];
$transactionStatus = $state === 'COMPLETE' ? 'Success' : 'Failure';
$success = $state === 'COMPLETE' ? true : false;

/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 *
 * @param int $invoiceId Invoice ID
 * @param string $gatewayName Gateway Name
 */
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
/**
 * Check Callback Transaction ID.
 *
 * Performs a check for any existing transactions with the same given
 * transaction number.
 *
 * Performs a die upon encountering a duplicate.
 *
 * @param string $transactionId Unique Transaction ID
 */
checkCbTransID($transactionId);

/**
 * Log Transaction.
 *
 * Add an entry to the Gateway Log for debugging purposes.
 *
 * The debug data can be a string or an array. In the case of an
 * array it will be
 *
 * @param string $gatewayName        Display label
 * @param string|array $debugData    Data to log
 * @param string $transactionStatus  Status
 */
logTransaction($gatewayParams["name"], $data, $transactionStatus);

if ($success) {

    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */

    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );
}

$redirectUrl = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
header("Location: $redirectUrl");
exit;