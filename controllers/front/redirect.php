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



class ReservationRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */

  public function postProcess()
  {
    if (Tools::getValue('action') == 'error') {
      return $this->displayError('An error occurred while trying to redirect the customer');
    }
    else {
      $this->context->smarty->assign(array(
        'cart_id' => Context::getContext()->cart->id,
        'secure_key' => Context::getContext()->customer->secure_key,
      ));
    }
      
    $this->httpGetUrl("GET", "payins/cardpayments/".$_GET['id']);

  }

  public function httpGetUrl($typeCall, $nameUrl)
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

    $authToken = curl_exec($ch);
    if (curl_errno($ch) != 0) {
      $this->context->cookie->test_error = 504;
      Tools::redirect($this->context->link->getPageLink('index',true).'index.php?fc=module&module=reservation&controller=confirmation');
    }
    curl_close($ch);

    $curl_jason = json_decode($authToken);
    $this->context->cookie->test_error = $curl_jason->{'ErrorCode'};
     
    Tools::redirect($this->context->link->getPageLink('index',true).'index.php?fc=module&module=reservation&controller=confirmation');
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
