<?php

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../config/smarty.config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');


$reservation = Module::getInstanceByName('Reservation');

if(Tools::isSubmit('bouton')) {

    $result = array();
    $result = Tools::getValue('location');

    $traitement = [0, 0, 0];

    // Get Id Cart        
    $context = Context::getContext();
    $id_cart = $context->cookie->__get('id_cart');

    foreach ($result as $key) {
        $result[$key] = $key;

        if ($result[$key] == "casque") {
            $traitement[0] = 1;
            $cart = new Cart($id_cart);
            $cart->id_currency = 2;
            $cart->id_lang = 1;
            $cart->updateQty(1, 11);
        }

        if ($result[$key] == "carriole") {
            $traitement[1] = 1;
            $cart = new Cart($id_cart);
            $cart->id_currency = 2;
            $cart->id_lang = 1;
            $cart->updateQty(1, 12);
        }

        if ($result[$key] == "siege") {
            $traitement[2] = 1;
            $cart = new Cart($id_cart);
            $cart->id_currency = 2;
            $cart->id_lang = 1;
            $cart->updateQty(1, 13);
        }
    }
    
    Tools::redirect('order');
}

?>