<?php
setlocale(LC_TIME, "fr_FR.utf-8");

class HistoryController extends HistoryControllerCore
{
    public $auth = true;
    public $php_self = 'history';
    public $authRedirection = 'history';
    public $ssl = true;

    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS(_THEME_CSS_DIR_.'history.css');
        $this->addCSS(_THEME_CSS_DIR_.'addresses.css');
        $this->addJqueryPlugin('scrollTo');
        $this->addJS(array(
                    _THEME_JS_DIR_.'history.js',
                    _THEME_JS_DIR_.'tools.js')
                    );
    }

    //Sort arrays
    public static function _sortByDeliveryDateAsc($order_a, $order_b){
        $delivery_a = strtotime($order_a['date_delivery']);
        $delivery_b = strtotime($order_b['date_delivery']);

        return strcmp($delivery_a, $delivery_b);
    }
    public static function _sortByDeliveryDateDesc($order_a, $order_b){
        $delivery_a = strtotime($order_a['date_delivery']);
        $delivery_b = strtotime($order_b['date_delivery']);

        return -strcmp($delivery_a, $delivery_b);
    }

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        //init vars
        $delivered_orders = array();
        $coming_orders = array();
        $count_delivered_orders = 4;
        $count_coming_orders = 1000; //display all

        if ($orders = Order::getCustomerOrders($this->context->customer->id)) {

            foreach ($orders as &$order)
            {
                $delivery_date =  strtotime($order['date_delivery']);
                $today = strtotime(date("Y-m-d"));

                if ($today > $delivery_date) { //if date is passed
                    $delivered_orders[] = $order;
                }
                else {
                    $addressDelivery = new Address((int)($order['id_address_delivery']));

                    //set isDateAvailableForAdjustment
                    $zone = new Zone(Address::getZoneByZipCode($addressDelivery->postcode));
                    if (strtotime($order['date_delivery']) >= Order::getFirstAvailableOrderDate($zone->id))
                        $order['isOrderDateAvailableForAdjustment'] = true;
                    else
                        $order['isOrderDateAvailableForAdjustment'] = false;

                    $coming_orders[] = $order;
                }

                //set virtual
                $myOrder = new Order((int)$order['id_order']);
                if (Validate::isLoadedObject($myOrder))
                    $order['virtual'] = $myOrder->isVirtual(false);
            }
            //Sort arrays
            usort( &$delivered_orders , array('HistoryController', '_sortByDeliveryDateDesc') );
            usort( &$coming_orders , array('HistoryController', '_sortByDeliveryDateAsc') );
        }

        $this->context->smarty->assign(array(
            'orders' => $orders,
            'delivered_orders' => $delivered_orders,
            'coming_orders' => $coming_orders,
            'count_delivered_orders' => $count_delivered_orders,
            'count_coming_orders' => $count_coming_orders,
            'invoiceAllowed' => (int)(Configuration::get('PS_INVOICE')),
            'slowValidation' => Tools::isSubmit('slowvalidation')
        ));
        $this->setTemplate(_PS_THEME_DIR_.'history.tpl');
    }
}


