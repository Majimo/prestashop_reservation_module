<?php

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../config/smarty.config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');


$context->cookie->__unset('id_reservation');


$reservation = Module::getInstanceByName('Reservation');

if(Tools::isSubmit('bouton')) {
    $bungalo_num = Tools::getValue('bungalo_num');
    $id_borne = Tools::getValue('id_borne');
    $date_debut = Tools::getValue('date_debut');
    $date_fin = Tools::getValue('date_fin');

    if (!$id_borne || !$date_debut || !$date_fin) {
        echo("Vous devez rentrer tous les champs demandés.");
        return ;
    }

    $annee = substr($date_debut, 0, 4);
    $mois = substr($date_debut, 5, 6);
    $jour = substr($date_debut, 8, 9);

    settype($annee, "integer");
    settype($mois, "integer");
    settype($jour, "integer");

    if ($annee != 0 && $mois != 0 && $jour != 0) {
        if ($date_fin == "m" || $date_fin == "a") {
        $products_ids = 8;
        } else {
            $products_ids = 9;        
        }

        if ($date_fin == "m") {
            $date_fin = $date_debut." 12:00:00";
        }
        else {
            $date_fin = $date_debut." 18:00:00";
            if ($date_fin == "a") {
                $date_debut .= " 14:00:00";
            }
        }

        $date_debut .= " 08:00:00";

        $random_digicode = intval( "0" . rand(1,9) . rand(0,9) . rand(0,9) . rand(0,9));


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
                $cart->updateQty(1, $id_product);   
            }
        }

        Db::getInstance()->insert('dpr_reservation', array(
            'id_client' => $cookie->id_customer,
            'bungalo_num' => $bungalo_num,
            'digicode' => $random_digicode,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'id_borne' => $id_borne,
            'id_cart' => $cart->id,
            'annulation' => 0
        ));

        /*global $smarty;

        $infos = Db::getInstance()->executeS('SELECT MAX(id), id_borne, firstname, lastname, date_debut, date_fin, email FROM `ps_dpr_reservation`JOIN `ps_customer` ON `id_client` = `id_customer` WHERE `annulation` != 1 AND `rendu` != 1 AND `id_client` ='.$cookie->id_customer.' AND `date_debut` = "'.$date_debut.'" AND `date_fin` = "'.$date_fin.'"');

        $id_borne = "La Réserve";
        if ($infos['id_borne'] == "siblu03") {
            $id_borne = "Les Dunes de Contis";
        }

        $sujet = 'Votre Réservation de Vélo Siblu '.$id_borne;
        $date_debut = date("d M Y à G:i", strtotime($infos['date_debut']));
        $date_fin = date("d M Y à G:i", strtotime($infos['date_fin']));
        $donnees = array('{nom}' => $infos['lastname'], '{prenom}' => $infos['firstname'],'{date_debut}' => $date_debut, '{date_fin}' => $date_fin,  '{digicode}' => $random_digicode);
        $destinataire = $infos['email'];
     
        Mail::Send(intval($cookie->id_lang), 'envoiDigicode', $sujet , $donnees, $destinataire, NULL, NULL, NULL, NULL, NULL, '../mails/');

        $context->cookie->__set('id_reservation' , $infos['id']);*/

        Tools::redirect('order');
    } else {
        echo("Vous devez renseigner correctement la date de réservation.");
        return ;
    }
    
}

?>