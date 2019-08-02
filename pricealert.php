<?php
if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/krona.php');

class PriceAlert extends Module
{
	private $html = '';

	public function __construct()
	{
		$this->name = 'pricealert';
		$this->tab = 'front_office_features';
		$this->version = '1.0.12';
		$this->author = 'Petr Hucik <petr.hucik@gmail.com>';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Pricedrop Alert');
		$this->description = $this->l('Allows user to subscribe for notification on price changes');
		$this->html = '';
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install($delete_params = true)
	{
		if ($delete_params)
		{
			if (!file_exists(dirname(__FILE__).'/install.sql'))
				return false;
			else if (!$sql = Tools::file_get_contents(dirname(__FILE__).'/install.sql'))
				return false;
			$sql = str_replace(array('PREFIX_', 'ENGINE_TYPE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
			$sql = preg_split("/;\s*[\r\n]+/", $sql);
			foreach ($sql as $query)
				if ($query)
					if (!Db::getInstance()->execute(trim($query)))
						return false;
		}

		return (parent::install() &&
			$this->registerHook('displayProductButtons') &&
			$this->registerHook('actionProductUpdate') &&
			$this->registerHook('datakickExtend') &&
      $this->registerHook('registerGDPRConsent') &&
      $this->registerHook('actionDeleteGDPRCustomer') &&
      $this->registerHook('actionExportGDPRData') &&
      $this->registerHook('actionRegisterKronaAction') &&
			$this->registerHook('header')
		);
	}


	public function uninstall($delete_params = true)
	{
		if (($delete_params && !$this->deleteTables()) || !parent::uninstall())
			return false;

		return true;
	}

	private function deleteTables()
	{
		return Db::getInstance()->execute(
			'DROP TABLE IF EXISTS
			`'._DB_PREFIX_.'ph_pricealert`'
		);
	}

	public function reset()
	{
		if (!$this->uninstall(false))
			return false;
		if (!$this->install(false))
			return false;

		return true;
	}

	public function hookHeader($params)
	{
		$this->page_name = Dispatcher::getInstance()->getController();
		if ($this->page_name == 'product')
		{
			$this->context->controller->addJS($this->_path . 'views/js/pricealert-bootstrap.js');
			$this->context->controller->addCSS($this->_path . 'views/css/pricealert.css');
		}
	}

	private function percError($name, $text, &$output) {
		$fields = $this->getConfigurationFields();
		$field = $fields[$name];
		$output .= $this->displayError(sprintf($text, $field['label']));
		return false;
	}

	private function getPercValue($name, &$output) {
		$val = strval(Tools::getValue($name));
		if (! is_numeric($val)) {
			return $this->percError($name, $this->l('Invalid value for %s'), $output);
		}
		$val = (float)($val);
		if ($val < 0.0 || $val > 100.0)
			return $this->percError($name, $this->l('%s value must be between 0 and 100'), $output);
		return $val / 100.0;
	}

	private function getConfigurationFields() {
		return array(
			'PH_PRICEALERT_MIN_DISCOUNT' => array(
				'type' => 'text',
				'label' => $this->l('Minimal allowed discount [%]'),
				'name' => 'PH_PRICEALERT_MIN_DISCOUNT',
				'size' => 20,
				'required' => true
			),
			'PH_PRICEALERT_DEFAULT_DISCOUNT' => array(
				'type' => 'text',
				'label' => $this->l('Initial discount [%]'),
				'name' => 'PH_PRICEALERT_DEFAULT_DISCOUNT',
				'size' => 20,
				'required' => true
			),
      'PH_PRICEALERT_SEND_NOTIFICATION' => array(
				'type' => 'switch',
				'name' => 'PH_PRICEALERT_SEND_NOTIFICATION',
        'is_bool' => true,
        'values' => array(
          array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
          array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
        ),
				'label' => $this->l('Send notification when new alert is created'),
				'size' => 20,
				'required' => true
			),
      'PH_PRICEALERT_NOTIFICATION_EMAIL' => array(
				'type' => 'text',
				'name' => 'PH_PRICEALERT_NOTIFICATION_EMAIL',
				'label' => $this->l('Send notification to'),
				'size' => 20,
				'required' => false
			),
      'PH_PRICEALERT_SHOW_CONSENT' => array(
				'type' => 'switch',
				'name' => 'PH_PRICEALERT_SHOW_CONSENT',
        'is_bool' => true,
        'values' => array(
          array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
          array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
        ),
				'label' => $this->l('Show GDPR consent message'),
				'size' => 20,
				'required' => true
			),
		);
	}

	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name))
		{
			$minDiscount = $this->getPercValue('PH_PRICEALERT_MIN_DISCOUNT', $output);
			$defaultDiscount = $this->getPercValue('PH_PRICEALERT_DEFAULT_DISCOUNT', $output);
      $email = Tools::getValue('PH_PRICEALERT_NOTIFICATION_EMAIL');
      if (! Validate::isEmail($email)) {
        $email = '';
      }
      Configuration::updateValue('PH_PRICEALERT_NOTIFICATION_EMAIL', $email);
      Configuration::updateValue('PH_PRICEALERT_SEND_NOTIFICATION', Tools::getValue('PH_PRICEALERT_SEND_NOTIFICATION') ? 1 : 0);
      Configuration::updateValue('PH_PRICEALERT_SHOW_CONSENT', Tools::getValue('PH_PRICEALERT_SHOW_CONSENT') ? 1 : 0);
			if ($minDiscount !== false && $defaultDiscount !== false) {
				Configuration::updateValue('PH_PRICEALERT_MIN_DISCOUNT', $minDiscount);
				Configuration::updateValue('PH_PRICEALERT_DEFAULT_DISCOUNT', $defaultDiscount);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output . $this->displayForm();
	}


	public function displayForm()
	{
		// Get default language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		// Init Fields form array
		$fields = $this->getConfigurationFields();
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Settings'),
			),
			'input' => array_values($fields),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'btn btn-default pull-right'
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
		$helper->show_toolbar = true;				 // false -> remove toolbar
		$helper->toolbar_scroll = true;			 // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' => array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		$helper->fields_value['PH_PRICEALERT_MIN_DISCOUNT'] = 100 * self::getMinDiscount();
		$helper->fields_value['PH_PRICEALERT_DEFAULT_DISCOUNT'] = 100 * self::getDefaultDiscount();
    $helper->fields_value['PH_PRICEALERT_SEND_NOTIFICATION'] = self::getSendNotification();
    $helper->fields_value['PH_PRICEALERT_NOTIFICATION_EMAIL'] = self::getNotificationEmail();
    $helper->fields_value['PH_PRICEALERT_SHOW_CONSENT'] = self::getShowConsent();
		return $helper->generateForm($fields_form);
	}

	public function hookDatakickExtend($params)
	{
		return array(
			'priceAlert' => array(
				'id' => 'priceAlerts',
				'singular' => 'priceAlert',
				'description' => 'Price Alert',
				'key' => array('id'),
				'category' => 'relationships',
				'display' => 'email',
				'parameters' => array('shop'),
				'tables' => array(
					'pa' => array(
						'table' => 'ph_pricealert',
						'conditions' => array(
							'pa.id_shop = <param:shop>'
						)
					)
				),
				'fields' => array(
					'id' => array(
						'type' => 'number',
						'description' => 'id',
						'sql' => 'pa.id_pricealert',
						'require' => array('pa'),
						'update' => false,
					),
					'productId' => array(
						'type' => 'number',
						'description' => 'product id',
						'sql' => 'pa.id_product',
						'require' => array('pa'),
						'update' => false,
					),
					'combinationId' => array(
						'type' => 'number',
						'description' => 'combination id',
						'sql' => 'pa.id_product_attribute',
						'require' => array('pa'),
						'update' => false,
					),
					'customerId' => array(
						'type' => 'number',
						'description' => 'customer id',
						'sql' => 'pa.id_customer',
						'require' => array('pa'),
						'update' => false,
					),
					'created' => array(
						'type' => 'datetime',
						'description' => 'date created',
						'sql' => 'pa.date_add',
						'require' => array('pa'),
						'update' => array(
							'pa' => 'date_add'
						),
					),
					'sent' => array(
						'type' => 'datetime',
						'description' => 'date email sent',
						'sql' => 'pa.date_send',
						'require' => array('pa'),
						'update' => array(
							'pa' => 'date_send'
						),
					),
					'jsId' => array(
						'type' => 'string',
						'description' => 'javascript id',
						'sql' => 'pa.id_local',
						'require' => array('pa'),
						'update' => false
					),
					'email' => array(
						'type' => 'string',
						'description' => 'email',
						'sql' => 'pa.email',
						'require' => array('pa'),
						'update' => array(
							'pa' => 'email'
						)
					),
					'price' => array(
						'type' => 'currency',
						'description' => 'price',
						'sql' => array(
							'value' => 'pa.price',
							'currency' => 'pa.id_format_currency'
						),
						'require' => array('pa'),
						'fixedCurrency' => false,
						'update' => array(
							'pa' => array(
								'value' => 'price',
								'currency' => 'id_format_currency'
							)
						)
					)
				),
				'expressions' => array(
					'currentPrice' => array(
						'type' => 'currency',
						'description' => 'current price',
						'expression' => 'productPrice(<field:productId>, <field:combinationId>, true)'
					)
				),
				'links' => array(
					'product' => array(
						'description' => 'Product',
						'collection' => 'products',
						'type' => 'BELONGS_TO',
						'sourceFields' => array('productId'),
						'targetFields' => array('id')
					),
					'combination' => array(
						'description' => 'Combination',
						'collection' => 'combinations',
						'type' => 'HAS_ONE',
						'sourceFields' => array('combinationId'),
						'targetFields' => array('id')
					),
					'customer' => array(
						'description' => 'Customer',
						'collection' => 'customers',
						'type' => 'HAS_ONE',
						'sourceFields' => array('customerId'),
						'targetFields' => array('id')
					),
				)
			),
			'products' => array(
				'links' => array(
					'priceAlert' => array(
						'description' => 'Price alerts',
						'collection' => 'priceAlerts',
						'type' => 'HAS_MANY',
						'sourceFields' => array('id'),
						'targetFields' => array('productId')
					)
				)
			),
			'combinations' => array(
				'links' => array(
					'priceAlert' => array(
						'description' => 'Price alerts',
						'collection' => 'priceAlerts',
						'type' => 'HAS_MANY',
						'sourceFields' => array('id'),
						'targetFields' => array('combinationId')
					)
				)
			),
			'customers' => array(
				'links' => array(
					'priceAlert' => array(
						'description' => 'Price alerts',
						'collection' => 'priceAlerts',
						'type' => 'HAS_MANY',
						'sourceFields' => array('id'),
						'targetFields' => array('customerId')
					)
				)
			)
		);
	}

	public function hookActionProductUpdate($params) {
		$db = DB::getInstance(_PS_USE_SQL_SLAVE_);
		$product = $params['product'];
		$id = $product->id;
		$table = "`" . _DB_PREFIX_ ."ph_pricealert`";
		$ret = $db->ExecuteS("select distinct(id_product_attribute) comb from $table where date_send is null and id_product = " . (int)$id);
		$it = new RecursiveIteratorIterator(new RecursiveArrayIterator($ret));
		foreach($it as $combination) {
			$price = $product->getPrice(true, $combination);
			$combCond = $combination == null ? "is null" : "= $combination";
			$sql = "select * from $table where date_send is null and price >= $price and id_product_attribute $combCond and id_product = " . (int)$id;
			foreach ($db->ExecuteS($sql) as $alert) {
				self::sendAlert($alert, $product, $combination, $price);
			}
		}
	}

	public function sendAlert($alert, $product, $combination, $price) {
		$db = DB::getInstance();
		$id = (int)$alert['id_pricealert'];
		$email = $alert['email'];
		$images = array();
		if ($combination == null) {
			$images = $product->getImages($this->context->language->id);
		} else {
			$comb = $product->getCombinationImages($this->context->language->id);
			$images = (isset($comb[$combination][0])) ? $comb[$combination] : $product->getImages($this->context->language->id);
		}
		$image_id = (int)(isset($images[0]['id_image']) ? $images[0]['id_image'] : 0);
		$image = $this->getImageLink($product->link_rewrite, $image_id);

		$currency = Currency::getCurrencyInstance((int)$alert['id_format_currency']);

		$productName = $product->getProductName($product->id, $combination);
		$data = array(
			'{price}' => Tools::displayPrice(Tools::convertPrice($price, $currency), $currency),
			'{product_name}' => $productName,
			'{product_detail}' => $product->description_short,
			'{product_url}' => $product->getLink(),
			'{product_image}' => $image
		);
		$lang = $this->context->language->id;

		Mail::Send(
			$lang,
			'pricealert_drop',
			str_replace("%s", $productName, Mail::l('Price dropped on %s', $lang)),
			$data,
			$email,
			null, null, null, null, null, dirname(__FILE__).'/mails/', false, $this->context->shop->id);
		$db->update('ph_pricealert', ['date_send' => date("Y-m-d H:i:s")], "id_pricealert = $id");
	}

	public static function getShowConsent() {
		$val = Configuration::get('PH_PRICEALERT_SHOW_CONSENT');
		if ($val === false)
			return true;
		return $val;
	}

	public static function getMinDiscount() {
		$val = Configuration::get('PH_PRICEALERT_MIN_DISCOUNT');
		if ($val === false)
			return 0.2;
		return (float)$val;
	}

	public static function getDefaultDiscount() {
		$val = Configuration::get('PH_PRICEALERT_DEFAULT_DISCOUNT');
		if ($val === false)
			return 0.8;
		return (float)$val;
	}

	public static function getSendNotification() {
		$val = Configuration::get('PH_PRICEALERT_SEND_NOTIFICATION');
		if ($val === false)
      return true;
    return $val;
  }

	public static function getNotificationEmail() {
		$val = Configuration::get('PH_PRICEALERT_NOTIFICATION_EMAIL');
		if ($val === false || !Validate::isEmail($val))
      return Configuration::get('PS_SHOP_EMAIL');
    return $val;
  }

  public static function sendNotification($data, $context) {
    if (self::getSendNotification()) {
      $email = self::getNotificationEmail();
      $product = new Product($data['id_product']);
      $combination = null;
      if (isset($data['id_product_attribute'])) {
        $combination = $data['id_product_attribute'];
      }
      $productName = $product->getProductName($product->id, $combination);
      $price = $data['price'];
  		$images = array();
  		if (is_null($combination)) {
  			$images = $product->getImages($context->language->id);
  		} else {
  			$comb = $product->getCombinationImages($context->language->id);
  			$images = (isset($comb[$combination][0])) ? $comb[$combination] : $product->getImages($context->language->id);
  		}
  		$image_id = (int)((isset($images[0]['id_image']) ? $images[0]['id_image'] : 0));
  		$image = self::getImageLinkStatic($context->link, $product->link_rewrite, $image_id, $context->language->id);
  		$currency = Currency::getCurrencyInstance((int)$data['id_format_currency']);

    	$emailData = array(
        '{email}' => $data['email'],
  			'{price}' => Tools::displayPrice(Tools::convertPrice($price, $currency), $currency),
  			'{product_name}' => $productName,
  			'{product_url}' => $product->getLink(),
  			'{product_image}' => $image
  		);
      $lang = (int)Configuration::get('PS_LANG_DEFAULT');
  		Mail::Send($lang, 'pricealert_notification', str_replace("%s", $productName, Mail::l('New price alert for %s', $lang)), $emailData, $email, null, null, null, null, null, dirname(__FILE__).'/mails/', false, $context->shop->id);
    }
    PriceAlertKrona::priceAlertCreated($data);
  }

  public function hookActionRegisterKronaAction($params) {
    return PriceAlertKrona::getActions();
  }

	public function hookDisplayProductButtons($params)
	{
    $product = $params['product'];
    if (is_array($product)) {
      $product = new Product($product['id']);
    }
    $this->context->smarty->assign(array(
      'priceAlertUrl' => $this->context->shop->getBaseURI() . 'modules/pricealert/ajax.php',
			'priceAlertData' => array(
				'customer' => $this->getCustomer(),
				'product' => $this->getProduct($product),
				'currency' => $this->context->currency,
				'config' => array(
					'theme' => 'light',
					'separator' => Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'),
					'defaultDiscount' => self::getDefaultDiscount(),
					'minDiscount' => self::getMinDiscount(),
					'showFullScale' => false,
          'consent' => $this->getConsent()
				),
				'translation' => array(
					'alert_me_when_price_drops_to' => $this->l('Alert me when price drops to'),
					'current_price' => $this->l('Current price'),
					'cancel' => $this->l('Cancel'),
					'create_alert' => $this->l('Create alert'),
					'your_email_address' => $this->l('Your Email Address'),
					'alert_has_been_created' => $this->l('Alert has been created')
				)
			)
		));
		return $this->display(__FILE__, 'views/templates/hook/pricealert.tpl');
	}

  public function getConsent() {
    if (self::getShowConsent()) {
      if (Module::isInstalled('psgdpr') && Module::isEnabled('psgdpr')) {
        Module::getInstanceByName('psgdpr');
        $active = GDPRConsent::getConsentActive($this->id);
        if ($active === "1" || $active === true || $active === 1) {
          return GDPRConsent::getConsentMessage($this->id, $this->context->language->id);
        }
      }
      return $this->l('By submitting this request you agree to use of your data as outlined in our privacy policy');
    }
    return '';
  }

  public function hookRegisterGDPRConsent() {
  }

  private function getCustomerDataSql($email, $idCustomer=null) {
    $table = _DB_PREFIX_ . "ph_pricealert";
    $email = psql($email);
    $idCustomer = (int)$idCustomer;
    $sql = "FROM $table WHERE email = '$email'";
    if ($idCustomer) {
      $sql .= " OR id_customer = $idCustomer";
    }
    return $sql;
  }

  public function hookActionExportGDPRData($customer) {
    if (isset($customer['email']) && Validate::isEmail($customer['email'])) {
      $email = $customer['email'];
      $id = isset($customer['id']) ? $customer['id'] : null;
      $sql = "SELECT * " . $this->getCustomerDataSql($customer['email'], $id);
      $data = Db::getInstance()->ExecuteS($sql);
      if ($data) {
        return json_encode($data, JSON_PRETTY_PRINT);
      }
    }
  }

  public function hookActionDeleteGDPRCustomer($customer) {
    if (isset($customer['email']) && Validate::isEmail($customer['email'])) {
      $email = $customer['email'];
      $id = isset($customer['id']) ? $customer['id'] : null;
      $sql = "DELETE " . $this->getCustomerDataSql($customer['email'], $id);
      return json_encode(Db::getInstance()->execute($sql));
    }
  }

	protected function getCustomer() {
		$customer = $this->context->customer;
		return array(
			'id' => $customer->id,
			'email' => $customer->email
		);
	}

	protected function getProduct($input)
	{
      $context = $this->context;
      $lang = $context->language->id;
      $product = new Product($this->getProductId($input), false, $lang);
			$colors = array();
			$groups = array();
			$combinations = array();
			$images = $product->getImages($lang);
			$image_id = (int)(isset($images[0]['id_image']) ? $images[0]['id_image'] : 0);
			$defaultImage = $this->getImageLink($product->link_rewrite, $image_id);

			$attributes_groups = $product->getAttributesGroups($lang);
			if (is_array($attributes_groups) && $attributes_groups) {
					$combination_images = $product->getCombinationImages($lang);
					foreach ($attributes_groups as $row) {
							$comb = (int)$row['id_product_attribute'];
							$grp = (int)$row['id_attribute_group'];
							$attr = (int)$row['id_attribute'];

							// Color management
							if (isset($row['is_color_group']) && $row['is_color_group'] && (isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_.$attr.'.jpg'))) {
									$colors[$attr]['value'] = $row['attribute_color'];
									$colors[$attr]['name'] = $row['attribute_name'];
									if (!isset($colors[$attr]['attributes_quantity'])) {
											$colors[$attr]['attributes_quantity'] = 0;
									}
									$colors[$attr]['attributes_quantity'] += (int)$row['quantity'];
							}
							if (!isset($groups[$grp])) {
									$groups[$grp] = array(
											'id' => $grp,
											'name' => $row['public_group_name'],
											'type' => $row['group_type']
									);
							}
							$groups[$grp]['values'][$attr] = array(
								'id' => $attr,
								'name' => $row['attribute_name']
							);

							if (!isset($groups[$grp]['attributes_quantity'][$attr])) {
									$groups[$grp]['attributes_quantity'][$attr] = 0;
							}
							$groups[$grp]['attributes_quantity'][$attr] += (int)$row['quantity'];
							$combinations[$comb]['id'] = $comb;
							$combinations[$comb]['attributes'][$grp] = (int)$attr;
							$combinations[$comb]['price'] = $product->getPrice(true, $comb);
							$combinations[$comb]['quantity'] = (int)$row['quantity'];

							if (isset($combination_images[$comb][0]['id_image'])) {
									$combinations[$comb]['image'] = $this->getImageLink($product->link_rewrite, (int)($combination_images[$comb][0]['id_image']));
							}
					}

					// wash attributes list (if some attributes are unavailables and if allowed to wash it)
					if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0) {
							foreach ($groups as &$group) {
									foreach ($group['attributes_quantity'] as $key => &$quantity) {
											if ($quantity <= 0) {
													unset($group['attributes'][$key]);
											}
									}
							}

							foreach ($colors as $key => $color) {
									if ($color['attributes_quantity'] <= 0) {
											unset($colors[$key]);
									}
							}
					}
					foreach ($groups as &$group) {
							$group['values'] = array_values($group['values']);
					}
			}

			return array(
				'id' => $product->id,
				'name' => $product->name,
				'image' => $defaultImage,
				'price' => $product->getPrice(),
				'attributes' => array_values($groups),
				'combinations' => array_values($combinations),
				'colors' => $colors,
			);
	}

  private static function getImageLinkStatic($link, $rewrite, $imageId, $languageId) {
    if ($imageId) {
      if (is_array($rewrite)) {
        $rewrite = $rewrite[$languageId];
      }
      $type = is_callable(array('ImageType', 'getFormattedName')) ? ImageType::getFormattedName('home') : ImageType::getFormatedName('home');
      return $link->getImageLink($rewrite, $imageId, $type);
    }
    return '';
  }

  private function getImageLink($rewrite, $imageId) {
    $link = $this->context->link;
    $language = $this->context->language->id;
    return self::getImageLinkStatic($link, $rewrite, $imageId, $language);
  }

  private static function getProductId($product) {
    if (is_array($product) && isset($product['id_product'])) {
      return (int)$product['id_product'];
    }
    if (is_object($product) && property_exists($product, 'id_product')) {
      return (int)$product->id_product;
    }
    if (is_int($product)) {
      return (int)$product;
    }
    if ((int)Tools::getValue('id_product')) {
      return (int)Tools::getValue('id_product');
    }
    return null;
  }
}
