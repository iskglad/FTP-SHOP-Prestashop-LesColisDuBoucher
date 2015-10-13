<?php

class Supplier extends SupplierCore
{
    //Retrieve Active Supplier
      public static function getShopSuppliers(){
            $query = "SELECT * FROM "._DB_PREFIX_."supplier WHERE active = 1";
            return Db::getInstance()->executeS($query);
      }
}

