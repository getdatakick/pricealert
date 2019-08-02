<?php
if (!defined('_PS_VERSION_'))
    exit;

class PriceAlertKrona
{
    const ACTION_PRICEALERT_CREATED = 'pricealert_created';

    public static function getActions()
    {
        return [
            self::ACTION_PRICEALERT_CREATED => [
                'title' => 'Price alert created',
                'message' => 'You received {points} points for creating price alert',
            ]
        ];
    }

    public static function priceAlertCreated($data)
    {
        self::triggerAction(self::ACTION_PRICEALERT_CREATED, self::getProductLink($data['id_product']));
    }

    private static function getProductLink($productId)
    {
        if ((int)$productId) {
            return \Context::getContext()->link->getProductLink($productId);
        }
        return null;
    }

    private static function triggerAction($action, $url = null)
    {
        $customer = \Context::getContext()->customer;
        if ($customer->isLogged() && array_key_exists($action, self::getActions())) {
            $data = [
                'module_name' => 'pricealert',
                'action_name' => $action,
                'id_customer' => (int)$customer->id
            ];
            if (!is_null($url)) {
                $data['action_url'] = $url;
            }
            \Hook::exec('actionExecuteKronaAction', $data);
        }
    }

}
