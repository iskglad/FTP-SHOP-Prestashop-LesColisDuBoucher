<?php

class AdminOrdersPreparationController extends AdminController{
    public $orders;
    public $shop_carriers;
    public $shop_zones;

    //==============================================================================================================
    //DATABASE QUERY - RETRIEVES DATAS
    //==================================================================

    public function __construct()
    {
        $this->table = 'orders';

        $this->deleted = false;
        $this->context = Context::getContext();

        //=================================
        //DATE
        //==================================
        $date_interval_begin = date('Y-m-d', strtotime('+2 days')); //default value: today + 2days
        $date_interval_end = $date_interval_begin; //default value: same

        if (Tools::isSubmit('date_interval_begin') && Tools::isSubmit('date_interval_end')){
            $date_interval_begin = Tools::getValue('date_interval_begin');
            $date_interval_end = Tools::getValue('date_interval_end');
        }

        //=================================
        //LOAD DATAS
        //==================================
        //load orders
        $this->_loadOrders($date_interval_begin, $date_interval_end);

        foreach ($this->_list as &$order){
            //load order's products
            $order["products"] = $this->_loadOrdersProducts($order['id_order']);
            //set zone name
            $zone = new Zone(Address::getZoneByZipCode($order['delivery_postcode']));
            $order["zone"] = $zone->name;

            //set isNewCustomer
            $order['is_new_customer'] = Customer::isNewCustomer($order['id_client']);

            //set isPaymentDone ("paiement effectué" si moyen de paiement != "comptant a la livraison")
            $order["is_payment_done"] = true;
            if ($order['payment'] == "Comptant à la livraison")
                $order["is_payment_done"] = false;

            foreach ($order["products"] as &$product)
                //load proudct's attributes
                $this->_updateProductAttributes($product);
        }

        //set all orders weight
        $this->_setOrdersWeight();

        //Apply manual sort and filter (by carrier and zone)
        $this->_customSortOrders();

        //=================================
        //ASSIGN VARS
        //==================================
        $smarty = $this->context->smarty;
        $smarty->assign(array(
            'date_interval_begin'      =>  $date_interval_begin,
            'date_interval_end'        =>  $date_interval_end,
            'use_date_interval'        =>  Tools::getValue('use_date_interval')
        ));

        parent::__construct();
    }

    //loadOrders
    //Description:
    //get all orders of the choosen date + UPS order of <choosen date + 1day>
    //Set them to this->orders
    public function _loadOrders($date_interval_begin, $date_interval_end){

        $day_after_interval = date('Y-m-d',strtotime($date_interval_end . "+1 days"));

        //=================================
        //SELECT
        //==================================

        $select = '
            a.id_order              as  id_order,
            a.id_customer           as  id_client,
            a.valid                 as  valid,
            a.payment               as  payment,
            a.date_delivery         as  delivery_date,
            a.hour_delivery         as  hours,
            a.total_products_wt     as  total_products_wt,
            a.total_discounts_tax_incl  as total_discounts_wt,
            a.total_paid_tax_incl   as  total_paid_wt,
            a.message               as  message,

            da.firstname            as  delivery_first_name,
            da.lastname             as  delivery_last_name,
            da.company              as  delivery_company,
            da.address1             as  delivery_address1,
            da.address2             as  delivery_address2,
            da.postcode             as  delivery_postcode,
            da.city                 as  delivery_city,
            da.phone                as  delivery_phone,
            da.phone_mobile         as  delivery_phone_mobile,
            da.code                 as  delivery_code,
            da.floor                as  delivery_floor,

            cr.name                 as  carrier,

            c.note                  as  client_note,
            c.email                 as  client_email,
            CONCAT(c.firstname, " ", c.lastname) as  client_name
        ';
        $from = '
            `'._DB_PREFIX_.$this->table.'`    as  a
        ';
        $limit = "";


        //=================================
        //JOIN
        //==================================
        $join = '
            LEFT JOIN `'._DB_PREFIX_.'customer`  c          ON  (a.`id_customer` = c.`id_customer`)
            LEFT JOIN `'._DB_PREFIX_.'address` da           ON  (a.`id_address_delivery` = da.`id_address`)
            LEFT JOIN `'._DB_PREFIX_.'carrier` cr           ON  (a.`id_carrier` = cr.`id_carrier`)
        ';

        //=================================
        //ORDER BY
        //==================================
        $order_by = Tools::getValue("SortBy");
        if ($order_by == "" || $order_by == "carrier" || $order_by == "zone") //zone and carrier are ordered manually, not with "ORDER BY" clause.
            $order_by = "id_order";

        $order_by .= " ".(Tools::getValue("SortWay") ? Tools::getValue("SortWay"): "asc");

        //=================================
        //FILTERS AND WHERE CLAUSE
        //==================================
        //Where choosen date and choosen Date + 1 for UPS.
        //The list will be manualy clean later to keep UPS only for date+1Day
        $where = ' (DATE(a.date_delivery) >= DATE("'.$date_interval_begin.'")
                                AND DATE(a.date_delivery) <= DATE("'.$day_after_interval.'"))';

        //Valid payment state only
        $where .= ' AND a.current_state != '.ID_ORDER_STATE_CANCELED;
        $where .= ' AND a.current_state != '.ID_ORDER_STATE_PAYMENT_ERROR;
        $where .= ' AND a.current_state != '.ID_ORDER_STATE_REFUND;
        $where .= ' AND a.current_state != '.ID_ORDER_STATE_WAITING_BANKWIRE_PAYMENT;
        $where .= ' AND a.current_state != '.ID_ORDER_STATE_WAITING_PAYPAL_PAYMENT;

        //Filters
        if(Tools::getValue('productFilter_id_order'))
            $where .= ' AND a.id_order = "'.Tools::getValue('productFilter_id_order').'"';
        if(Tools::getValue('productFilter_client_name'))
            $where .= ' AND CONCAT(c.firstname, " ", c.lastname)  LIKE "%'.Tools::getValue('productFilter_client_name').'%"';
        if(Tools::getValue('productFilter_hours'))
            $where .= ' AND a.hour_delivery  LIKE "%'.Tools::getValue('productFilter_hours').'%"';


        //=================================
        //REQUEST
        //==================================
        $sql = "SELECT ".$select." FROM".$from." "
            .$join
            ." WHERE ".$where
            ." ORDER BY ".$order_by;
        if ($limit){
            $sql .= " LIMIT ".$limit;
        }
        $this->_list = Db::getInstance()->executeS($sql);

        //=================================
        //CLEAN UP (UPS): DATE+1DAY ONLY
        //==================================
        foreach ($this->_list as $key => &$row){
            $row_date = date("Y-m-d", strtotime($row["delivery_date"]));

            //if UPS date (when date = choosen date + 1day)
            if ( $row_date == $day_after_interval){
                //if zone is not UPS
                if (ID_ZONE_UPS != Address::getZoneByZipCode($row["delivery_postcode"]))
                    unset($this->_list[$key]);//delete product row
            }

            if ( $row_date == $date_interval_begin){
                //if zone is UPS
                if (ID_ZONE_UPS == Address::getZoneByZipCode($row["delivery_postcode"]))
                    unset($this->_list[$key]);//delete product row
            }
        }
    }

    //loadOrdersProducts
    //Description:
    //get products ordered in order with id = $id_order
    public function _loadOrdersProducts($id_order){

        //=================================
        //SELECT
        //==================================

        $select = '
            a.id_order_detail       as  id_order_detail,
            a.product_id            as  id_product,
            a.product_attribute_id  as  id_product_attribute,
            a.product_quantity      as  quantity,

            s.id_supplier           as  id_supplier,
            s.name                  as  supplier,

            p.weight                as  weight,

            plang.id_lang           as  id_lang,
            plang.name              as  product_name,
            plang.description_short as  description_short,

            calang.name             as  category
        ';
        $from = '
            `'._DB_PREFIX_.'order_detail`    as  a
        ';
        $limit = "";


        //=================================
        //JOIN
        //==================================
        $join = '
            LEFT JOIN `'._DB_PREFIX_.'product`  p           ON  (a.`product_id` = p.`id_product`)
            LEFT JOIN `'._DB_PREFIX_.'supplier` s           ON  (p.`id_supplier` = s.`id_supplier`)
            LEFT JOIN `'._DB_PREFIX_.'product_lang` plang   ON  (a.`product_id` = plang.`id_product`)
            LEFT JOIN `'._DB_PREFIX_.'category_lang` calang ON  (p.`id_category_default` = calang.`id_category`)
         ';

        //=================================
        //WHERE CLAUSE AND ORDER BY
        //==================================
        //specify language
        $where = "plang.id_lang = calang.id_lang AND plang.id_lang = ".$this->context->language->id;
        //specify order
        $where .=  " AND a.id_order = $id_order";

        $order_by = "id_order_detail ASC";

        //=================================
        //REQUEST
        //==================================
        $sql = "SELECT ".$select." FROM".$from." "
            .$join
            ." WHERE ".$where
            ." ORDER BY ".$order_by;
        if ($limit){
            $sql .= " LIMIT ".$limit;
        }

        return Db::getInstance()->executeS($sql);
    }

    //Update Product attributes
    //Description:
    //Set unretrieved infos (promo, label, colis, etc)
    public function _updateProductAttributes(&$combination){
        $sql = 'SELECT  pac.`id_attribute` AS id,
                            a.id_attribute_group AS id_group,
                            al.`name` AS value,
                            agl.`name` AS group_name
                    FROM    `'._DB_PREFIX_.'product_attribute_combination` pac
                    LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                    LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON al.`id_attribute` = pac.`id_attribute`
                    LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON agl.`id_attribute_group` = a.`id_attribute_group`
                    WHERE pac.`id_product_attribute` = '.(int)$combination['id_product_attribute'].'
           ';
        $combination['attributes'] = Db::getInstance()->executeS($sql);
        //set Promo
        $combination['isPromo'] = Product::isCombinationPromo($combination);
        $combination['promo_name'] = Product::getCombinationPromoName($combination);
        //Promo lcdb is considered "None" in Cumul product BonDeCommande
        if ($combination['promo_name'] == 'lcdb'){
            $combination['isPromo'] == 0;
            $combination['promo_name'] == '';
        }
        //set Pro value
        $combination['isPro'] = Product::isCombinationPro($combination);
        //get Labels name Array
        $combination['label_name'] = Product::getCombinationLabelName($combination);
        //get Colis name (used for Colis surprise Only)
        $combination['colis_name'] = Product::getCombinationColisName($combination);
    }

    //END DATAS RETRIVING
    //==============================================================================================================


    //==================================================================
    //INIT FUNCS (OVERRIDE)
    //==================================================================


    public function initContent()
    {
        parent::initContent();
        //init vars

        $smarty = $this->context->smarty;
        $cookie = Context::getContext()->cookie;
        $admin_cmd_token = Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)($cookie->id_employee));

        //get filter elements list
        $this->shop_zones = Zone::getZones();
        $this->shop_carriers = Carrier::getCarriersWithoutRelays($this->context->language->id);

        //get order global infos
        $orders_total = $this->_getTotal();

        $orders_total_average = 0;
        if (count($this->_list) > 0)
            $orders_total_average = $orders_total / count($this->_list);

        $orders_total_weight = $this->_getTotalWeight();

        //Updating values
        if (Tools::getValue("update") && Tools::getValue("id_order"))
            $this->update_order();

        //Exporting files
        if (Tools::getValue("export_csv") == 1 && count($this->_list)){
            $this->export_csv();
        }
        if (Tools::getValue("export_carrier_file_type") && count($this->_list)){
            $this->export_carrier_file();
        }

        else {
        //assign vars
        $smarty->assign(array(
            'orders'                    => $this->_list,
            'shop_zones'                => $this->shop_zones,
            'shop_carriers'             => $this->shop_carriers,

            //totals
            'orders_total'              => $orders_total,
            'orders_total_average'      => $orders_total_average,
            'orders_total_weight'       => $orders_total_weight,
            'orders_count'              => count($this->_list),
            'orders_colis_total_weight' => $orders_total_weight + (count($this->_list) * 2.5),

            //settings
            'token'                     => Tools::getValue("token"),
            'admin_cmd_token'           => $admin_cmd_token,

            //Sort
            'SortBy'   => Tools::getValue("SortBy"),
            'SortWay'  => Tools::getValue("SortWay"),

            //filters
            'productFilter_id_order'    => Tools::getValue("productFilter_id_order"),
            'productFilter_client_name'    => Tools::getValue("productFilter_client_name"),
            'productFilter_zone'       => Tools::getValue("productFilter_zone"),
            'productFilter_carrier'        => Tools::getValue("productFilter_carrier"),
            'productFilter_hours'    => Tools::getValue("productFilter_hours"),
        ));
        }
    }

    public function setMedia()
    {
        parent::setMedia();
        //add CSS
        $this->addCSS(_THEME_CSS_DIR_.'admin/ordered_product.css');
        $this->addCSS(_THEME_CSS_DIR_.'admin/order_preparation_print.css');
        //add JS
        $this->addJS(_THEME_JS_DIR_.'jquery.easing.1.3.js');
        $this->addJqueryUI('ui.accordion');
        $this->addJqueryUI('ui.datepicker');
        $this->addJS(_THEME_JS_DIR_.'admin/ordered_product.js');
        if ($this->tabAccess['edit'] == 1 && $this->display == 'view')
        {
            $this->addJS(_PS_JS_DIR_.'admin_order.js');
            $this->addJS(_PS_JS_DIR_.'tools.js');
            $this->addJqueryPlugin('autocomplete');
        }
    }

    //==================================================================
    //UTILS
    //==================================================================
    public function _getTotal(){
        $total = 0;
        foreach ($this->_list as $order){
            $total += $order['total_products_wt'];
        }
        return $total;
    }
    public function _setOrdersWeight(){
        foreach ($this->_list as &$order){
            $total = 0;

            foreach ($order['products'] as $product){
                $total += $product['weight'];
            }
            $order['products_weight'] = $total;
            $order['package_weight'] = $total + 2.5; //+ pain de glace
        }
    }
    public function _getTotalWeight(){
        $total = 0;
        foreach ($this->_list as &$order){
            foreach ($order['products'] as $product){
                $total += $product['weight'];
            }
        }
        return $total;
    }

    //=================================
    //LABEL SORT AND FILTER (MANUALLY)
    //==================================
    //Sort
    public function _customSortOrders(){
        if (Tools::isSubmit("SortWay") &&
            (Tools::getValue("SortBy") == "carrier" || Tools::getValue("SortBy") == "zone")){
            //get vars
            $sort_by = Tools::getValue("SortBy");
            $sort_way = Tools::getValue("SortWay");

            //sort by zone
            if ($sort_by == "zone"){
                if ($sort_way == "asc")
                    usort($this->_list, array("AdminOrdersPreparationController","_customSortZoneAsc"));
                else
                    usort($this->_list, array("AdminOrdersPreparationController","_customSortZoneDesc"));
            }
            //sort by carrier
            if ($sort_by == "carrier"){
                if ($sort_way == "asc")
                    usort($this->_list, array("AdminOrdersPreparationController","_customSortCarrierAsc"));
                else
                    usort($this->_list, array("AdminOrdersPreparationController","_customSortCarrierDesc"));
            }
        }
        //Filter
        if(Tools::getValue('productFilter_zone'))
            $this->_customFilterZone(Tools::getValue('productFilter_zone'));
        if(Tools::getValue('productFilter_carrier'))
            $this->_customFilterCarrier(Tools::getValue('productFilter_carrier'));
    }

    //Sort funcs
    public static function _customSortZoneAsc($order_a, $order_b){
        return strcmp($order_a["zone"], $order_b["zone"]);
    }
    public static function _customSortZoneDesc($order_a, $order_b){
        return -strcmp($order_a["zone"], $order_b["zone"]);
    }
    public static function _customSortCarrierAsc($order_a, $order_b){
        return strcmp($order_a["carrier"], $order_b["carrier"]);
    }
    public static function _customSortCarrierDesc($order_a, $order_b){
        return -strcmp($order_a["carrier"], $order_b["carrier"]);
    }

    //Filter funcs
    public function _customFilterZone($name){
        foreach ($this->_list as $key => $product)
            if ($product["zone"] != $name)
                unset($this->_list[$key]);
    }
    public function _customFilterCarrier($name){
        foreach ($this->_list as $key => $product)
            if ($product["carrier"] != $name)
                unset($this->_list[$key]);
    }

    //==================================================================
    //UPDATE ORDER
    //==================================================================

    public function update_order(){
        $order = new Order(Tools::getValue("id_order"));

        //error: not found
        if (!$order->id){
            die("Order not found");
        }

        if (Tools::getValue("hours")){
            $order->hour_delivery = Tools::getValue("hours");
            $order->update();
            echo "ok";
        }
        if (Tools::getValue("id_carrier")){
            $order->id_carrier = Tools::getValue("id_carrier");
            $order->update();
            echo "ok";
        }
        exit;
    }
    //==================================================================
    //EXPORT CVS
    //==================================================================
    public function export_csv(){
        $data = array(
            array(
                "Date",
                "ID commande",
                "Commentaire",
                "Note client",
                "Type de paiement",
                "Payé", //empty if payment is not "comptant ala livraison"
                "Montant commande",
                "1ere commande",
                "Prenom NOM",
                "email",//commandeur
                "Tel1 livr.",//livraison
                "Tel2 livr.",//livraison
                "Code postal livr.", //livraison
                //@TODO ask mel
                "Colis", //1 par defaut
                "Livreur",
                //@TODO ask mel to take out Zone
                "Zone", //empty
                "Horaires",
                "Nom livr.",
                "Prenom livr.",
                "Entreprise livr.",
                "Adresse", //adresse 1
                "Complement d'adresse", //adresse 2
                "Code Postal",
                "Ville",
                "Accès",
                "Étage",

            )
        );
        foreach ($this->_list as $order){

            $row = array(
                strftime("%e %b %y", strtotime($order["delivery_date"])),
                $order["id_order"],
                $order["message"],
                $order["client_note"],
                $order['payment'],
                ($order["is_payment_done"]) ? "Payement effectué" : "",
                $order["total_paid_wt"],
                ($order["is_new_customer"]) ? "Oui" : "",
                $order["client_name"], //commandeur
                $order["client_email"],//commandeur
                $order["delivery_phone"],//livraison
                $order["delivery_phone_mobile"],//livraison
                $order["delivery_postcode"], //livraison
                //@TODO ask mel
                //$order["Colis"], //1 par defaut
                1,
                $order["carrier"],
                //@TODO ask mel to take out Zone
                //$order["Zone"], //empty
                "",
                $order["hours"],
                $order["delivery_first_name"],
                strtoupper($order["delivery_last_name"]),
                $order["delivery_company"],
                $order["delivery_address1"],
                $order["delivery_address2"],
                $order["delivery_postcode"],
                $order["delivery_city"],
                $order["delivery_code"],
                $order["delivery_floor"],
            );
            $data[] = $row;
        }
        //Tools::testVar($data);
        //download
        Tools::downloadCsv($data);
        exit();
    }

    public function export_carrier_file(){
        //init vars
        //$carrier = Tools::getValue('productFilter_zone');
        $carrier = Tools::getValue('data_system_file');
        $file_type = Tools::getValue('export_carrier_file_type');

       //Download Ecolotrans Files
        if ($carrier == 'Ecolotrans'){
            $ecoloDataSystem =  new DataSystemFileEcolotrans($this->_list);

            //etiquette
            if ($file_type == 'carrier_etiquette')
                $ecoloDataSystem->packages_etiquette();

            //csv
            if ($file_type == 'carrier_csv')
                $ecoloDataSystem->delivery_csv();
        }

        //Download Ecolotrans Files
        if ($carrier == 'Jet'){
            $jetDataSystem =  new DataSystemFileJet($this->_list);

            //etiquette
            if ($file_type == 'carrier_etiquette')
                $jetDataSystem->packages_etiquette();

            //csv
            if ($file_type == 'carrier_csv')
                $jetDataSystem->delivery_system_file();
        }
        exit();
    }
} 