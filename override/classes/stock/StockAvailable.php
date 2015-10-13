<?php

class StockAvailable extends StockAvailableCore
{
    /**
     * For a given id_product and id_product_attribute, gets its stock available
     *
     * @param int $id_product
     * @param int $id_product_attribute Optional
     * @param int $id_shop Optional : gets context by default
     * @return int Quantity
     */
    public static function getQuantityAvailableByProduct($id_product = null, $id_product_attribute = null, $id_shop = null)
    {
        // if null, it's a product without attributes
        if ($id_product_attribute === null)
            $id_product_attribute = 0;

        $query = new DbQuery();
        $query->select('SUM(quantity)');
        $query->from('stock_available');

        // if null, it's a product without attributes
        if ($id_product !== null)
            $query->where('id_product = '.(int)$id_product);

        $query->where('id_product_attribute = '.(int)$id_product_attribute);
        $query = StockAvailable::addSqlShopRestriction($query, $id_shop);

        $stock = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        if ($stock < 0)
            $stock = NB_STOCK_INFINITE; //see config/setting.inc.php line 64
        return $stock;
    }

}

