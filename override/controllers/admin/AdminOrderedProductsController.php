<?php

class AdminOrderedProductsController extends AdminController{
    public $cumulated_products;
    public $shop_suppliers;
    public $shop_labels;
    public $shop_product_categories;

    public function __construct()
    {
        $this->table = 'order_detail';
        $this->className = 'OrderDetail';
        $this->lang = false;
        $this->explicitSelect = true;

        $this->deleted = false;
        $this->context = Context::getContext();

        //=================================
        //SELECT
        //==================================

        $select = '
            a.id_order_detail       as  id_order_detail,
            a.id_order              as  id_order,
            a.product_id            as  id_product,
            a.product_attribute_id  as  id_product_attribute,
            a.product_quantity      as  quantity,

            da.lastname             as  delivery_last_name,
            da.firstname            as  delivery_first_name,
            da.company              as  delivery_company,
            da.postcode             as  delivery_post_code,

            o.date_delivery         as  delivery_date
            ,
            s.id_supplier           as  id_supplier,
            s.name                  as  supplier,

            plang.id_lang           as  id_lang,
            plang.name              as  product_name,
            plang.description_short as  description_short,

            calang.name             as  category
        ';
        $from = '
            `'._DB_PREFIX_.$this->table.'`    as  a
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
            LEFT JOIN `'._DB_PREFIX_.'orders` o             ON  (a.`id_order` = o.`id_order`)
            LEFT JOIN `'._DB_PREFIX_.'address` da           ON  (o.`id_address_delivery` = da.`id_address`)
        ';

        //=================================
        //ORDER BY
        //==================================
        $order_by = Tools::getValue("SortBy");
        if ($order_by == "" || $order_by == "label") //label are ordered manually, not with "ORDER BY" clause.
            $order_by = "id_order_detail";

        $order_by .= " ".(Tools::getValue("SortWay") ? Tools::getValue("SortWay"): "asc");


        //=================================
        //DATE
        //==================================
        $date_interval_begin = date('Y-m-d', strtotime('+2 days')); //default value: today + 2days
        $date_interval_end = $date_interval_begin; //default value: same

        if (Tools::isSubmit('date_interval_begin') && Tools::isSubmit('date_interval_end')){
            $date_interval_begin = Tools::getValue('date_interval_begin');
            $date_interval_end = Tools::getValue('date_interval_end');
        }
        $day_after_interval = date('Y-m-d',strtotime($date_interval_end . "+1 days"));


        //=================================
        //FILTERS AND WHERE CLAUSE
        //==================================
        //specify language
        $where = "plang.id_lang = calang.id_lang AND plang.id_lang = ".$this->context->language->id;

        //Where choosen date and choosen Date + 1 for UPS.
        //The list will be manualy clean later to keep UPS only for date+1Day.
        $where = ' (DATE(o.date_delivery) >= DATE("'.$date_interval_begin.'")
                                AND DATE(o.date_delivery) <= DATE("'.$day_after_interval.'"))';

        //Valid payment state only
        $where .= ' AND o.current_state != '.ID_ORDER_STATE_CANCELED;
        $where .= ' AND o.current_state != '.ID_ORDER_STATE_PAYMENT_ERROR;
        $where .= ' AND o.current_state != '.ID_ORDER_STATE_REFUND;
        $where .= ' AND o.current_state != '.ID_ORDER_STATE_WAITING_BANKWIRE_PAYMENT;
        $where .= ' AND o.current_state != '.ID_ORDER_STATE_WAITING_PAYPAL_PAYMENT;

        //Filters
        if(Tools::getValue('productFilter_supplier'))
            $where .= ' AND s.name = "'.Tools::getValue('productFilter_supplier').'"';
        if(Tools::getValue('productFilter_category'))
            $where .= ' AND calang.name  = "'.Tools::getValue('productFilter_category').'"';
        if(Tools::getValue('productFilter_name'))
            $where .= ' AND product_name  LIKE "%'.Tools::getValue('productFilter_name').'%"';
        if(Tools::getValue('productFilter_short_description'))
            $where .= ' AND description_short  LIKE "%'.Tools::getValue('productFilter_short_description').'%"';
        if(Tools::getValue('productFilter_quantity'))
            $where .= ' AND quantity = '.Tools::getValue('productFilter_quantity');


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
        //UPDATE LIST INFOS + UPS CLEAN
        //==================================
        foreach ($this->_list as $key => &$row){
            $row_date = date("Y-m-d", strtotime($row["delivery_date"]));

           //if UPS date (when date = choosen date + 1day)
             if ( $row_date == $day_after_interval){
                if (ID_ZONE_UPS == Address::getZoneByZipCode($row["delivery_post_code"]))
                    $this->_updateProductAttributes($row); //update UPS product infos
                else
                    unset($this->_list[$key]);//delete product row
             }

            else if ( $row_date == $date_interval_begin){
                if (ID_ZONE_UPS != Address::getZoneByZipCode($row["delivery_post_code"]))
                    $this->_updateProductAttributes($row); //update !UPS product infos
                else
                    unset($this->_list[$key]);//delete product row
            }
            else
                $this->_updateProductAttributes($row); //update product
        }
        $this->_cumulProducts($this->_list);

        //=================================
        //LABEL SORT AND FILTER (MANUALLY)
        //==================================
        //Sort
        if (Tools::isSubmit("SortWay")){
            //get vars
            $sort_by = Tools::getValue("SortBy");
            $sort_way = Tools::getValue("SortWay");

            //sort by Label
            if ($sort_by == "label"){
                if ($sort_way == "asc")
                    usort($this->cumulated_products, array("AdminOrderedProductsController","_customSortLabelAsc"));
                else
                    usort($this->cumulated_products, array("AdminOrderedProductsController","_customSortLabelDesc"));
            }
            //sort by quantity
            if ($sort_by == "quantity"){
                if ($sort_way == "asc")
                    usort($this->cumulated_products, array("AdminOrderedProductsController","_customSortQuantityAsc"));
                else
                    usort($this->cumulated_products, array("AdminOrderedProductsController","_customSortQuantityDesc"));
            }
        }

        //Filter
        if(Tools::getValue('productFilter_label'))
            $this->_customFilterLabel(Tools::getValue('productFilter_label'));



        //=================================
        //ASSIGN VARS
        //==================================
        $smarty = $this->context->smarty;
        $smarty->assign(array(
            'date_interval_begin'      =>  $date_interval_begin,
            'date_interval_end'        =>  $date_interval_end,
            'use_date_interval'        =>  Tools::getValue('use_date_interval'),
        ));

        parent::__construct();
    }


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
        $this->shop_suppliers = Supplier::getShopSuppliers();
        $this->shop_labels = Attribute::getShopLabels();
        $this->shop_product_categories = Category::getChildren(ID_CATEGORY_MAIN, $this->context->language->id, $this->context->shop->id);

        //Exporting files
        if (Tools::getValue("export_csv") == 1){
            $this->exportCsv();
            exit();
        }
        else {
        //assign vars
        $smarty->assign(array(
            'products'                  => $this->cumulated_products,
            'shop_suppliers'             => $this->shop_suppliers,
            'shop_labels'               => $this->shop_labels,
            'shop_product_categories'   => $this->shop_product_categories,

            //settings
            'token'                     => Tools::getValue("token"),
            'admin_cmd_token'           => $admin_cmd_token,

            //Sort
            'SortBy'   => Tools::getValue("SortBy"),
            'SortWay'  => Tools::getValue("SortWay"),

            //filters
            'productFilter_supplier'    => Tools::getValue("productFilter_supplier"),
            'productFilter_category'    => Tools::getValue("productFilter_category"),
            'productFilter_label'       => Tools::getValue("productFilter_label"),
            'productFilter_name'        => Tools::getValue("productFilter_name"),
            'productFilter_quantity'    => Tools::getValue("productFilter_quantity"),
            'productFilter_short_description' => Tools::getValue("productFilter_short_description")
        ));
        }
    }

    public function setMedia()
    {
        parent::setMedia();
        //add CSS
        $this->addCSS(_THEME_CSS_DIR_.'admin/ordered_product.css');
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
    //UPDATE PRODUCT ATTRIBUTES
    //Set unretrieved infos (promo, label, colis, etc)
    //==================================================================


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


    //==================================================================
    //CUMUL PRODUCT
    //Combine same product from different clients
    //==================================================================


    public function _cumulProducts($products){
        //init vars
        $key = 0;
        $product = 0;
        $this->cumulated_products = array();

        while (count($products)){
            //pop first elem until array empty
            $product = array_shift($products);

            //If product is in cumullist
            $key = $this->_findInCumulList($product);
            if ($key !== NULL)
                $this->_cumulate($key, $product); //add quantity and buyer
            else
                $this->_addToCumulList($product); //add new cumul product row
        }
    }

    //FindInCumulList
    //Description:
    //Find product in cumul list
    //Product are same if equivalent product_id, label (or colis), and promo (promo "lcdb" and "none" have already been replaced by "")

    public function _findInCumulList($product){
        if (count($this->cumulated_products) > 0)

            foreach ($this->cumulated_products as $key => $cumulated_product){
                if ($cumulated_product["id_product"] == $product["id_product"] &&
                    $cumulated_product["label_name"] == $product["label_name"] &&
                    $cumulated_product["colis_name"] == $product["colis_name"] &&
                    $cumulated_product["promo_name"] == $product["promo_name"])
                    return $key;
            }
        return NULL;
    }

    public function _cumulate($key, $product){
        //add quantity
        $this->cumulated_products[$key]["quantity"] += $product["quantity"];
        //add buyer
        $buyer = $this->_createBuyer($product);
        array_push($this->cumulated_products[$key]['buyers'], $buyer);
    }
    public function _addToCumulList($product){
        $new_cumul_row = $product;

        //add buyer
        $new_cumul_row['buyers'] = array(); //init
        $buyer = $this->_createBuyer($product);
        array_push($new_cumul_row['buyers'], $buyer);

        //unset unused field
        unset($new_cumul_row['delivery_last_name']);
        unset($new_cumul_row['delivery_first_name']);
        unset($new_cumul_row['delivery_company_name']);

        //add to list
        array_push($this->cumulated_products, $new_cumul_row);
    }
    public function _createBuyer($product){
        $buyer = array(
            "id_order"      => $product['id_order'],
            "last_name"     => $product['delivery_last_name'],
            "first_name"    => $product['delivery_first_name'],
            "company"       => $product['delivery_company'],
            "quantity"      => $product['quantity'],
            "is_pro"        => $product['isPro']
        );
        return $buyer;
    }
    //END CUMUL PRODUCT
    //==================================================================




    //==================================================================
    //SORT
    //==================================================================

    public static function _customSortLabelAsc($product_a, $product_b){
        return strcmp($product_a["label_name"], $product_b["label_name"]);
    }
    public static function _customSortLabelDesc($product_a, $product_b){
        return -(strcmp($product_a["label_name"], $product_b["label_name"]));
    }
    public static function _customSortQuantityAsc($product_a, $product_b){
        if ($product_a["quantity"] > $product_b["quantity"])
            return 1;
        if ($product_a["quantity"] < $product_b["quantity"])
            return -1;
        if ($product_a["quantity"] == $product_b["quantity"])
            return 0;
    }
    public static function _customSortQuantityDesc($product_a, $product_b){
        if ($product_a["quantity"] > $product_b["quantity"])
            return -1;
        if ($product_a["quantity"] < $product_b["quantity"])
            return 1;
        if ($product_a["quantity"] == $product_b["quantity"])
            return 0;
    }
    //==================================================================
    //FILTER LABEL
    //==================================================================
    public function _customFilterLabel($name){
        foreach ($this->cumulated_products as $key => $product){
            if ($product["label_name"] != $name){
                unset($this->cumulated_products[$key]);
            }
        }
    }
    //==================================================================
    //EXPORT CVS
    //==================================================================
    public function exportCsv(){
        $data = array(
            array("Viande", "Morceau", "label", "Colisage", "Qté")
        );
        foreach ($this->cumulated_products as $product){
            $row = array(
                $product['category'],
                $product['product_name'],
                $product['label_name'],
                $product['description_short'],
                $product['quantity']
            );
            $data[] = $row;
        }
        //Tools::testVar($data);
        //download
        Tools::downloadCsv($data);
    }
} 