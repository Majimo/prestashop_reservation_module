<?php
require_once dirname(__FILE__) . '/../../../../../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../../../../../config/smarty.config.inc.php';
require_once dirname(__FILE__) . '/../../../../../../init.php';

$date = $_POST['myDate'];
$crenau = $_POST['myInterval'];
if ($date == null || $date == 'null' || $crenau == null || $crenau == 'null') {
	return;
}
$mois = substr($date, 0, 2);
$jour = substr($date, 3, 2);
$year = substr($date, 6, 4);
$date = $year.'-'.$mois.'-'.$jour;
$velo_dispo = 4;

if (!strcmp($crenau, 'm')) {
	$dateDebut = $date . ' 08:00:00';
	$dateFin = $date . ' 13:00:00';
} else if (!strcmp($crenau, 'a')) {
	$dateDebut = $date . ' 14:00:00';
	$dateFin = $date . ' 19:00:00';
} else if (!strcmp($crenau, 'd')) {
	$dateDebut = $date . ' 08:00:00';
	$dateFin = $date . ' 19:00:00';
} else if (!strcmp($crenau, 'n')) {
	$dateDebut = $date . ' 20:00:00';
}

if (strcmp($crenau, 'n')) {
	$sql = "SELECT MAX(velo_num) FROM `ps_dpr_reservation` WHERE `date_debut` LIKE '" . $dateDebut . "' AND `date_fin` LIKE '" . $dateFin . "';";
	$result = DB::getInstance()->getValue($sql);
	$velo_dispo = $velo_dispo - $result;
}
else {
	$sql = "SELECT MAX(velo_num) FROM `ps_dpr_reservation` WHERE `date_debut` LIKE '" . $date . " 20:00:00';";
	$result = DB::getInstance()->getValue($sql);
	$velo_dispo = $velo_dispo - $result;
}

if (!strcmp($crenau, 'm') || !strcmp($crenau, 'a')) {
	$sql = "SELECT MAX(velo_num) FROM `ps_dpr_reservation` WHERE `date_debut` LIKE '" . $date . " 08:00:00' AND `date_fin` LIKE '" . $date . " 19:00:00';";
	$result = DB::getInstance()->getValue($sql);
	$velo_dispo = $velo_dispo - $result;
}
else if (!strcmp($crenau, 'd')) {
	$sql = "SELECT MAX(velo_num) FROM `ps_dpr_reservation` WHERE `date_debut` LIKE '" . $date . " 08:00:00' AND `date_fin` LIKE '" . $date . " 13:00:00';";
	$resultMatin = DB::getInstance()->getValue($sql);
	$sql = "SELECT MAX(velo_num) FROM `ps_dpr_reservation` WHERE `date_debut` LIKE '" . $date . " 14:00:00' AND `date_fin` LIKE '" . $date . " 19:00:00';";
	$resultSoir = DB::getInstance()->getValue($sql);
	$velo_dispo = $velo_dispo - max($resultMatin, $resultSoir);
}
echo $velo_dispo;
?>