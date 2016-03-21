<?php
    /*
    Developed by Robert Thayer, GamerGadgets.net
    Contact: rob@gamergadgets.net
    Simple Script using the PayPal REST API for an express checkout payment.
    You may use this code as you see fit. I do not offer support with this script.
    */
    $token; // Used to hold the global token so you don't have to request a new token every time.
    class Token
    {
        // Replace these values with your own sandbox Client ID and Secret. You can obtain these at developer.paypal.com
        const CLIENT_ID = "";
        const SECRET = "";
        
        public $oauth; // Hold the OAuth Token
        public $expires_at; // Holds when the OAuth token expires
        
        // Constructor will get an oauth token
        function __construct() {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://api.sandbox.paypal.com/v1/oauth2/token',
                CURLOPT_POST => 1,
                CURLOPT_USERPWD => self::CLIENT_ID . ":" . self::SECRET,
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
                CURLOPT_SSLVERSION => 6
                )
            );
            
            $resp = curl_exec($ch);
            if(!curl_exec($ch)){
                // If an error, display error and break
                die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            } else {
                // Decode response
                $json = json_decode($resp, true);
                
                // Set Oauth token
                $this->oauth = "Authorization: Bearer " . $json['access_token'];
                // Determine when token expires and store the time
                $this->expires_in = time() + (int) $json['expires_in'];
            }
            curl_close($ch);
        }
        
        // Returns the Oauth token
        function getOAuth() {
            return $this->oauth;
        }
        
        // Returns bool whether token is expired
        function isExpired() {
            return (time() >= $this->expires_at);
        }
    }

    class PayPalPayment
    {
        // When class is created, determine if the token has expired or if there isn't one yet.
        // If so, obtain a new one. If not, use existing token
        function PayPalPayment() {
            global $token;
            if (is_a($token, 'Token')) {
                if (!$token->isExpired()) {
                    break;
                }
            } else {
                $token = new Token();
            }
           
        }
        
        // Run payment and get EC token
        function executePayment($amount) {
            global $token;
            $oauth = $token->getOAuth();
            $transaction = array(
                "amount" => array(
                            "currency" => "USD",
                            "total" => strval($amount)
                            ),
                        "description" => "This is for an awesome shirt!"
                        );
            $postvars = array(
                "transactions" => array($transaction),
                    "payer" => array(
                        "payment_method" => "paypal"
                        ),
                    "intent" => "sale",
                    "redirect_urls" => array(
                        "cancel_url" => "", // Enter your own cancel URL
                        "return_url" => "" // Enter your own return URL
                    )
                );
            
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://api.sandbox.paypal.com/v1/payments/payment',
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => array($oauth, 'Content-Type:application/json'),
                CURLOPT_POSTFIELDS => json_encode($postvars),
                CURLOPT_SSLVERSION => 6
            ));
            
            $resp = curl_exec($ch);
            if(!curl_exec($ch)){
                die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            } else {
                $json = json_decode($resp, true);
            }
            curl_close($ch);
            
            // Check to make sure the payment token successfully created.
            // If so, redirect customer to the referral link.
            if ($json['state'] == 'created') {
                header( 'Location: ' . $json['links'][1]['href']);
            }
        }
    }

// Obtain shipping information
function lookupPayment($payerId) {
    // Use existing token if available
    global $token;
    if (is_a($token, 'Token')) {
        if (!$token->isExpired()) {
            break;
        }
    } else {
        $token = new Token();
    }
    // Get current OAuth token
    $oauth = $token->getOAuth();
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://api.sandbox.paypal.com/v1/payments/payment/' . $payerId,
        CURLOPT_HTTPHEADER => array($oauth, 'Content-Type:application/json'),
        CURLOPT_SSLVERSION => 6
        )
    );

    $resp = curl_exec($ch);
    if(!curl_exec($ch)){
        die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
    } else {
        $json = json_decode($resp, true);
    }
    curl_close($ch);
    
    // Grab shipping name and address
    $shipToName = $json['payer']['payer_info']['shipping_address']['recipient_name'];
    $address1 = $json['payer']['payer_info']['shipping_address']['line1'];
    if (isset($json['payer']['payer_info']['shipping_address']['line2'])) {
        $address2 = $json['payer']['payer_info']['shipping_address']['line2'];
    }
    $city = $json['payer']['payer_info']['shipping_address']['city'];
    $state = $json['payer']['payer_info']['shipping_address']['state'];
    $zip = $json['payer']['payer_info']['shipping_address']['postal_code'];
    $country = $json['payer']['payer_info']['shipping_address']['country_code'];
        
    echo $shipToName . '<br>';
    echo $address1 . '<br>';
    if (isset($address2)) {
        echo $address1 . '<br>';
    }
    echo $city . ', ' . $state . '  ' . $zip . '<br>';
    echo $country;
}

// Complete the payment.
// $payId is the Payment ID token used in the curl request
// $payerId is the payerID of the PayPal payer
function doExpressCheckout($payId, $payerId) {
    // Use existing token if available
    global $token;
    if (is_a($token, 'Token')) {
        if (!$token->isExpired()) {
            break;
        }
    } else {
        $token = new Token();
    }
    $oauth = $token->getOAuth();
    $ch = curl_init();
    $postvars = array(
        "payer_id" => $payerId
        );
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://api.sandbox.paypal.com/v1/payments/payment/' . $payId . '/execute/',
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => array($oauth, 'Content-Type:application/json'),
        CURLOPT_POSTFIELDS => json_encode($postvars),
        CURLOPT_SSLVERSION => 6
        ));
    $resp = curl_exec($ch);
    if(!curl_exec($ch)){
        die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
    } else {
        $json = json_decode($resp, true);
        
    }
    curl_close($ch);
    
    // If all goes well, thank the customer!
    if ($json['state'] == 'approved') {
        echo "<br><strong>Thank you for your payment. Order approved!</strong>";
    }
}
?>
