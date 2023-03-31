<?php
/** @noinspection PhpUnhandledExceptionInspection */

define('PRICEALERT_PS_BASE', dirname(__FILE__) . '/../..');
require_once(PRICEALERT_PS_BASE . '/config/config.inc.php');
require_once(PRICEALERT_PS_BASE . '/init.php');
require_once(dirname(__FILE__) . '/pricealert.php');

$context = Context::getContext();
$db = DB::getInstance();

$action = Tools::getValue('action');

if ($action === 'create') {
    $product = Tools::getValue('product');
    $insert = array();

    // validate product id
    if (!is_numeric($product) || $db->getValue("select count(1) from `" . _DB_PREFIX_ . "product` where id_product = $product") == "0") {
        die('Invalid product id');
    }
    $insert['id_product'] = (int)$product;

    // validate combination id
    $combination = Tools::getValue('combination');
    if ($combination && "-1" != $combination) {
        $sql = 'SELECT product_attribute_shop.id_product_attribute FROM ' . _DB_PREFIX_ . 'product_attribute pa ' . Shop::addSqlAssociation('product_attribute', 'pa') . ' WHERE pa.id_product = ' . (int)$product . ' and pa.id_product_attribute = ' . (int)$combination;
        if (!is_numeric($combination) || !$db->getValue($sql)) {
            die('Invalid combination id');
        }
        $insert['id_product_attribute'] = (int)$combination;
    }

    // get customer id, if exists
    $customer = $context->customer->id;
    if ($customer) {
        $insert['id_customer'] = (int)$customer;
    }

    // validate email
    $email = Tools::getValue('email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email = $context->customer->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die('Invalid email');
        }
    }
    $insert['email'] = pSQL($email);

    // validate price
    $price = Tools::getValue('price');
    if (!is_numeric($price)) {
        die('Invalid price');
    }
    $price = Tools::convertPrice($price, $context->currency, false);

    // validate lid
    $lid = Tools::getValue('lid');
    if (!$lid) {
        die('Lid not found');
    }

    $insert['price'] = $price;
    $insert['date_add'] = date("Y-m-d H:i:s");
    $insert['id_local'] = pSQL($lid);
    $insert['id_format_currency'] = $context->currency->id;

    // perform insert into db
    $db->insert('ph_pricealert', $insert);

    PriceAlert::sendNotification($insert, $context);
    print("true");
} else {
    print("false");
}


die(1);
