<?php

if (!defined('_PS_VERSION_')) {
	exit;
}

class Reservation extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'reservation';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Pierre Fervel (Dropbird)';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
		$this->bootstrap = true;
		
		parent::__construct();
		
		$this->displayName = $this->l('Reservation Siblu');
		$this->description = $this->l('Schedule bike location and manage it.');
		
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	public function install()
	{
		if (parent::install()
			&& Configuration::updateValue('reservation', '')
			&& $this->registerHook('displayHome')
			&& $this->registerHook('header')
			&& $this->registerHook('reservation')
			&& $this->registerHook('suiviReservation')
			&& $this->registerHook('carteTrajets')
			&& $this->registerHook('optionsReservation')
			&& $this->registerHook('Payment')
			&& $this->registerHook('EnvoiSMS')
			&& $this->registerHook('Tarifs'));
		{
			/* Creates tables */
			$res = $this->createTables();

			return (bool)$res;
		}

		return false;

	}

	/**
	 * Creates tables
	 */
	protected function createTables()
	{
		$res = (bool)Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'dpr_reservation` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`id_client` int(11) NOT NULL DEFAULT \'0\',
			`id_cart` int(11) NOT NULL DEFAULT \'0\',
			`bungalo_num` int(5) DEFAULT \'0\',
			`digicode` varchar(4) NOT NULL,
			`date_debut` DATETIME NOT NULL,
			`date_fin` DATETIME NOT NULL,
			`id_borne` varchar(50) NOT NULL,					
			`paid` int(1) NOT NULL DEFAULT \'0\',
			`annulation` int(1) NOT NULL DEFAULT \'0\',					
			`rendu` int(1) NOT NULL DEFAULT \'0\',
			PRIMARY KEY(`id`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
			');

		$res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'dpr_log` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`date_log` DATETIME NOT NULL,
			`id_borne` varchar(50) NOT NULL,
			`version` varchar(20) NOT NULL,
			`message` varchar(200),
			PRIMARY KEY(`id`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
			');

		$res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'location` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`lng` FLOAT NOT NULL,
			`lat` FLOAT NOT NULL,
			`sizeIcon` INT(11) NOT NULL,
			`icon` VARCHAR(55),
			`name` VARCHAR(255),
			`infoImage` VARCHAR(2442),
			`infoDescription` VARCHAR(2555),
			PRIMARY KEY(`id`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
			');

		return $res;
	}

	public function uninstall()
	{
		/* // Deletes Module
        if (parent::uninstall())
        {
            // Deletes tables
            $res = $this->deleteTables();

            // Unsets configuration
            $res &= Configuration::deleteByName('jsvalue');

            return $res;
        }

        return false; */
        if (parent::uninstall())
        	return true;
        return false;
    }

    /**
     * deletes tables
     
    protected function deleteTables()
    {
        return Db::getInstance()->query('
			DROP TABLE IF EXISTS `'._DB_PREFIX_.'dpr_reservation`, `'._DB_PREFIX_.'dpr_log`;
		');
	} */

	public function getContent()
	{
		$output = null;
		
		if (Tools::isSubmit('submit'.$this->name))
		{
			$reservation = strval(Tools::getValue('reservation'));
			if (!$reservation  || empty($reservation) || !Validate::isGenericName($reservation))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			else
			{
				Configuration::updateValue('reservation', $reservation);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}

		return $output.$this->displayForm();
	}

	public function displayForm()
	{
	    // Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
	    // Init Fields form array
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Settings'),
				),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Configuration value'),
					'name' => 'reservation',
					'size' => 20,
					'required' => true
					),
				array(
					'type' => 'switch',
					'label' => $this->l('Activer les réservations de vélos'),
					'name' => 'reservation_mode',
					'is_bool' => true,
					'desc' => $this->l('Activer votre module'),
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => true,
							'label' => $this->l('Enabled')
							),
						array(
							'id' => 'active_off',
							'value' => false,
							'label' => $this->l('Disabled')
							)
						),
					),
				array(
					'type' => 'list',
					'label' => $this->getAdminReservations(),
					'name' => 'reservation'
					)
				),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
				)
			);
		
		$helper = new HelperForm();
		
	    // Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		
	    // Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		
	    // Title and toolbar
		$helper->title = $this->displayName;
	    $helper->show_toolbar = true;        // false -> remove toolbar
	    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
	    $helper->submit_action = 'submit'.$this->name;
	    $helper->toolbar_btn = array(
	    	'save' =>
	    	array(
	    		'desc' => $this->l('Save'),
	    		'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
	    		'&token='.Tools::getAdminTokenLite('AdminModules'),
	    		),
	    	'back' => array(
	    		'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
	    		'desc' => $this->l('Back to list')
	    		)
	    	);
	    
	    // Load current value
	    $helper->fields_value['reservation'] = Configuration::get('reservation');
	    
	    return $helper->generateForm($fields_form);
	}

	public function hookDisplayHome($params)
	{
		$this->context->smarty->assign(
			array(
				'reservation_name' => Configuration::get('reservation'),
				'reservation_link' => $this->context->link->getModuleLink('reservation', 'display')
				)
			);
	}

	public function hookDisplayHeader()
	{
		$this->context->controller->addCSS($this->_path.'css/reservation.css', 'all');
	}

	public function hookreservation($params) {
		if (Tools::getValue('id_cms') != 6) {
			return;
		}

		$this->context->smarty->assign('velo_dispo', '4');

		return $this->display(__FILE__, 'reservation.tpl');
	}

	public function hooksuiviReservation($params) {
		$this->context->smarty->assign('reservations_list', $this->getReservations());
		$this->context->smarty->assign('select_options', $this->getSelectOptions());

		if (Tools::getValue('id_cms') != 7) {
			return;
		}

		return $this->display(__FILE__, 'suivi.tpl');
	}

	public function hookcarteTrajets($params) {
		$this->context->smarty->assign('variables_list', $this->getVariablesList());
		$this->context->smarty->assign('right_sidebar', $this->getRightSidebar());
		$this->context->smarty->assign('trajet_fieldset', $this->getTrajetFieldset());

		if (Tools::getValue('id_cms') != 8) {
			return;
		}

		return $this->display(__FILE__, 'trajet.tpl');
	}

	public function hookoptionsReservation($params)
	{
		return $this->display(__FILE__, 'options.tpl');
	}

	public function hookPayment($params)
	{
        /* $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies) == false)
        return false; */
        $this->smarty->assign(array(
        	'this_path' => $this->_path,
        	'this_path_bw' => $this->_path,
        	'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        	));

        return $this->display(__FILE__, 'paiement.tpl');
    }

    
    public function hookEnvoiSMS($params)
    {
    	if (Tools::getValue('id_cms') != 9)
    		return;
    	return $this->display(__FILE__, 'envoiSMS.tpl');
    } 

    public function hookTarifs($params)
    {
    	if (Tools::getValue('id_cms') != 11)
    		return;
    	return $this->display(__FILE__, 'tarifs.tpl');
    } 
	/*

    public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		if (in_array($state, array(Configuration::get('PS_OS_BANKWIRE'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'))))
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'bankwireDetails' => Tools::nl2br($this->details),
				'bankwireAddress' => Tools::nl2br($this->address),
				'bankwireOwner' => $this->owner,
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	} 

	*/


	public function getTrajetFieldset() {
		$sqlResquest = "SELECT * FROM `ps_dpr_trajet` ORDER BY `id`";
		$arraySql = DB::getInstance()->executeS($sqlResquest);
		$htmlContent = array();
		for ($i = 0; $i < count($arraySql); $i++) {
			$name = $arraySql[$i]['name'];
			$id = $arraySql[$i]['id'];
			$htmlContent[$i] = '<div><label for="trajet'.$id.'" id="trajet'.$id.'_label">'.$name.'</label>';
			$htmlContent[$i] = $htmlContent[$i].'<input type="radio" name="trajet" id="trajet'.$id.'"></div>';
		}
		return $htmlContent;
	}

	public function getRightSidebar() {
		$htmlContent = array();
		$pathToImages = __PS_BASE_URI__.'modules/reservation/views/templates/hook/suivi/images/';
		$sqlResquest = "SELECT `id`, `name`, `icon`, `RightSidebarId`, `additionalInfo` FROM `ps_dpr_location` WHERE `RightSidebarId` != 0 ORDER BY `RightSidebarId`";
		$arraySql = DB::getInstance()->executeS($sqlResquest);
		
		for ($i = 0; $i < count($arraySql); $i++) {
			$additionalInfo = json_decode($arraySql[$i]['additionalInfo']);
			$htmlContent[$i] = '<div class="listUnderling" id="listePartenaires'.$arraySql[$i]['id'].'">';
			$htmlContent[$i] = $htmlContent[$i].'<img src="'.$pathToImages.$arraySql[$i]['icon'].'"/>';
			$htmlContent[$i] = $htmlContent[$i].'<p>'.$arraySql[$i]['name'].'</p>';
			if ($additionalInfo != null) {
				$Car = $additionalInfo->Car->time;
				$Bicycle = $additionalInfo->Bicycle->time;
				$Kilometer = $additionalInfo->Car->distance . 'km';
				if ($Car < 60)
					$Car = intval($Car).'min';
				else
					$Car = intval($Car / 60).'h'.intval($Car % 60);
				if ($Bicycle < 60)
					$Bicycle = intval($Bicycle).'min';
				else
					$Bicycle = intval($Bicycle / 60).'h'.intval($Bicycle % 60);
				$htmlContent[$i] = $htmlContent[$i].'<div class="infoLocation">';
				$htmlContent[$i] = $htmlContent[$i].'<img src="'.__PS_BASE_URI__.'modules/reservation/views/templates/hook/suivi/images/iconmonstr-map-5-24.png"><p>'.$Kilometer.'</p>';
				$htmlContent[$i] = $htmlContent[$i].'<img src="'.__PS_BASE_URI__.'modules/reservation/views/templates/hook/suivi/images/velo.png"><p>'.$Bicycle.'</p>';
				$htmlContent[$i] = $htmlContent[$i].'<img src="'.__PS_BASE_URI__.'modules/reservation/views/templates/hook/suivi/images/voiture.png"><p>'.$Car.'</p>';
				$htmlContent[$i] = $htmlContent[$i].'</div>';
			}
			$htmlContent[$i] = $htmlContent[$i].'</div>';
		}
		return $htmlContent;
	}

	public function getVariablesList() {
		$encoded = 'var locations = ';
		$sqlResquest = "SELECT `id`, `lat`, `lng`, `sizeIcon`, `icon`, `name`, `infoImage`, `infoDescription` FROM `ps_dpr_location` WHERE 1";
		$i = 0;
		$arraySql = Db::getInstance()->executeS($sqlResquest);
		for ($i = 0; $i < count($arraySql); $i++) {
			$arraySql[$i]['id'] = json_decode($arraySql[$i]['id']);
			$arraySql[$i]['lat'] = json_decode($arraySql[$i]['lat']);
			$arraySql[$i]['lng'] = json_decode($arraySql[$i]['lng']);
			$arraySql[$i]['sizeIcon'] = json_decode($arraySql[$i]['sizeIcon']);
		}
		$encoded = $encoded.json_encode($arraySql).'
		';
		$encoded = $encoded.'var trajets = ';
		$sqlResquest = 'SELECT * FROM `ps_dpr_trajet`';
		$arraySql = DB::getInstance()->executeS($sqlResquest);
		for ($i = 0; $i < count($arraySql); $i++) {
			$arraySql[$i]['trachet'] = json_decode($arraySql[$i]['trachet']);
			$arraySql[$i]['id'] = json_decode($arraySql[$i]['id']);
			$arraySql[$i]['length'] = json_decode($arraySql[$i]['length']);
			$arraySql[$i]['strokeweight'] = json_decode($arraySql[$i]['strokeweight']);
			$arraySql[$i]['time'] = json_decode($arraySql[$i]['time']);
		}
		$encoded = $encoded.json_encode($arraySql).'
		';
		return $encoded;
	}

	public function getSelectOptions() {
		$all_reservations = array();
		$classList = array();
		global $cookie;
		$id_client = $cookie->id_customer;
		$sql = "SELECT DISTINCT d.`id_order_detail`, o.`reference`, r.`date_debut`, r.`date_fin`, d.`product_quantity`, d.`product_name`, o.`total_paid` FROM `ps_dpr_reservation` AS `r` JOIN `ps_orders` AS `o` ON r.`id_cart` = o.`id_cart` JOIN `ps_order_detail` AS `d` ON o.`id_order` = d.`id_order` WHERE r.`id_client` = " . $id_client . " AND r.`annulation` != 1 ORDER BY r.`date_debut`, r.`date_fin`, r.`rendu`";
		$total = Db::getInstance()->executeS($sql);
		$all_reservations[0] = '<option disabled selected value style="display: none"></option>';

		foreach ($total as $key => $value) {
			$date_id = date("dMY", strtotime($value['date_debut']));
			$temp = 0;
			for ($i = 1; $i < count($classList); $i++) {
				if (!(strcmp($date_id, $classList[$i]))) {
					$temp = 1;
					break;
				}
			}
			if ($temp == 1) {
				continue;
			}
			$classList[count($classList)] = $date_id;
			$date_debut = date("d M Y", strtotime($value['date_debut']));
			$all_reservations[count($all_reservations)] = "<option value='" . $date_id . "' class='" . $date_id . "'>" . $date_debut . "</option>";
		}
		return $all_reservations;
	}
	
	public function getReservations() {
		$all_reservations = array();
		$classList = array();
		global $cookie;
		$id_client = $cookie->id_customer;

		$sql = "SELECT DISTINCT d.`id_order_detail`, o.`reference`, r.`date_debut`, r.`date_fin`, d.`product_quantity`, d.`product_name`, o.`total_paid` FROM `ps_dpr_reservation` AS `r` JOIN `ps_orders` AS `o` ON r.`id_cart` = o.`id_cart` JOIN `ps_order_detail` AS `d` ON o.`id_order` = d.`id_order` WHERE r.`id_client` = " . $id_client . " AND r.`annulation` != 1 ORDER BY r.`date_debut`, r.`date_fin`, r.`rendu`";
		$total = Db::getInstance()->executeS($sql);

		if ($total === false) {
			return $all_reservations;
		}

		$z = 0;
		$color = 1;

		$langue = $this->context->cookie->id_lang;

		/*for($i = 0; $i < count($total); $i++) {
			if ($total[$i]['reference'] == $total[$i +1]['reference']) {
				$z++;
				if ($total[$i]['product_quantity'] > 1 ) {
					$total[$i + 1]['product_name'] = $total[$i]['product_quantity']. " x ". $total[$i]['product_name'];
				} else {
					$total[$i + 1]['product_name'] .= "<br />".$total[$i]['product_name'];
				}
				unset($total[$i]);
				$z = 0;
			}
		}*/

		function moveEveryElement($array, $i) {
			$endArray = array_slice($array, $i + 1);
			$beginArray = array_splice($array, 0, $i + 1);
			unset($beginArray[count($beginArray) - 1]);
			$array = array_merge($beginArray, $endArray);
			return $array;
		}

		function resultToStr($array) {
			$result = '';
			foreach ($array as $key => $value) {
				if ($value > 1)
					$result .= $value . " x " . $key;
				else
					$result .= $key;
				$result .= '<br>';
			}
			return $result;
		}

		$resultArray = array();
		$resultArray[$total[0]['product_name']] = $total[0]['product_quantity'];
		for($i = 0; $i < count($total); $i++) {
			if (!strcmp($total[$i]['reference'], $total[$i + 1]['reference'])) {
				$resultArray[$total[$i + 1]['product_name']] = $total[$i + 1]['product_quantity'];
				$total = moveEveryElement($total, $i + 1);
				$i--;
			}
			else {
				$total[$i]['product_name'] = resultToStr($resultArray);
				$resultArray = array();
				$resultArray[$total[$i + 1]['product_name']] = $total[$i + 1]['product_quantity'];
			}
		}

		if ($langue == 1) {

			$all_reservations[count($all_reservations)] = "</form><div>
			<table id='listReservations' style='width: 100%'>
				<tr>
					<th>Commande</th>
					<th>Du :</th>
					<th>Au :</th>
					<th>Produits</th>
					<th>Prix</th>
				</tr>";

				foreach ($total as $key => $value) {
					$commande = $value['reference'];
					setlocale(LC_TIME, "fr_FR");
					$originalDateDebut = $value['date_debut'];
					$date_debut = date("d M Y à G:i", strtotime($originalDateDebut));
					$date_id = date("dMY", strtotime($originalDateDebut));
					$originalDateFin = $value['date_fin'];
					$date_fin = date("d M Y à G:i", strtotime($originalDateFin));
					$product_name = $value['product_name'];
					$paid = intval($value['total_paid']);

					if ($color == 1) {
						$all_reservations[count($all_reservations)] = "
						<tr class='" . $date_id . "'>
							<td class='col-md-offset-1 col-md-2'>" . $commande . "</td>
							<td class='col-md-2'>" . $date_debut . "</td>
							<td class='col-md-2'>" . $date_fin . "</td>
							<td class='col-md-2'>" . $product_name ."</td>
							<td class='col-md-2'>" . $paid ." €</td>
						</tr>";
						$color = 0;
					} else {
						$all_reservations[count($all_reservations)] = "
						<tr class='" . $date_id . "' style='background-color:#ddd;'>
							<td class='col-md-offset-1 col-md-2'>" . $commande . "</td>
							<td class='col-md-2'>" . $date_debut . "</td>
							<td class='col-md-2'>" . $date_fin . "</td>
							<td class='col-md-2'>" . $product_name ."</td>
							<td class='col-md-2''>" . $paid ." €</td>
						</tr>";
						$color = 1;
					}
				}

			} elseif ($langue == 2) {

				$all_reservations[count($all_reservations)] = "</form><div>
				<table id='listReservations' style='width: 100%'>
					<tr>
						<th>Commande</th>
						<th>Du :</th>
						<th>Au :</th>
						<th>Produits</th>
						<th>Prix</th>
					</tr>";

					foreach ($total as $key => $value) {
						$commande = $value['reference'];
						setlocale(LC_TIME, "fr_FR");
						$originalDateDebut = $value['date_debut'];
						$date_debut = date("d M Y à G:i", strtotime($originalDateDebut));
						$date_id = date("dMY", strtotime($originalDateDebut));
						$originalDateFin = $value['date_fin'];
						$date_fin = date("d M Y à G:i", strtotime($originalDateFin));
						$product_name = $value['product_name'];
						$paid = intval($value['total_paid']);

						if ($color == 1) {
							$all_reservations[count($all_reservations)] = "
							<tr class='" . $date_id . "'>
								<td class='col-md-offset-1 col-md-2'>" . $commande . "</td>
								<td class='col-md-2'>" . $date_debut . "</td>
								<td class='col-md-2'>" . $date_fin . "</td>
								<td class='col-md-2'>" . $product_name ."</td>
								<td class='col-md-2'>" . $paid ." €</td>
							</tr>";
							$color = 0;
						} else {
							$all_reservations[count($all_reservations)] = "
							<tr class='" . $date_id . "' style='background-color:#ddd;'>
								<td class='col-md-offset-1 col-md-2'>" . $commande . "</td>
								<td class='col-md-2'>" . $date_debut . "</td>
								<td class='col-md-2'>" . $date_fin . "</td>
								<td class='col-md-2'>" . $product_name ."</td>
								<td class='col-md-2''>" . $paid ." €</td>
							</tr>";
							$color = 1;
						}
					}
				}

				$all_reservations[count($all_reservations)] = '</table></div>';

				return $all_reservations;

			}

		}

		?>