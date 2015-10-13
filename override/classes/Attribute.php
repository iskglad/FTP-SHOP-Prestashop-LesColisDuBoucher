<?php

class Attribute extends AttributeCore
{
    public static function getShopLabels(){
        $select = "SELECT * FROM "._DB_PREFIX_."attribute_lang al";
        $join = '
            LEFT JOIN '._DB_PREFIX_.'attribute a ON al.id_attribute = a.id_attribute
        ';
        $where = "WHERE a.id_attribute_group = ".ID_ATTRIBUTE_GROUP_LABEL;
        return Db::getInstance()->executeS($select.' '.$join.' '.$where);
    }
}

