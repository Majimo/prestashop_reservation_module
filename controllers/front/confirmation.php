<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Pierre Fervel (DropBird)
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ReservationConfirmationModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{

		$id_cart = $this->context->cart->id;
		$id_client = $this->context->cookie->id_customer;

		$id_select = Db::getInstance()->getRow('SELECT MAX(id) FROM `ps_dpr_inter_reservation` WHERE `id_client` = "' . $id_client . '" AND `id_cart` = "' . $id_cart . '"');

		$error = $this->context->cookie->test_error;

		if ($error != 0)
		{
			$message = $this->getPaymentErrorMessage($error);

		// $sql = Db::getInstance()->executeS('UPDATE `ps_dpr_reservation` SET `annulation` = 1 WHERE `rendu` != 1 AND `id` = "'.$id_select[0]["MAX(id)"].'" AND `id_client` = "' . $id_client . '" AND `id_cart` = "' . $id_cart. '"');

			switch ($error)
			{
				case '1':
				$status = Configuration::get('PS_OS_ERROR');
				break;
				case '2':
				$status = Configuration::get('PS_OS_ERROR');
				break;
				case '3':
				$status = Configuration::get('PS_OS_CANCELED');
				$sql = Db::getInstance()->executeS('UPDATE `ps_dpr_inter_reservation` SET `annulation` = 1 WHERE `rendu` != 1 AND `id` = "'.$id_select["MAX(id)"].'" AND `id_client` = "' . $id_client . '" AND `id_cart` = "' . $id_cart. '"');
				break;
				case '4':
				$status = Configuration::get('PS_OS_ERROR'); 	// Pierre F.: Renvoie une erreur car Erreur chez S-Money
				break;
				case '5':
				$status = Configuration::get('PS_OS_ERROR');
				break;
				case '6':
				$status = Configuration::get('PS_OS_ERROR');
				break;
				default:
				$status = Configuration::get('PS_OS_ERROR');
				break;
			}

		$cms = new CMS(7);
		$linktoSuivi = $this->context->link->getCMSLink($cms);
	}
	else 
	{
		$status = Configuration::get('PS_OS_PAYMENT');
		$message = "Transaction success";

		$sql = Db::getInstance()->executeS('UPDATE `ps_dpr_inter_reservation` SET `paid` = 1 WHERE `annulation` != 1 AND `id` = "'.$id_select["MAX(id)"].'" AND `id_client` = "' . $id_client . '" AND `id_cart` = "' . $id_cart. '"');

		$sql2 = Db::getInstance()->executeS('SELECT * FROM `ps_dpr_inter_reservation` WHERE `annulation` != 1 AND `id` = "'.$id_select["MAX(id)"].'" AND `id_client` = "' . $id_client . '" AND `id_cart` = "' . $id_cart. '"');

		$nb_velos = Db::getInstance()->executeS('SELECT `nb_velos` FROM `ps_dpr_inter_reservation` WHERE `annulation` != 1 AND `id` = "'.$id_select["MAX(id)"].'" AND `id_client` = "' . $id_client . '" AND `id_cart` = "' . $id_cart. '"');

		$nb_velos = intval($nb_velos[0]['nb_velos']);

		$i = 0;

		for ($i = 0; $i < $nb_velos; $i++) {
			$random_digicode = intval( "0" . rand(1,9) . rand(0,9) . rand(0,9) . rand(0,9));

			$sqlR = "SELECT date_debut, date_fin, id_borne FROM ps_dpr_reservation WHERE rendu != '1'";
			$resultSql = Db::getInstance()->executeS($sqlR);

			$velo_num = 1;

			for ($key = 0; $key < count($resultSql); $key++) {
				$int = strcmp($sql2[0]['date_debut'], $resultSql[$key]['date_debut']) + strcmp($sql2[0]['date_fin'], $resultSql[$key]['date_fin']) + strcmp($sql2[0]['id_borne'], $resultSql[$key]['id_borne']);
				if ($int == 0) {
					$velo_num++;
				}
			}

			Db::getInstance()->insert('dpr_reservation', array(
			'id' => intval($sql2[0]["id"]) + $i + 1,
			'velo_num' => intval($velo_num),
			'id_client' => intval($sql2[0]["id_client"]),
			'bungalo_num' => $sql2[0]["bungalo_num"],
			'digicode' => $random_digicode,
			'date_debut' => date('Y-m-d H:i:s', strtotime($sql2[0]["date_debut"])),
			'date_fin' => date('Y-m-d H:i:s', strtotime($sql2[0]["date_fin"])),
			'paid' => 1,
			'id_borne' => $sql2[0]["id_borne"],
			'id_cart' => intval($sql2[0]["id_cart"])
			));
		}		

		$linktoSuivi = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/reservation/forms/sms_form.php'.'?cart='.$id_cart;
	}

	$cart_id =  Context::getContext()->cart->id;
	$secure_key = Context::getContext()->customer->secure_key;

	$cart = new Cart((int)$cart_id);
	$customer = new Customer((int)$cart->id_customer);


	if (!Validate::isLoadedObject($customer))
		Tools::redirect('index.php?controller=order&step=1');

	/**
	* Converting cart into a valid order
	*/
	$module_name = $this->module->displayName;
	$currency_id = (int)Context::getContext()->currency->id;

	$this->module->validateOrder($cart_id, $status, $cart->getOrderTotal(), $module_name, $message, array(), $currency_id, false, $secure_key);

	/**
	* If the order has been validated we try to retrieve it
	*/
	$order_id = Order::getOrderByCartId((int)$cart->id);
	unset($_SESSION['static']);
	if ($order_id && ($secure_key == $customer->secure_key)) 
	{
		/**
		* The order has been placed so we redirect the customer on the confirmation page.
		*/
		Tools::redirect($linktoSuivi);
	} 
	else 
	{
		/**
		* An error occured and is shown on a new page.
		*/
		$this->errors[] = $this->module->l('An error occured. Please contact the merchant to have more informations');
		return $this->setTemplate('error.tpl');
	}
}

public function getPaymentErrorMessage($errorCode)
{
	$this->payment_errors = array(
		1 => 'Le commerçant doit contacter la banque du porteur',
		2 => 'Paiement refusé',
		3 => 'Paiement annulé par le client',
		4 => 'Porteur non enrôlé 3D-Secure',
		5 => 'Erreur authentification 3D-Secure',
		6 => 'Erreur technique SystemPay',
		710 => 'Erreur 710'
		);

	return $this->payment_errors[$errorCode];
}

}
