<!-- 

Developed by Robert Thayer, GamerGadgets.net
Contact: rob@gamergadgets.net
Simple Script using the PayPal REST API for an express checkout payment.
You may use this code as you see fit. I do not offer support with this script.

-->

<?php

// Script takes a payment amount from index.html and creates a PayPal payment for that amount.
    include_once('restfunctions.php');
    $amount = $_POST['amount'];
    $payment = new PayPalPayment();
    $payment->executePayment($amount);
?>