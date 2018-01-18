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
		 
		$this->displayName = $this->l('Reservation Module');
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
		&& $this->registerHook('EnvoiSMS'));
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
							`velo_num` tinyint(1) NOT NULL DEFAULT \'0\',
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

		return $this->display(__FILE__, 'reservation.tpl');
	}

	public function hooksuiviReservation($params) {
		$this->context->smarty->assign('reservations_list', $this->getReservations());

		if (Tools::getValue('id_cms') != 7) {
			return;
		}

		return $this->display(__FILE__, 'suivi.tpl');
	}

	public function hookcarteTrajets($params) {
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

    public function getReservations() {     
          
	    $all_reservations = array();
		$classList = array();
		global $cookie;
		$id_client = $cookie->id_customer;

		$sql = "SELECT * FROM `ps_dpr_reservation` WHERE `id_client` = " . $id_client . " AND `annulation` != 1 ORDER BY date_debut, date_fin, rendu";
		$total = Db::getInstance()->executeS($sql);
		$rendu = "";
		$color = 1;
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

		$all_reservations[count($all_reservations)] = "</select></form><div id='listReservations'>";

		foreach ($total as $key => $value) {
			setlocale(LC_TIME, "fr_FR");
			$originalDateDebut = $value['date_debut'];
			$date_debut = date("d M Y à G:i", strtotime($originalDateDebut));
			$date_id = date("dMY", strtotime($originalDateDebut));
			$originalDateFin = $value['date_fin'];
			$date_fin = date("d M Y à G:i", strtotime($originalDateFin));
			$paid = "<span style='color:red;'><b>Non</b></span>";
			if ($value['paid'] == 1) {
				$paid = "<span style='color:green;'><b>Oui</b></span>";
			}

			$rendu = "<span style='color:red;'><b>Non</b></span>";
			
			if ($value['rendu'] == 1) {
				$rendu = "<span style='color:green;'><b>Oui</b></span>";
			}

			if ($color == 1) {
				$all_reservations[count($all_reservations)] = "<li class='" . $date_id . "' style='list-style:none;padding:3px 10px 3px;'>" . ($key + 1) . " - Du : " . $date_debut . " &emsp; Au : " . $date_fin . " &emsp; &emsp; Payé : " . $paid . " &emsp; &emsp; Vélo rendu : " . $rendu . "</li>";
				$color = 0;
			} else {
				$all_reservations[count($all_reservations)] = "<li class='" . $date_id . "' style='list-style:none;padding:3px 10px 3px;background-color:#ddd'>" . ($key + 1) . " - Du : " . $date_debut . " &emsp; Au : " . $date_fin . " &emsp; &emsp; Payé : " . $paid . " &emsp; &emsp; Vélo rendu : " . $rendu . "</li>";
				$color = 1;
			}
		}

		$all_reservations[count($all_reservations)] = '</div>';

		return $all_reservations;

    }

}

?>