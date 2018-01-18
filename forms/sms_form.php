<?php

require_once dirname(__FILE__) . '/../../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../../config/smarty.config.inc.php';
require_once dirname(__FILE__) . '/../../../init.php';
require __DIR__ . '/vendor/autoload.php';
use \Ovh\Api;

$cart_id = Tools::getValue('cart');

$context = Context::getContext();
$id_client = $context->cookie->id_customer;

$infos = Db::getInstance()->executeS('SELECT r.`id_borne`, c.`firstname`, c.`lastname`, r.`date_debut`, r.`date_fin`, c.`email`, r.`digicode`, a.`phone_mobile` FROM `ps_dpr_reservation` AS `r` JOIN `ps_customer` AS `c` ON r.`id_client` = c.`id_customer` JOIN `ps_address` AS `a` ON c.`id_customer` = a.`id_customer` WHERE r.`annulation` != "1" AND r.`rendu` != "1" AND r.`id_client` = "' . $id_client . '" AND r.`id_cart` = "' . $cart_id. '"');

$date_debut = date("d M Y à G:i", strtotime($infos[0]['date_debut']));
$date_fin = date("d M Y à G:i", strtotime($infos[0]['date_fin']));

$id_borne = "Les Dunes de Contis";

$digi_text = "Votre digicode est le : ";

if (count($infos) > 1) {
	$digi_text = "Vos digicodes sont : ";
}

$digicode = $infos[0]['digicode'];

for ($i=1; $i < count($infos) ; $i++) {
	$digicode .= " - ".$infos[$i]['digicode'];
}

$sujet = 'Votre Digicode de retrait Vélo Siblu ' . $id_borne;
$donnees = array('{nom}' => $infos[0]['lastname'], '{prenom}' => $infos[0]['firstname'], '{date_debut}' => $date_debut, '{date_fin}' => $date_fin, '{digi_text}' => $digi_text, '{digicode}' => $digicode);

$destinataire = $infos[0]['email'];
$admin01 = 'contact@dropbird.fr';
$admin02 = 'pierre.fervel@dropbird.fr';
$admin03 = 'emilien.jegou@epitech.fr';

$endpoint = 'ovh-eu';
$applicationKey = "gCR1VuJQ8OEtB48x";
$applicationSecret = "XuecrbVv0epJ1wA1SJUKI37zyTnKW6qG";
$consumer_key = "tenelCMQdQUK6FafzI51ovH1PLCPCmcC";

$conn = new Api($applicationKey,
	$applicationSecret,
	$endpoint,
	$consumer_key);


$smsServices = $conn->get('/sms/');
$smsServices = '/sms/' . $smsServices[0] . '/jobs'; 

$phoneMobile = $infos[0]['phone_mobile'];
$phoneMobile = trim($phoneMobile);

$content = (object) array(
	"charset" => "UTF-8",
	"class" => "phoneDisplay",
	"coding" => "7bit",
	"message" => "Bonjour ".$infos[0]['firstname']." ".$infos[0]['lastname']." vous avez réservé votre vélo du " . $date_debut . " au " . $date_fin . ". ". $digi_text . $digicode,
	"noStopClause" => true,
	"priority" => "high",
	"sender" => "Dropnride",
	"receivers" => [$phoneMobile, '+33698729394'],
	"senderForResponse" => false,
	"validityPeriod" => '2880',
	);


$resultPostJob = $conn->post($smsServices, $content);
$smsJobs = $conn->get($smsServices);
Mail::Send(intval($cookie->id_lang), 'envoiDigicode', $sujet, $donnees, $destinataire, NULL, NULL, NULL, NULL, NULL, '../mails/');
Mail::Send(intval($cookie->id_lang), 'envoiDigicode', $sujet, $donnees, $admin01, NULL, NULL, NULL, NULL, NULL, '../mails/');
Mail::Send(intval($cookie->id_lang), 'envoiDigicode', $sujet, $donnees, $admin02, NULL, NULL, NULL, NULL, NULL, '../mails/');
Mail::Send(intval($cookie->id_lang), 'envoiDigicode', $sujet, $donnees, $admin03, NULL, NULL, NULL, NULL, NULL, '../mails/');


$cms = new CMS(7);
$linktoSuivi = $context->link->getCMSLink($cms);

Tools::redirect($linktoSuivi);