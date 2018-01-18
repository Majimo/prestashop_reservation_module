<?php
/**
 * First, download the latest release of PHP wrapper on github
 * And include this script into the folder with extracted files
 */
require __DIR__ . '/vendor/autoload.php';
use \Ovh\Api;

/**
 * Instanciate an OVH Client.
 * You can generate new credentials with full access to your account on
 * the token creation page
 */
$endpoint = 'ovh-eu';
$applicationKey = "m5Si0oIRlggHm2xQ";
$applicationSecret = "PJvOF09cYykrKCl0jEpBsvVsQMBZDzWz";
$consumer_key = "Gft3iyh9i1tdo7nRwPEtBv9YZ0i0uuxE";

$ovh = new Api($applicationKey,
	$applicationSecret,
	$endpoint,
	$consumer_key);

$result = $ovh->get('/sms/sms-je44825-1');

echo '<pre>';
print_r($result);
echo '</pre>';
?>