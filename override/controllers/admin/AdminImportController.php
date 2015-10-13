<?php

class AdminImportController extends AdminImportControllerCore
{
    public function attributeImport()
    {
        $default_language = Configuration::get('PS_LANG_DEFAULT');

        $groups = array();
        foreach (AttributeGroup::getAttributesGroups($default_language) as $group)
            $groups[$group['name']] = (int)$group['id_attribute_group'];

        $attributes = array();
        foreach (Attribute::getAttributes($default_language) as $attribute)
            $attributes[$attribute['attribute_group'].'_'.$attribute['name']] = (int)$attribute['id_attribute'];

        $this->receiveTab();
        $handle = $this->openCsvFile();
        AdminImportController::setLocale();
        for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator); $current_line++)
        {
            if (count($line) == 1 && empty($line[0]))
                continue;

            if (Tools::getValue('convert'))
                $line = $this->utf8EncodeArray($line);
            $info = AdminImportController::getMaskedRow($line);
            $info = array_map('trim', $info);

            AdminImportController::setDefaultValues($info);

            if (!Shop::isFeatureActive())
                $info['shop'] = 1;
            elseif (!isset($info['shop']) || empty($info['shop']))
                $info['shop'] = implode($this->multiple_value_separator, Shop::getContextListShopID());

            // Get shops for each attributes
            $info['shop'] = explode($this->multiple_value_separator, $info['shop']);

            $id_shop_list = array();
            foreach ($info['shop'] as $shop)
                if (!is_numeric($shop))
                    $id_shop_list[] = Shop::getIdByName($shop);
                else
                    $id_shop_list[] = $shop;

            $product = new Product((int)$info['id_product'], false, $default_language);
            $id_image = null;

            //delete existing images if "delete_existing_images" is set to 1
            if (array_key_exists('delete_existing_images', $info) && $info['delete_existing_images'] && !isset($this->cache_image_deleted[(int)$product->id]))
            {
                $product->deleteImages();
                $this->cache_image_deleted[(int)$product->id] = true;
            }

            if (isset($info['image_url']) && $info['image_url'])
            {
                $product_has_images = (bool)Image::getImages($this->context->language->id, $product->id);

                $url = $info['image_url'];
                $image = new Image();
                $image->id_product = (int)$product->id;
                $image->position = Image::getHighestPosition($product->id) + 1;
                $image->cover = (!$product_has_images) ? true : false;

                $field_error = $image->validateFields(UNFRIENDLY_ERROR, true);
                $lang_field_error = $image->validateFieldsLang(UNFRIENDLY_ERROR, true);

                if ($field_error === true && $lang_field_error === true && $image->add())
                {
                    $image->associateTo($id_shop_list);
                    if (!AdminImportController::copyImg($product->id, $image->id, $url))
                    {
                        $this->warnings[] = sprintf(Tools::displayError('Error copying image: %s'), $url);
                        $image->delete();
                    }
                    else
                        $id_image = array($image->id);
                }
                else
                {
                    $this->warnings[] = sprintf(
                        Tools::displayError('%s cannot be saved'),
                        (isset($image->id_product) ? ' ('.$image->id_product.')' : '')
                    );
                    $this->errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').mysql_error();
                }
            }
            elseif (isset($info['image_position']) && $info['image_position'])
            {
                $images = $product->getImages($default_language);

                if ($images)
                    foreach ($images as $row)
                        if ($row['position'] == (int)$info['image_position'])
                        {
                            $id_image = array($row['id_image']);
                            break;
                        }
                if (!$id_image)
                    $this->warnings[] = sprintf(
                        Tools::displayError('No image found for combination with id_product = %s and image position = %s.'),
                        $product->id,
                        (int)$info['image_position']
                    );
            }

            $id_attribute_group = 0;
            // groups
            $groups_attributes = array();
            foreach (explode($this->multiple_value_separator, $info['group']) as $key => $group)
            {
                $tab_group = explode(':', $group);
                $group = trim($tab_group[0]);
                if (!isset($tab_group[1]))
                    $type = 'select';
                else
                    $type = trim($tab_group[1]);

                // sets group
                $groups_attributes[$key]['group'] = $group;

                // if position is filled
                if (isset($tab_group[2]))
                    $position = trim($tab_group[2]);
                else
                    $position = false;

                if (!isset($groups[$group]))
                {
                    $obj = new AttributeGroup();
                    $obj->is_color_group = false;
                    $obj->group_type = pSQL($type);
                    $obj->name[$default_language] = $group;
                    $obj->public_name[$default_language] = $group;
                    $obj->position = (!$position) ? AttributeGroup::getHigherPosition() + 1 : $position;

                    if (($field_error = $obj->validateFields(UNFRIENDLY_ERROR, true)) === true &&
                        ($lang_field_error = $obj->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true)
                    {
                        $obj->add();
                        $obj->associateTo($id_shop_list);
                        $groups[$group] = $obj->id;
                    }
                    else
                        $this->errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '');

                    // fils groups attributes
                    $id_attribute_group = $obj->id;
                    $groups_attributes[$key]['id'] = $id_attribute_group;
                }
                else // alreay exists
                {
                    $id_attribute_group = $groups[$group];
                    $groups_attributes[$key]['id'] = $id_attribute_group;
                }
            }

            // inits attribute
            $id_product_attribute = 0;
            $id_product_attribute_update = false;
            $attributes_to_add = array();

            // for each attribute
            foreach (explode($this->multiple_value_separator, $info['attribute']) as $key => $attribute)
            {
                $tab_attribute = explode(':', $attribute);
                $attribute = trim($tab_attribute[0]);
                // if position is filled
                if (isset($tab_attribute[1]))
                    $position = trim($tab_attribute[1]);
                else
                    $position = false;

                if (isset($groups_attributes[$key]))
                {
                    $group = $groups_attributes[$key]['group'];
                    if (!isset($attributes[$group.'_'.$attribute]) && count($groups_attributes[$key]) == 2)
                    {
                        $id_attribute_group = $groups_attributes[$key]['id'];
                        $obj = new Attribute();
                        // sets the proper id (corresponding to the right key)
                        $obj->id_attribute_group = $groups_attributes[$key]['id'];
                        $obj->name[$default_language] = str_replace('\n', '', str_replace('\r', '', $attribute));
                        $obj->position = (!$position) ? Attribute::getHigherPosition($groups[$group]) + 1 : $position;

                        if (($field_error = $obj->validateFields(UNFRIENDLY_ERROR, true)) === true &&
                            ($lang_field_error = $obj->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true)
                        {
                            $obj->add();
                            $obj->associateTo($id_shop_list);
                            $attributes[$group.'_'.$attribute] = $obj->id;
                        }
                        else
                            $this->errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '');
                    }

                    $info['minimal_quantity'] = isset($info['minimal_quantity']) && $info['minimal_quantity'] ? (int)$info['minimal_quantity'] : 1;

                    $info['wholesale_price'] = str_replace(',', '.', $info['wholesale_price']);
                    $info['price'] = str_replace(',', '.', $info['price']);
                    $info['ecotax'] = str_replace(',', '.', $info['ecotax']);
                    $info['weight'] = str_replace(',', '.', $info['weight']);

                    // if a reference is specified for this product, get the associate id_product_attribute to UPDATE
                    if (isset($info['reference']) && !empty($info['reference']))
                    {
                        $id_product_attribute = Combination::getIdByReference($product->id, strval($info['reference']));

                        // updates the attribute
                        if ($id_product_attribute)
                        {
                            // gets all the combinations of this product
                            $attribute_combinations = $product->getAttributeCombinations($default_language);
                            foreach ($attribute_combinations as $attribute_combination)
                            {
                                if ($id_product_attribute && in_array($id_product_attribute, $attribute_combination))
                                {
                                    $product->updateAttribute(
                                        $id_product_attribute,
                                        (float)$info['wholesale_price'],
                                        (float)$info['price'],
                                        (float)$info['weight'],
                                        0,
                                        (float)$info['ecotax'],
                                        $id_image,
                                        strval($info['reference']),
                                        strval($info['ean13']),
                                        (int)$info['default_on'],
                                        0,
                                        strval($info['upc']),
                                        (int)$info['minimal_quantity'],
                                        0,
                                        0,
                                        null,
                                        $id_shop_list
                                    );

                                    $id_product_attribute_update = true;
                                }
                            }
                        }
                    }

                    // if no attribute reference is specified, creates a new one
                    if (!$id_product_attribute)
                    {
                        $id_product_attribute = $product->addCombinationEntity(
                            (float)$info['wholesale_price'],
                            (float)$info['price'],
                            (float)$info['weight'],
                            0,
                            (float)$info['ecotax'],
                            (int)$info['quantity'],
                            $id_image,
                            strval($info['reference']),
                            0,
                            strval($info['ean13']),
                            (int)$info['default_on'],
                            0,
                            strval($info['upc']),
                            (int)$info['minimal_quantity'],
                            $id_shop_list
                        );
                    }

                    // fills our attributes array, in order to add the attributes to the product_attribute afterwards
                    $attributes_to_add[] = (int)$attributes[$group.'_'.$attribute];

                    // after insertion, we clean attribute position and group attribute position
                    $obj = new Attribute();
                    $obj->cleanPositions((int)$id_attribute_group, false);
                    AttributeGroup::cleanPositions();
                }
            }

            $product->checkDefaultAttributes();
            if (!$product->cache_default_attribute)
                Product::updateDefaultAttribute($product->id);
            if ($id_product_attribute)
            {
                // now adds the attributes in the attribute_combination table
                if ($id_product_attribute_update)
                {
                    Db::getInstance()->execute('
						DELETE FROM '._DB_PREFIX_.'product_attribute_combination
						WHERE id_product_attribute = '.(int)$id_product_attribute);
                }

                foreach ($attributes_to_add as $attribute_to_add)
                {
                    Db::getInstance()->execute('
						INSERT IGNORE INTO '._DB_PREFIX_.'product_attribute_combination (id_attribute, id_product_attribute)
						VALUES ('.(int)$attribute_to_add.','.(int)$id_product_attribute.')');
                }

                StockAvailable::setQuantity($product->id, $id_product_attribute, (int)$info['quantity']);
            }
        }

        $this->closeCsvFile($handle);
    }
}

