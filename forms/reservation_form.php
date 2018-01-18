<?php

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../config/smarty.config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');

$context->cookie->__unset('id_reservation');

$context = Context::getContext();
$id_client = $context->cookie->id_customer;

$reservation = Module::getInstanceByName('Reservation');

if(Tools::isSubmit('bouton')) {
    $bungalo_num = Tools::getValue('bungalo_num');
    $id_borne = Tools::getValue('id_borne');
    $date_debut = Tools::getValue('date_debut');
    $date_fin = Tools::getValue('date_fin');
    $nb_velo = Tools::getValue('nb_velo');

    if (!$id_borne || !$date_debut || !$date_fin || !$bungalo_num || $id_client == 0) {
        echo "Vous devez rentrer tous les champs demandés ainsi qu'être <b>inscrit et connecté</b> à la plateforme Drop n' Ride pour pouvoir effectuer une réservation. Merci de votre compréhension";
        $cms = new CMS(6);
        $linktoSuivi = $context->link->getCMSLink($cms);
        echo "<br /><a href='".$linktoSuivi."'>Retour à la page de réservation</a> ou <a href='https://www.dropnride.fr/fr/mon-compte'>Connectez vous ici</a>";
        return ;
    }

    $jour = substr($date_debut, 3, 2);
    $mois = substr($date_debut, 0, 2);
    $annee = substr($date_debut, 6, 4);

    $date_debut = $annee."-".$mois."-".$jour;
	if ($mois == '01' || $mois == '03' || $mois == '05' || $mois == '07' || $mois == '08' || $mois == '10' || $mois == '12') {
		if ($jour == 31 && $mois != 12) {
			$date_nuit = $annee."-".($mois + 1)."-01";
		} elseif ($jour == 31 && $mois == 12) {
			$date_nuit = ($annee + 1)."-01-01";                              // Jour de l'an
		} else {
			$date_nuit = $annee."-".$mois."-".($jour + 1);
		}	
	} elseif ($mois == '04' || $mois == '06' || $mois == '09' || $mois == '11') {
		if ($jour == 30) {
			$date_nuit = $annee."-".($mois + 1)."-01";
		} else {
			$date_nuit = $annee."-".$mois."-".($jour + 1);
		}
	} elseif ($mois == 02) {
		if (idate("L", mktime(0, 0, 0, 0, 0, $annee + 1)) == 1) {             // Année bisextile
            if ($jour == 29) {
                $date_nuit = $annee."-".($mois + 1)."-01";
            } else {
				$date_nuit = $annee."-".$mois."-".($jour + 1);
			}
		} elseif ($jour == 28) {
            $date_nuit = $annee."-".($mois + 1)."-01";
        } else {
			$date_nuit = $annee."-".$mois."-".($jour + 1);
		}
	}

    settype($annee, "integer");
    settype($mois, "integer");
    settype($jour, "integer");


    if ($annee != 0 && $mois != 0 && $jour != 0) {

        $date_php = date("Y-m-d", strtotime($date_debut));
        $today = date("Y-m-d", $timestamp = time());

        if ($date_php < $today) {
            $cms = new CMS(10);
            $linktoSuivi = $context->link->getCMSLink($cms);
            Tools::redirect($linktoSuivi);
        }

        $products_ids = 9;

        if ($date_fin == "m" || $date_fin == "a") {
        $products_ids = 8;
        } elseif ($date_fin == 'n') {
            $products_ids = 14;        
        }

        if ($date_fin == "m") {
            $date_fin = $date_debut." 13:00:00";
            $date_debut .= " 08:00:00";
        } elseif ($date_fin == 'a') {
            $date_fin = $date_debut." 19:00:00";
            $date_debut .= " 14:00:00";
        } elseif ($date_fin == 'd') {
            $date_fin = $date_debut." 19:00:00";
            $date_debut .= " 08:00:00";
        } else {
            $date_fin = $date_nuit." 07:00:00";
            $date_debut .= " 20:00:00";
        }

        // $random_digicode = intval( "0" . rand(1,9) . rand(0,9) . rand(0,9) . rand(0,9));

        $sqlR = "SELECT date_debut, date_fin, id_borne FROM ps_dpr_reservation WHERE rendu != '1'";
        $resultSql = Db::getInstance()->executeS($sqlR);

        // Get Id Cart        
        $context = Context::getContext();

        // Add cart if no cart found
        if (!$context->cart->id)
        {
            if (Context::getContext()->cookie->id_guest)
            {
                $guest = new Guest(Context::getContext()->cookie->id_guest);
                $context->cart->mobile_theme = $guest->mobile_theme;
            }
            $context->cart->add();
            if ($context->cart->id) {
                $context->cookie->id_cart = (int)$context->cart->id;
            }
        }

        $id_cart = $context->cookie->__get('id_cart');                
        // $cart = new Cart($id_cart);     
        $qts = 1;
        $products_ids_array=explode(",",$products_ids);
        $countParam = count($products_ids_array);
        
        // Add Product to Cart
        if($countParam>0) {
            $cart = new Cart($id_cart);
            $cart->id_currency = 2;
            $cart->id_lang = 1;
            foreach($products_ids_array as $key=>$id_product){
                $cart->updateQty($nb_velo, $id_product);   
            }
        }

        $id_select = Db::getInstance()->getRow('SELECT MAX(id) FROM `ps_dpr_reservation`');

        $id_select = $id_select['MAX(id)'] + 1;

        Db::getInstance()->insert('dpr_inter_reservation', array(
            'id' => $id_select,
            'id_client' => $cookie->id_customer,
            'bungalo_num' => $bungalo_num,
            'nb_velos' => $nb_velo,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'id_borne' => $id_borne,
            'id_cart' => $cart->id,
            'date_reservation' => date('Y-m-d H:i:s', time())
        ));

        Tools::redirect('order');
    } else {
        echo("Vous devez renseigner correctement la date de réservation.");
        return ;
    }
    
}

?>