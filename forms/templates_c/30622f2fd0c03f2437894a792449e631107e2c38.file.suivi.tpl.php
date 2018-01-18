<?php /* Smarty version Smarty-3.1.19, created on 2017-06-13 13:58:54
         compiled from "C:\xampp\htdocs\prestavelo\modules\reservation\views\templates\hook\suivi.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1809565493593fd37eddd022-56735285%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '30622f2fd0c03f2437894a792449e631107e2c38' => 
    array (
      0 => 'C:\\xampp\\htdocs\\prestavelo\\modules\\reservation\\views\\templates\\hook\\suivi.tpl',
      1 => 1497353989,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1809565493593fd37eddd022-56735285',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'reservations_list' => 0,
    'reservation' => 0,
    'date_reservation' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_593fd37ee04535_52706914',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_593fd37ee04535_52706914')) {function content_593fd37ee04535_52706914($_smarty_tpl) {?><p style="color: red;font-weight: bold;">Attention ! Votre réservation ne sera prise en compte que lorsque vous aurez effectué votre paiement ou utilisez le code fourni lors de votre réservation Siblu.</p>
<p>Liste des différentes réservations que vous avez effectué :</p>

<?php  $_smarty_tpl->tpl_vars['reservation'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['reservation']->_loop = false;
 $_smarty_tpl->tpl_vars['i'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['reservations_list']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['reservation']->key => $_smarty_tpl->tpl_vars['reservation']->value) {
$_smarty_tpl->tpl_vars['reservation']->_loop = true;
 $_smarty_tpl->tpl_vars['i']->value = $_smarty_tpl->tpl_vars['reservation']->key;
?>
    <?php echo $_smarty_tpl->tpl_vars['reservation']->value;?>

<?php } ?>

<?php echo $_smarty_tpl->tpl_vars['date_reservation']->value;?>
<?php }} ?>
