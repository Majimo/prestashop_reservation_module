<?php

require_once dirname(__FILE__) . '/../../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../../config/smarty.config.inc.php';
require_once dirname(__FILE__) . '/../../../init.php';
require __DIR__ . '/vendor/autoload.php';
use \Ovh\Api;

$context = Context::getContext();

/*$last_id = $context->cookie->id_reservation;

if ($last_id != NULL) {
	$infos = Db::getInstance()->getRow('SELECT r.`id_borne`, c.`firstname`, c.`lastname`, r.`date_debut`, r.`date_fin`, c.`email`, r.`digicode`, a.`phone_mobile` FROM `ps_dpr_reservation` AS `r` JOIN `ps_customer` AS `c` ON r.`id_client` = c.`id_customer` JOIN `ps_address` AS `a` ON c.`id_customer` = a.`id_customer` WHERE r.`annulation` != "1" AND r.`rendu` != "1" AND r.`id_client` = ' . $cookie->id_customer . ' AND r.`id` = "' . $last_id . '" AND r.`id_cart` = "' . $cart->id . '"');
} else {*/
$id_select = Db::getInstance()->getRow('SELECT MAX(id) FROM `ps_dpr_inter_reservation` WHERE `id_client` = "' . $cookie->id_customer . '" AND `id_cart` = "' . $cart->id . '"');

$infos = Db::getInstance()->getRow('SELECT r.`id_borne`, c.`firstname`, c.`lastname`, r.`date_debut`, r.`date_fin`, c.`email`, r.`digicode`, a.`phone_mobile` FROM `ps_dpr_inter_reservation` AS `r` JOIN `ps_customer` AS `c` ON r.`id_client` = c.`id_customer` JOIN `ps_address` AS `a` ON c.`id_customer` = a.`id_customer` WHERE r.`annulation` != "1" AND r.`rendu` != "1" AND r.`id_client` = "' . $cookie->id_customer . '" AND r.`id` = "'.$id_select["MAX(id)"].'" AND r.`id_client` = "' . $cookie->id_customer . '" AND r.`id_cart` = "' . $cart->id . '"');

// }

/*var_dump($id_select);
var_dump($infos);
var_dump($infos['id_borne']);
azerty();*/


$id_borne = "La Réserve";
if ($infos['id_borne'] == "siblu03") {
	$id_borne = "Les Dunes de Contis";
}
$sujet = 'Votre Réservation de Vélo Siblu ' . $id_borne;
$date_debut = date("d M Y à G:i", strtotime($infos['date_debut']));
$date_fin = date("d M Y à G:i", strtotime($infos['date_fin']));
$donnees = array('{nom}' => $infos['lastname'], '{prenom}' => $infos['firstname'], '{date_debut}' => $date_debut, '{date_fin}' => $date_fin);
$destinataire = $infos['email'];

Mail::Send(intval($cookie->id_lang), 'confirmation', $sujet, $donnees, $destinataire, NULL, NULL, NULL, NULL, NULL, '../mails/');

$context->cookie->__unset('id_reservation');

$linktoSuivi = $link->getModuleLink('reservation', 'payment');

Tools::redirect('index.php?fc=module&module=reservation&controller=payment');

?>