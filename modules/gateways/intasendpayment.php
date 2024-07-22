<?php
/**
 * WHMCS Sample Payment Gateway Module
 *
 * Payment Gateway modules allow you to integrate payment solutions with the
 * WHMCS platform.
 *
 * This sample file demonstrates how a payment gateway module for WHMCS should
 * be structured and all supported functionality it can contain.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "gatewaymodule" and therefore all functions
 * begin "gatewaymodule_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _config
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function intasendpayment_MetaData()
{
    return array(
        'DisplayName' => 'InaSend Payment',
        'APIVersion' => '1.1', // Use API Version 1.1
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function intasendpayment_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'IntaSend Payments',
        ),
        // a text field type allows for single line text input
        'publicKey' => array(
            'FriendlyName' => 'Public Key',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your public key here',
        ),
        // a password field type allows for masked text input
        'secretKey' => array(
            'FriendlyName' => 'Secret Key',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your secret key here',
        ),
        // the yesno field type displays a single checkbox option
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        ),
        // the dropdown field type renders a select menu of options
        // 'dropdownField' => array(
        //     'FriendlyName' => 'Dropdown Field',
        //     'Type' => 'dropdown',
        //     'Options' => array(
        //         'option1' => 'Display Value 1',
        //         'option2' => 'Second Option',
        //         'option3' => 'Another Option',
        //     ),
        //     'Description' => 'Choose one',
        // ),
        // the radio field type displays a series of radio button options
        // 'radioField' => array(
        //     'FriendlyName' => 'Radio Field',
        //     'Type' => 'radio',
        //     'Options' => 'First Option,Second Option,Third Option',
        //     'Description' => 'Choose your option!',
        // ),
        // the textarea field type allows for multi-line text input
        // 'textareaField' => array(
        //     'FriendlyName' => 'Textarea Field',
        //     'Type' => 'textarea',
        //     'Rows' => '3',
        //     'Cols' => '60',
        //     'Description' => 'Freeform multi-line text input field',
        // ),
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function intasendpayment_link($params)
{
    // Gateway Configuration Parameters
    $publicKey = $params['publicKey'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    // $dropdownField = $params['dropdownField'];
    // $radioField = $params['radioField'];
    // $textareaField = $params['textareaField'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    $url = 'https://api.intasend.com/api/v1/checkout/';

    $postfields = array();
    // $postfields['username'] = $username;
    $postfields['api_ref'] = $invoiceId;
    // $postfields['description'] = $description;
    $postfields['amount'] = $amount;
    $postfields['currency'] = $currencyCode;
    $postfields['first_name'] = $firstname;
    $postfields['last_name'] = $lastname;
    $postfields['email'] = $email;
    $postfields['address'] = $address1;
    $postfields['city'] = $city;
    $postfields['state'] = $state;
    $postfields['zipcode'] = $postcode;
    $postfields['country'] = $country;
    $postfields['phone_number'] = $phone;
    $postfields['callback_url'] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';
    $postfields['redirect_url'] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($postfields),
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
        // echo "Request error:" . $err;
        $redirectUrl = $systemUrl . '/error.php?error=' . $err;
        header("Location: $redirectUrl");
        exit;
    } 
    
    $data = json_decode($response, true);
    $response_url = $data['url'];

    $htmlOutput = '<form method="get" action="' . $response_url . '"  name="f1">';
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
function intasendpayment_refund($params)
{
    // Gateway Configuration Parameters
    $publicKey = $params['publicKey'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    // $dropdownField = $params['dropdownField'];
    // $radioField = $params['radioField'];
    // $textareaField = $params['textareaField'];

    // Transaction Parameters
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to initiate refund and interpret result

    // return array(
    //     // 'success' if successful, otherwise 'declined', 'error' for failure
    //     'status' => 'success',
    //     // Data to be recorded in the gateway log - can be a string or array
    //     'rawdata' => $responseData,
    //     // Unique Transaction ID for the refund transaction
    //     'transid' => $refundTransactionId,
    //     // Optional fee amount for the fee value refunded
    //     'fees' => $feeAmount,
    // );
}

/**
 * Cancel subscription.
 *
 * If the payment gateway creates subscriptions and stores the subscription
 * ID in tblhosting.subscriptionid, this function is called upon cancellation
 * or request by an admin user.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/subscription-management/
 *
 * @return array Transaction response status
 */
function intasendpayment_cancelSubscription($params)
{
    // Gateway Configuration Parameters
    $publicKey = $params['publicKey'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    // $dropdownField = $params['dropdownField'];
    // $radioField = $params['radioField'];
    // $textareaField = $params['textareaField'];

    // Subscription Parameters
    $subscriptionIdToCancel = $params['subscriptionID'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to cancel subscription and interpret result

    // return array(
    //     // 'success' if successful, any other value for failure
    //     'status' => 'success',
    //     // Data to be recorded in the gateway log - can be a string or array
    //     'rawdata' => $responseData,
    // );
}
