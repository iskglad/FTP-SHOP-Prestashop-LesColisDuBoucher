<?php

/*
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class AdminController extends AdminControllerCore {

    
    /**
     * Set the filters used for the list display
     */
    public function processFilter() {
        $prefix = str_replace(array('admin', 'controller'), '', Tools::strtolower(get_class($this)));
        // Filter memorization
        if (isset($_POST) && !empty($_POST) && isset($this->table))
            foreach ($_POST as $key => $value) {
                if (stripos($key, $this->table . 'Filter_') === 0)
                    $this->context->cookie->{$prefix . $key} = !is_array($value) ? $value : serialize($value);
                elseif (stripos($key, 'submitFilter') === 0)
                    $this->context->cookie->$key = !is_array($value) ? $value : serialize($value);
            }

        if (isset($_GET) && !empty($_GET) && isset($this->table))
            foreach ($_GET as $key => $value)
                if (stripos($key, $this->table . 'OrderBy') === 0 || stripos($key, $this->table . 'Orderway') === 0)
                    $this->context->cookie->{$prefix . $key} = $value;

        $filters = $this->context->cookie->getFamily($prefix . $this->table . 'Filter_');
        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */
            if ($value != null && !strncmp($key, $prefix . $this->table . 'Filter_', 7 + Tools::strlen($prefix . $this->table))) {
                $key = Tools::substr($key, 7 + Tools::strlen($prefix . $this->table));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmp_tab = explode('!', $key);
                $filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

                if ($field = $this->filterToField($key, $filter)) {
                    $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));
                    if (($type == 'date' || $type == 'datetime') && is_string($value))
                        $value = Tools::unSerialize($value);
                    $key = isset($tmp_tab[1]) ? $tmp_tab[0] . '.`' . $tmp_tab[1] . '`' : '`' . $tmp_tab[0] . '`';

                    // Assignement by reference
                    if (array_key_exists('tmpTableFilter', $field))
                        $sql_filter = & $this->_tmpTableFilter;
                    elseif (array_key_exists('havingFilter', $field))
                        $sql_filter = & $this->_filterHaving;
                    else
                        $sql_filter = & $this->_filter;

                    /* Only for date filtering (from, to) */
                    if (is_array($value)) {
                        if(Tools::isSubmit('submitFilterorderbydate') || Tools::isSubmit('exportorderbydate')|| Tools::isSubmit('exportorderbydatetocsv')){
                            if (isset($value[2]) && !empty($value[2])) {

                                if (!Validate::isDate($value[2]))
                                    $this->errors[] = Tools::displayError('The \'From\' date format is invalid (YYYY-MM-DD)');
                               // else
                                 //   $sql_filter .= ' AND ' . pSQL($key) . ' between \'' . pSQL(Tools::substr(Tools::dateFrom($value[2]), 0, 10)) . ' 00:00:00' . '\'' . ' AND \'' . pSQL(Tools::substr(Tools::dateFrom($value[2]), 0, 10)) . ' 23:59:59' . '\'';
                            }
                        } else {
                            if (isset($value[0]) && !empty($value[0])) {
                                if (!Validate::isDate($value[0]))
                                    $this->errors[] = Tools::displayError('The \'From\' date format is invalid (YYYY-MM-DD)');
                                else
                                    $sql_filter .= ' AND ' . pSQL($key) . ' >= \'' . pSQL(Tools::dateFrom($value[0])) . '\'';
                            }

                            if (isset($value[1]) && !empty($value[1])) {
                                if (!Validate::isDate($value[1]))
                                    $this->errors[] = Tools::displayError('The \'To\' date format is invalid (YYYY-MM-DD)');
                                else
                                    $sql_filter .= ' AND ' . pSQL($key) . ' <= \'' . pSQL(Tools::dateTo($value[1])) . '\'';
                            }
                        }
                    }
                    else {
                        $sql_filter .= ' AND ';
                        $check_key = ($key == $this->identifier || $key == '`' . $this->identifier . '`');

                        if ($type == 'int' || $type == 'bool')
                            $sql_filter .= (($check_key || $key == '`active`') ? 'a.' : '') . pSQL($key) . ' = ' . (int) $value . ' ';
                        elseif ($type == 'decimal')
                            $sql_filter .= ($check_key ? 'a.' : '') . pSQL($key) . ' = ' . (float) $value . ' ';
                        elseif ($type == 'select')
                            $sql_filter .= ($check_key ? 'a.' : '') . pSQL($key) . ' = \'' . pSQL($value) . '\' ';
                        else
                            $sql_filter .= ($check_key ? 'a.' : '') . pSQL($key) . ' LIKE \'%' . pSQL($value) . '%\' ';
                    }
                }
            }
        }
    }

    /**
     * @todo uses redirectAdmin only if !$this->ajax
     */
    public function postProcess() {
        if ($this->ajax) {
            // from ajax-tab.php
            $action = Tools::getValue('action');
            // no need to use displayConf() here
            if (!empty($action) && method_exists($this, 'ajaxProcess' . Tools::toCamelCase($action)))
                return $this->{'ajaxProcess' . Tools::toCamelCase($action)}();
            elseif (method_exists($this, 'ajaxProcess'))
                return $this->ajaxProcess();
        }
        else {
            // Process list filtering
            if ($this->filter)
                $this->processFilter();
            // If the method named after the action exists, call "before" hooks, then call action method, then call "after" hooks
            if (!empty($this->action) && method_exists($this, 'process' . ucfirst(Tools::toCamelCase($this->action)))) {
                // Hook before action
            
                Hook::exec('actionAdmin' . ucfirst($this->action) . 'Before', array('controller' => $this));
                Hook::exec('action' . get_class($this) . ucfirst($this->action) . 'Before', array('controller' => $this));
                // Call process
                $return = $this->{'process' . Tools::toCamelCase($this->action)}();
                // Hook After Action
                Hook::exec('actionAdmin' . ucfirst($this->action) . 'After', array('controller' => $this, 'return' => $return));
                Hook::exec('action' . get_class($this) . ucfirst($this->action) . 'After', array('controller' => $this, 'return' => $return));

                return $return;
            }
        }
    }
 
   public function processExportbydatetocsv()
	{
		// clean buffer
		if (ob_get_level() && ob_get_length() > 0)
			ob_clean();
		$this->getList($this->context->language->id);
		if (!count($this->_list))
			return;

		header('Content-type: text/csv');
	        header('Content-Type: application/force-download; charset=UTF-8');
			header('Cache-Control: no-store, no-cache');
		header('Content-disposition: attachment; filename="'.$this->table.'_'.date('Y-m-d_His').'.csv"');
                $k=0;
        $headers=array('Catégorie','Produit','Appelation', 'Description', 'Quantité');//'Réference', 'Caractéristique'
        $this->Row_CSV($headers);
                        /*****************/
        $content = array();
                $id_product = array();
        foreach ($this->_list as $i => $row) {
            $content[$i] = array();
                $order = new Order($row['id_order']);
                $products = $order->getProducts();
                $orderDetailList = $order->getOrderDetailList();
                foreach ($orderDetailList as $orderDetailVal) {
                 $orderDetail = new OrderDetail($orderDetailVal['id_order_detail']);   
                 $product_attribute_id = $orderDetail->product_attribute_id; 

                 $id_product[$product_attribute_id.'#'.$order->getCustomer()->firstname." ".$order->getCustomer()->lastname."#".$orderDetail->product_id."#". $order->reference] = $orderDetail->product_quantity;
                }
                
                }
        
        $j=0;
        foreach ($id_product as $key => $value) {
            $contents = array();
                $val = explode('#', $key);
                $k++;
                $product_info = new Product((int)$val[2], false, $this->context->language->id);
                $category = new Category($product_info->id_category_default, $this->context->language->id);               
                
                $attribute_liste = $product_info->getAttributeCombinationsById($val['0'],$this->context->language->id);
                $idattribute = array();
                $attribute = '';
                foreach ($attribute_liste as $attributeid) {
                    $attribute = $attributeid['attribute_name'];
                }
                
//                $contents[] = $val[3];
                $contents[] = $category->name;
                $contents[] = $product_info->name;
                // features
                $features_ = '';
				$features = array(ID_FEATURE_LABEL_ROUGE, ID_FEATURE_LABEL_BIO, ID_FEATURE_NUMBER_OF, ID_FEATURE_WEIGHT);
				foreach ($features as $keyfeatures) {
					$query = '
						SELECT fvl.`value`, fl.`name`
						FROM `lcdb_product` AS p
						LEFT JOIN `lcdb_feature_product` AS fp ON fp.`id_product` = p.`id_product`
						LEFT JOIN `lcdb_feature_value_lang` AS fvl ON fvl.`id_feature_value` = fp.`id_feature_value`
                                                LEFT JOIN `lcdb_feature_lang` AS fl ON fl.`id_feature` = fp.`id_feature`
						WHERE fp.`id_feature` = '.$keyfeatures.' AND p.`id_product` = '.$product_info->id;

					$feature = Db::getInstance()->executeS($query);
                                         if(isset($feature[0]['value']) && isset($feature[0]['name']) && strtolower($feature[0]['value']) != 'non' && strtolower($feature[0]['name']) != 'nombre de personnes' &&  strtolower($feature[0]['name']) != 'poids')
					$features_ .= $feature[0]['name'].',';//.': '.$feature[0]['value']
				}
                $contents[] = Tools::substr($features_,0,-1);
//                $contents[] = $attribute;
                $contents[] = $product_info->description_short;
                $contents[] = $value;
                $this->Row_CSV($contents);
            }
         die;
	}
        
        public function Row_CSV($content)
	{
		$wraped_data = array_map(array('CSVCore', 'wrap'), $content);
		$new_content = strip_tags(str_replace('&#039;',"'",html_entity_decode(utf8_decode(implode(";", $wraped_data)))));
        echo sprintf("%s\n", $new_content);
    }

    public function processExportbydate() {
        // clean buffer
        
        if (ob_get_level() && ob_get_length() > 0)
            ob_clean();
        $this->getList($this->context->language->id);
        if (!count($this->_list))
            return;
 $k=0;
        $headers=array('Réference','Catégorie','Description','Appelation', 'Caractéristique', 'Produit', 'Quantité');
        $content = array();
                $id_product = array();
        foreach ($this->_list as $i => $row) {
            $content[$i] = array();
                $order = new Order($row['id_order']);
                $products = $order->getProducts();
                $orderDetailList = $order->getOrderDetailList();
                foreach ($orderDetailList as $orderDetailVal) {
                 $orderDetail = new OrderDetail($orderDetailVal['id_order_detail']);   
                 $product_attribute_id = $orderDetail->product_attribute_id; 
//                 $order->reference
                 $id_product[$product_attribute_id.'#'.$order->getCustomer()->firstname." ".$order->getCustomer()->lastname."#".$orderDetail->product_id."#". $order->reference] = $orderDetail->product_quantity;
                }
                
                }
        
        foreach ($id_product as $key => $value) {
                $val = explode('#', $key);
                $k++;
                $product_info = new Product((int)$val[2], false, $this->context->language->id);
                $category = new Category($product_info->id_category_default, $this->context->language->id);
                

                $features_ = '';
				$features = array(ID_FEATURE_LABEL_ROUGE, ID_FEATURE_LABEL_BIO, ID_FEATURE_NUMBER_OF, ID_FEATURE_WEIGHT);
				foreach ($features as $keyfeatures) {
					$query = '
						SELECT fvl.`value`, fl.`name`
						FROM `lcdb_product` AS p
						LEFT JOIN `lcdb_feature_product` AS fp ON fp.`id_product` = p.`id_product`
						LEFT JOIN `lcdb_feature_value_lang` AS fvl ON fvl.`id_feature_value` = fp.`id_feature_value`
                                                LEFT JOIN `lcdb_feature_lang` AS fl ON fl.`id_feature` = fp.`id_feature`
						WHERE fp.`id_feature` = '.$keyfeatures.' AND p.`id_product` = '.$product_info->id;

					$feature = Db::getInstance()->executeS($query);
                                        if(strtolower($feature[0]['value']) != 'non' && strtolower($feature[0]['name']) != 'nombre de personnes')
					$features_ .= $feature[0]['name'].': '.$feature[0]['value'].',';
				}
                
                
                $attribute_liste = $product_info->getAttributeCombinationsById($val['0'],$this->context->language->id);
                $idattribute = array();
                $attribute = '';
                foreach ($attribute_liste as $attributeid) {
                    $attribute = $attributeid['attribute_name'];
                }
                
                $content[$i.$key][] = $val[3];
                $content[$i.$key][] = $category->name;
                $content[$i.$key][] = Tools::substr(strip_tags($product_info->description_short),0,20);
                $content[$i.$key][] = Tools::substr(Tools::substr($features_,0,-1),0,20);
                $content[$i.$key][] = Tools::substr($attribute,0,20);
                $content[$i.$key][] = Tools::substr($product_info->name,0,20);
                $content[$i.$key][] = $value;
            }
        $pdfGenerator = new PDFGenerator();
        $pdfGenerator->AddPage("L");
        $pdfGenerator->BasicTable($headers, $content);
        $pdfGenerator->Output($this->table . '_' . date('Y-m-d_His'),'I');
    }
    
    /**
     * Retrieve GET and POST value and translate them to actions
     */
    public function initProcess() {
        // Manage list filtering
        if (Tools::isSubmit('submitFilter' . $this->table) || $this->context->cookie->{'submitFilter' . $this->table} !== false || Tools::getValue($this->table . 'Orderby') || Tools::getValue($this->table . 'Orderway'))
            $this->filter = true;

        $this->id_object = (int) Tools::getValue($this->identifier);

        /* Delete object image */
        if (isset($_GET['deleteImage'])) {
            if ($this->tabAccess['delete'] === '1')
                $this->action = 'delete_image';
            else
                $this->errors[] = Tools::displayError('You do not have permission to delete this.');
        }
        /* Delete object */
        elseif (isset($_GET['delete' . $this->table])) {
            if ($this->tabAccess['delete'] === '1')
                $this->action = 'delete';
            else
                $this->errors[] = Tools::displayError('You do not have permission to delete this.');
        }
        /* Change object statuts (active, inactive) */
        elseif ((isset($_GET['status' . $this->table]) || isset($_GET['status'])) && Tools::getValue($this->identifier)) {
            if ($this->tabAccess['edit'] === '1')
                $this->action = 'status';
            else
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
        }
        /* Move an object */
        elseif (isset($_GET['position'])) {
            if ($this->tabAccess['edit'] == '1')
                $this->action = 'position';            
            else
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
        }
        elseif (Tools::getValue('submitAdd' . $this->table) || Tools::getValue('submitAdd' . $this->table . 'AndStay') || Tools::getValue('submitAdd' . $this->table . 'AndPreview')) {
            // case 1: updating existing entry
            if ($this->id_object) {
                if ($this->tabAccess['edit'] === '1') {
                    $this->action = 'save';
                    if (Tools::getValue('submitAdd' . $this->table . 'AndStay'))
                        $this->display = 'edit';
                    else
                        $this->display = 'list';
                }
                else
                    $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
            // case 2: creating new entry
            else {
                if ($this->tabAccess['add'] === '1') {
                    $this->action = 'save';
                    if (Tools::getValue('submitAdd' . $this->table . 'AndStay'))
                        $this->display = 'edit';
                    else
                        $this->display = 'list';
                }
                else
                    $this->errors[] = Tools::displayError('You do not have permission to add this.');
            }
        }
        elseif (isset($_GET['add' . $this->table])) {
            if ($this->tabAccess['add'] === '1') {
                $this->action = 'new';
                $this->display = 'add';
            }
            else
                $this->errors[] = Tools::displayError('You do not have permission to add this.');
        }
        elseif (isset($_GET['update' . $this->table]) && isset($_GET[$this->identifier])) {
            $this->display = 'edit';
            if ($this->tabAccess['edit'] !== '1')
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
        }
        elseif (isset($_GET['view' . $this->table])) {
            if ($this->tabAccess['view'] === '1') {
                $this->display = 'view';
                $this->action = 'view';
            }
            else
                $this->errors[] = Tools::displayError('You do not have permission to view this.');
        }
        elseif (isset($_GET['export' . $this->table])) {
            if ($this->tabAccess['view'] === '1')
                $this->action = 'export';
        }elseif (isset($_GET['export' . $this->table .'bydate'])) {
            if ($this->tabAccess['view'] === '1')
                $this->action = 'exportbydate';
        }elseif (isset($_GET['export' . $this->table .'bydatetocsv'])) {
            if ($this->tabAccess['view'] === '1')
                $this->action = 'exportbydatetocsv';
        }
        /* Cancel all filters for this tab */
        elseif (isset($_POST['submitReset' . $this->table]))
            $this->action = 'reset_filters';
        /* Submit options list */
        elseif (Tools::getValue('submitOptions' . $this->table) || Tools::getValue('submitOptions')) {
            $this->display = 'options';
            if ($this->tabAccess['edit'] === '1')
                $this->action = 'update_options';
            else
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
        }
        elseif (Tools::isSubmit('submitFields') && $this->required_database && $this->tabAccess['add'] === '1' && $this->tabAccess['delete'] === '1')
            $this->action = 'update_fields';
        elseif (is_array($this->bulk_actions))
            foreach ($this->bulk_actions as $bulk_action => $params) {
                if (Tools::isSubmit('submitBulk' . $bulk_action . $this->table) || Tools::isSubmit('submitBulk' . $bulk_action)) {
                    if ($this->tabAccess['edit'] === '1') {
                        $this->action = 'bulk' . $bulk_action;
                        $this->boxes = Tools::getValue($this->table . 'Box');
                    }
                    else
                        $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                    break;
                }
                elseif (Tools::isSubmit('submitBulk')) {
                    if ($this->tabAccess['edit'] === '1') {
                        $this->action = 'bulk' . Tools::getValue('select_submitBulk');
                        $this->boxes = Tools::getValue($this->table . 'Box');
                    }
                    else
                        $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                    break;
                }
            }
        elseif (!empty($this->fields_options) && empty($this->fields_list))
            $this->display = 'options';
    }
    
  
}