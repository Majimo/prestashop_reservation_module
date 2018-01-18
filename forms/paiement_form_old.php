<?php

require_once dirname(__FILE__) . '/../../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../../config/smarty.config.inc.php';
require_once dirname(__FILE__) . '/../../../init.php';
require __DIR__ . '/vendor/autoload.php';
use \Ovh\Api;

$context = Context::getContext();

$last_id = $context->cookie->id_reservation;

if ($last_id != NULL) {
	$infos = Db::getInstance()->getRow('SELECT r.id_borne, c.firstname, c.lastname, r.date_debut, r.date_fin, c.email, r.digicode, a.phone_mobile FROM `ps_dpr_reservation` AS `r` JOIN `ps_customer` AS `c` ON r.id_client = c.id_customer JOIN `ps_address` AS `a` ON c.id_customer = a.id_customer WHERE r.`annulation` != 1 AND r.`rendu` != 1 AND r.`id_client` = ' . $cookie->id_customer . ' AND r.`id` = "' . $last_id . '" AND r.`id_cart` = "' . $cart->id . '"');
} else {
	$infos = Db::getInstance()->getRow('SELECT id_borne, firstname, lastname, date_debut, date_fin, email, digicode FROM `ps_dpr_reservation` JOIN `ps_customer` ON `id_client` = `id_customer` WHERE `annulation` != 1 AND `rendu` != 1 AND `id` = (SELECT MAX(id) FROM `ps_dpr_reservation` WHERE `id_client` = ' . $cookie->id_customer . ' AND `id_cart` = "' . $cart->id . '") AND `id_client` = ' . $cookie->id_customer . ' AND `id_cart` = "' . $cart->id . '"');
}

$id_borne = "La Réserve";
if ($infos['id_borne'] == "siblu03") {
	$id_borne = "Les Dunes de Contis";
}

$sujet = 'Votre Paiement de Vélo Siblu ' . $id_borne;
$date_debut = date("d M Y à G:i", strtotime($infos['date_debut']));
$date_fin = date("d M Y à G:i", strtotime($infos['date_fin']));
$donnees = array('{nom}' => $infos['lastname'], '{prenom}' => $infos['firstname'], '{date_debut}' => $date_debut, '{date_fin}' => $date_fin, '{digicode}' => $infos['digicode']);
$destinataire = $infos['email'];

$endpoint = 'ovh-eu';
$applicationKey = "gCR1VuJQ8OEtB48x";
$applicationSecret = "XuecrbVv0epJ1wA1SJUKI37zyTnKW6qG";
$consumer_key = "tenelCMQdQUK6FafzI51ovH1PLCPCmcC";

$conn = new Api($applicationKey,
	$applicationSecret,
	$endpoint,
	$consumer_key);

$smsServices = $conn->get('/sms/');

$phoneMobile = $infos['phone_mobile'];

if ($phoneMobile[0] <= '9' AND phoneMobile[0] >= '0') {
	$phoneMobile = "+33" . substr($infos['phone_mobile'], 1, strlen($infos['phone_mobile']) - 1);
}

$content = (object) array(
	"charset" => "UTF-8",
	"class" => "phoneDisplay",
	"coding" => "7bit",
	"message" => "Bonjour vous avez réservez votre vélo du " . $date_debut . " au " . $date_fin . ". Votre digicode est le: " . $infos['digicode'],
	"noStopClause" => false,
	"priority" => "high",
	"receivers" => [$phoneMobile],
	"sender" => "Dropnride",
	"senderForResponse" => true,
	"validityPeriod" => 2880,
);

$resultPostJob = $conn->post('/sms/' . $smsServices[0] . '/jobs/', $content);
$smsJobs = $conn->get('/sms/' . $smsServices[0] . '/jobs/');

Mail::Send(intval($cookie->id_lang), 'paiement', $sujet, $donnees, $destinataire, NULL, NULL, NULL, NULL, NULL, '../mails/');

$sql = Db::getInstance()->executeS('UPDATE `ps_dpr_reservation` SET `paid` = 1 WHERE `annulation` != 1 AND `rendu` != 1 AND `id` = ' . $last_id . ' AND `id_client` = ' . $cookie->id_customer . ' AND `id_cart` = "' . $cart->id . '"');

$context->cookie->__unset('id_reservation');

$linktoSuivi = $link->getModuleLink('reservation', 'payment');
Tools::redirect('index.php?fc=module&module=reservation&controller=payment');

?>