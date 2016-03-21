<?php
    /*
    Developed by Robert Thayer, GamerGadgets.net
    Contact: rob@gamergadgets.net
    Simple Script using the PayPal REST API for an express checkout payment.
    You may use this code as you see fit. I do not offer support with this script.
    */
    // Take a payID and Payer ID from return.php and confirm the payment.
    include_once('restfunctions.php');
    $payerID = $_POST['payerId'];
    $payID = $_POST['payId'];
    doExpressCheckout($payID, $payerID);
?>
