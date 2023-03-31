<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PriceAlertKrona
{
    const ACTION_PRICEALERT_CREATED = 'pricealert_created';

    /**
     * @return array[]
     */
    public static function getActions()
    {
        return [
            self::ACTION_PRICEALERT_CREATED => [
                'title' => 'Price alert created',
                'message' => 'You received {points} points for creating price alert',
            ]
        ];
    }

    /**
     * @param array $data
     *
     * @return void
     * @throws PrestaShopException
     */
    public static function priceAlertCreated($data)
    {
        self::triggerAction(self::ACTION_PRICEALERT_CREATED, self::getProductLink($data['id_product']));
    }

    /**
     * @param int $productId
     *
     * @return string|null
     * @throws PrestaShopException
     */
    private static function getProductLink($productId)
    {
        $productId = (int)$productId;
        if ($productId) {
            return \Context::getContext()->link->getProductLink($productId);
        }
        return null;
    }

    /**
     * @param string $action
     * @param string $url
     *
     * @return void
     * @throws PrestaShopException
     */
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
