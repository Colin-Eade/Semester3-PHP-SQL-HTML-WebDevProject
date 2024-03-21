<?php
include_once "lib/db.php";
include_once "lib/functions.php";
include_once "config/constants.php";
// IPN listener to handle PayPal Instant Payment Notification messages

$raw_post_array = explode('&', file_get_contents('php://input'));
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}
$req = 'cmd=_notify-validate';

foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

$paypal_url = "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr";
$ch = curl_init($paypal_url);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

if (!($res = curl_exec($ch))) {
    error_log("Curl failed with error: " . curl_error($ch));
    curl_close($ch);
    exit;
}

curl_close($ch);


if (strcmp($res, "VERIFIED") == 0) {

    $transaction_id = $myPost['txn_id'];
    $payment_status = $myPost['payment_status'];
    $item_name = $myPost['item_name'];
    $payment_amount = $myPost['mc_gross'];
    $buyer_email = $myPost['payer_email'];

    $db_connect = db_connect();

    $query = 'INSERT INTO TRANSACTIONS(transaction_id, payment_status, item_name, payment_amount, buyer_email) 
              VALUES($1, $2, $3, $4, $5)';
    pg_query_params($db_connect, $query, array($transaction_id ,$payment_status, $item_name, $payment_amount, $buyer_email));

} elseif (strcmp($res, "INVALID") == 0) {
    error_log("Invalid IPN: $req");
}

header("HTTP/1.1 200 OK");

