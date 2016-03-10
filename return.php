<!-- 

Developed by Robert Thayer, GamerGadgets.net
Contact: rob@gamergadgets.net
Simple Script using the PayPal REST API for an express checkout payment.
You may use this code as you see fit. I do not offer support with this script.

-->

<!DOCTYPE html>
<head><title>Confirm your Details</title></head>
<body><h1>Thank you for your order.</h1>
<p>You're not done, yet, though!</p>
<p>Please review the order details and click the confirm button at the bottom to get your awesome t-shirt of awesome.</p>
<p><strong>We will ship this shirt to:</strong></p>
<?php
include_once('restfunctions.php');
$payerID = $_GET['PayerID'];
$payID = $_GET['paymentId'];
// Get shipping address
lookupPayment($payID);
?>
    
<p>If this is correct, please click the 'Confirm' button below:</p>
<form action="doexpress.php" method="post">
    <input type="submit" value="Confirm">
    <input type="hidden" name="payerId" value="<?php echo $payerID; ?>">
    <input type="hidden" name="payId" value="<?php echo $payID; ?>">
</form>
</body>