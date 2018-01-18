<?php
/*
* 2007-2016 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class ReservationPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;
	private $orderId;

	public function setOrderId ($OrderId) { 
		return ($this->orderId=$OrderId); 
	}

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		session_start();
	    if(!isset($_SESSION['static']) && empty($_SESSION['static'])) {
	       $_SESSION['static'] = 10;
	    } else {
			if ($_SESSION['static'] == 10000) {
				session_destroy();
			} 
	     	$_SESSION['static'] = $_SESSION['static'] + 10;
		}

		parent::initContent();

		$cart = $this->context->cart;

		$this->context->smarty->assign(array(
			'nbProducts' 	=> $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' 	=> $this->module->getCurrency((int)$cart->id_currency),
			'total' 		=> $cart->getOrderTotal(true, Cart::BOTH),
			'this_path' 	=> $this->module->getPathUri(),
			'this_path_bw' 	=> $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		$this->orderId = "";
	    $this->appAccountId = "";
	    $this->base_return_url = "";

	    $product_array = $cart->getProducts(); 
   		$cart_id =  Context::getContext()->cart->id;


   		$this->setOrderId($this->generateReference());

   		// Ajout module de paiement SMoney

   		$this->list = $this->getPayments();
   		$this->list = $this->getList($this->list);

   		$this->httpPostUrl("POST", "payins/cardpayments/", $this->list);

	}

	public static function generateReference()
	{
	    $last_id = Db::getInstance()->getValue('
	        SELECT MAX(id_order)
	        FROM '._DB_PREFIX_.'orders');

	    return str_pad((int)$last_id + 2, 9, STR_PAD_LEFT);
	 
	}

	public function getPayments() 
	{
		if ($this->context->cookie->id_cart) {
    		$cart = $this->context->cart;
  		}

  		$verif_total = 0;
  		$product_array = $cart->getProducts();
  		$payments = array();

  		$payments[0] = array(
			    'amount'  		  => $cart->getOrderTotal() * 100,
			    'orderId'	 	  => $this->orderId.'-1_DnR',
			    'beneficiary'	  => ["appaccountid" => 'dropbird-com'], // ATTENTION : dropbird-com en PROD
			    'message' 		  => 'Réservation Dropn\' Ride',
			    'fee' 			  => 0
			);

  		/*foreach ($product_array as $key => $product_item) {
  			$total_price = (float)$product_item['total_wt'];

  			$payments[$key] = array(
			    'amount'  		  => $total_price * 100,
			    'orderId'	 	  => $this->orderId.'-'.($key + 1).'_DnR',
			    'beneficiary'	  => ["appaccountid" => 'dropbird-com'], // ATTENTION : dropbird-com en PROD
			    'message' 		  => $product_item['name'],
			    'fee' 			  => 0
			);

			$verif_total = $verif_total + $payments[$key]['amount'];
  		}*/

  		if ($payments[0]['amount'] >= 800) {
  			return $payments;
  		} else {
  			$cms = new CMS(10);
            $linktoSuivi = $this->context->link->getCMSLink($cms);
            Tools::redirect($linktoSuivi);
  		}

	}

	public function getList($payments) 
	{
		$cms = new CMS(7);
		$this->base_return_url = $this->context->link->getPageLink('index',true).'?fc=module&module=reservation&controller=redirect';

		$mylist = array (
			'orderId'         => $this->orderId, //.'toto',
			'availableCards'  => 'CB;MASTERCARD;VISA',
			'payments'        => $payments,
			'ismine' 		  => false,
			'urlReturn'       => $this->base_return_url
		);
		
		return json_encode($mylist);
	}

	public function httpPostUrl($typeCall, $nameUrl, $data) 
	{
		/*
		** COMPTE REEL SMoney
		*/
		$url = "https://rest.s-money.fr/api/B2B/";
		$token = "Mjc7NztyX0lMREgzMld6";

		/*
		** SANDBOX
		*/
		/*$url = "https://rest-pp.s-money.fr/api/sandbox/";
		$token = "NTsxNzI7aDhXZ0ZMNndIRw==";*/

		if (!$url || !$token) {
			$this->displayError("une erreur interne est parvenu, veuillez contacter l'administrateur (url/token)");
		    return;
		}

		$header = array(
		   'Accept: application/vnd.s-money.v2+json',
		   'Content-Type: application/vnd.s-money.v2+json',
		   'Authorization: Bearer '. $token);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url. $nameUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);                                                                   
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$authToken = curl_exec($ch);

		if (curl_errno($ch) != 0) {
		    $this->displayError("Une erreur est survenue lors de l'appel de l'API, veuillez vérifier votre connexion internet");
		    return;
		}

		curl_close($ch);
		$curl_jason = json_decode($authToken);	

			/*var_dump($curl_jason->{'Code'});
			azertyuiop();*/

		/*if ($curl_jason->{'Code'} != 0 && $curl_jason->{'Code'} != 710) {
    		$this->context->cookie->test_error = $curl_jason->{'Code'};
    		$this->displayError($curl_jason->{'ErrorMessage'});
  		}
  		else */if ($curl_jason->{'Code'} == 710)
		{
	  		// Order ID already exist
	   		$this->setOrderId (str_pad((int)$this->orderId . '-'. ($_SESSION['static'] + 1), 9, STR_PAD_LEFT));
	   		$this->list = $this->getPayments();
	   		$this->list = $this->getList($this->list);
	   		$this->httpPostUrl("POST", "payins/cardpayments/", $this->list);
	  	} 
	  	else 
	  	{
	    	Tools::redirect($curl_jason->{'Href'});
	  	}

	}


	protected function displayError($message, $description = false)
	{
        /**
         * Create the breadcrumb for your ModuleFrontController.
         */
        $this->context->smarty->assign('path', '
          <a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Payment').'</a>
          <span class="navigation-pipe">&gt;</span>'.$this->module->l('Error'));

        /**
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('error.tpl');
    }
}
