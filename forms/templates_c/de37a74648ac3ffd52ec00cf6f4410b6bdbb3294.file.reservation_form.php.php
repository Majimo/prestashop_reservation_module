<?php /* Smarty version Smarty-3.1.19, created on 2017-06-13 14:01:32
         compiled from "C:\xampp\htdocs\prestavelo\modules\reservation\forms\reservation_form.php" */ ?>
<?php /*%%SmartyHeaderCode:714885504593fd3eca53cf9-66916248%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'de37a74648ac3ffd52ec00cf6f4410b6bdbb3294' => 
    array (
      0 => 'C:\\xampp\\htdocs\\prestavelo\\modules\\reservation\\forms\\reservation_form.php',
      1 => 1497355284,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '714885504593fd3eca53cf9-66916248',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_593fd3eca6f554_86208176',
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_593fd3eca6f554_86208176')) {function content_593fd3eca6f554_86208176($_smarty_tpl) {?><<?php ?>?php

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../config/smarty.config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');


$reservation = Module::getInstanceByName('Reservation');

if(Tools::isSubmit('bouton')){
    $id_borne = Tools::getValue('id_borne');
	$date_debut = Tools::getValue('date_debut');
	$date_fin = Tools::getValue('date_fin');

		if (!$id_borne || !$date_debut || !$date_fin) {
	   		echo("Vous devez rentrer tous les champs demandés");
	   		return ;
        }

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
        'digicode' => $random_digicode,
        'date_debut' => $date_debut,
        'date_fin' => $date_fin,
        'id_borne' => $id_borne,
        'annulation' => 0
    ));

    $tpl = new Smarty();
    $date_reservation = Db::getInstance()->executeS('SELECT * FROM `ps_dpr_reservation` WHERE `annulation` != 1 AND `rendu` != 1 AND `id_client` ='.$cookie->id_customer.' AND `date_debut` = "'.$date_debut.'" AND `date_fin` = "'.$date_fin.'"');
    $tpl->assign('date_reservation','Hello World !');
    $tpl->display(__FILE__, '../suvi.tpl');

/*  $cms = new CMS(7);
    $linktoSuivi = $link->getCMSLink($cms); */
    Tools::redirect('order');

}

?<?php ?>><?php }} ?>
