<?php

class AdminTranslationsController extends AdminTranslationsControllerCore
{
    public function initFormBack()
    {
        $name_var = $this->translations_informations[$this->type_selected]['var'];
        $GLOBALS[$name_var] = $this->fileExists();
        $missing_translations_back = array();

        // Get all types of file (PHP, TPL...) and a list of files to parse by folder
        $files_per_directory = $this->getFileToParseByTypeTranslation();

        foreach ($files_per_directory['php'] as $dir => $files)
            foreach ($files as $file)
                // Check if is a PHP file and if the override file exists
                if (preg_match('/^(.*)\.php$/', $file) && Tools::file_exists_cache($file_path = $dir.$file) && !in_array($file, self::$ignore_folder))
                {
                    $prefix_key = basename($file);
                    // -4 becomes -14 to remove the ending "Controller.php" from the filename
                    if (strpos($file, 'Controller.php') !== false)
                        $prefix_key = basename(substr($file, 0, -14));
                    else if (strpos($file, 'Helper') !== false)
                        $prefix_key = 'Helper';

                    if ($prefix_key == 'Admin')
                        $prefix_key = 'AdminController';

                    if ($prefix_key == 'PaymentModule.php')
                        $prefix_key = 'PaymentModule';

                    // Get content for this file
                    $content = file_get_contents($file_path);

                    // Parse this content
                    $matches = $this->userParseFile($content, $this->type_selected, 'php');

                    foreach ($matches as $key)
                    {
                        // Caution ! front has underscore between prefix key and md5, back has not
                        if (isset($GLOBALS[$name_var][$prefix_key.md5($key)]))
                            $tabs_array[$prefix_key][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$name_var][$prefix_key.md5($key)], ENT_COMPAT, 'UTF-8'));
                        else
                        {
                            if (!isset($tabs_array[$prefix_key][$key]['trad']))
                            {
                                $tabs_array[$prefix_key][$key]['trad'] = '';
                                if (!isset($missing_translations_back[$prefix_key]))
                                    $missing_translations_back[$prefix_key] = 1;
                                else
                                    $missing_translations_back[$prefix_key]++;
                            }
                        }
                        $tabs_array[$prefix_key][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
                    }
                }

        foreach ($files_per_directory['specific'] as $dir => $files)
            foreach ($files as $file)
                if (Tools::file_exists_cache($file_path = $dir.$file) && !in_array($file, self::$ignore_folder))
                {
                    $prefix_key = 'index';

                    // Get content for this file
                    $content = file_get_contents($file_path);

                    // Parse this content
                    $matches = $this->userParseFile($content, $this->type_selected, 'specific');

                    foreach ($matches as $key)
                    {
                        // Caution ! front has underscore between prefix key and md5, back has not
                        if (isset($GLOBALS[$name_var][$prefix_key.md5($key)]))
                            $tabs_array[$prefix_key][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$name_var][$prefix_key.md5($key)], ENT_COMPAT, 'UTF-8'));
                        else
                        {
                            if (!isset($tabs_array[$prefix_key][$key]['trad']))
                            {
                                $tabs_array[$prefix_key][$key]['trad'] = '';
                                if (!isset($missing_translations_back[$prefix_key]))
                                    $missing_translations_back[$prefix_key] = 1;
                                else
                                    $missing_translations_back[$prefix_key]++;
                            }
                        }
                        $tabs_array[$prefix_key][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
                    }
                }

        foreach ($files_per_directory['tpl'] as $dir => $files)
            foreach ($files as $file)
                if (preg_match('/^(.*).tpl$/', $file) && Tools::file_exists_cache($file_path = $dir.$file))
                {
                    // get controller name instead of file name
                    $prefix_key = Tools::toCamelCase(str_replace(_PS_ADMIN_DIR_.'/themes', '', $file_path), true);
                    $pos = strrpos($prefix_key, DIRECTORY_SEPARATOR);
                    $tmp = substr($prefix_key, 0, $pos);

                    if (preg_match('#controllers#', $tmp))
                    {
                        $parent_class = explode(DIRECTORY_SEPARATOR, $tmp);
                        $override = array_search('override', $parent_class);
                        if ($override !== false)
                            $prefix_key = 'Admin'.ucfirst($parent_class[count($parent_class) - 1]);
                        else
                        {
                            $key = array_search('controllers', $parent_class);
                            $prefix_key = 'Admin'.ucfirst($parent_class[$key + 1]);
                        }
                    }
                    else
                        $prefix_key = 'Admin'.ucfirst(substr($tmp, strrpos($tmp, DIRECTORY_SEPARATOR) + 1, $pos));

                    // Adding list, form, option in Helper Translations
                    $list_prefix_key = array('AdminHelpers', 'AdminList', 'AdminView', 'AdminOptions', 'AdminForm', 'AdminHelpAccess');
                    if (in_array($prefix_key, $list_prefix_key))
                        $prefix_key = 'Helper';

                    // Adding the folder backup/download/ in AdminBackup Translations
                    if ($prefix_key == 'AdminDownload')
                        $prefix_key = 'AdminBackup';

                    // use the prefix "AdminController" (like old php files 'header', 'footer.inc', 'index', 'login', 'password', 'functions'
                    if ($prefix_key == 'Admin' || $prefix_key == 'AdminTemplate')
                        $prefix_key = 'AdminController';

                    $new_lang = array();

                    // Get content for this file
                    $content = file_get_contents($file_path);

                    // Parse this content
                    $matches = $this->userParseFile($content, $this->type_selected, 'tpl');

                    /* Get string translation for each tpl file */
                    foreach ($matches as $english_string)
                    {
                        if (empty($english_string))
                        {
                            $this->errors[] = sprintf($this->l('Error in template - Empty string found, please edit: "%s"'), $file_path);
                            $new_lang[$english_string] = '';
                        }
                        else
                        {
                            $trans_key = $prefix_key.md5($english_string);

                            if (isset($GLOBALS[$name_var][$trans_key]))
                                $new_lang[$english_string]['trad'] = html_entity_decode($GLOBALS[$name_var][$trans_key], ENT_COMPAT, 'UTF-8');
                            else
                            {
                                if (!isset($new_lang[$english_string]['trad']))
                                {
                                    $new_lang[$english_string]['trad'] = '';
                                    if (!isset($missing_translations_back[$prefix_key]))
                                        $missing_translations_back[$prefix_key] = 1;
                                    else
                                        $missing_translations_back[$prefix_key]++;
                                }
                            }
                            $new_lang[$english_string]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
                        }
                    }
                    if (isset($tabs_array[$prefix_key]))
                        $tabs_array[$prefix_key] = array_merge($tabs_array[$prefix_key], $new_lang);
                    else
                        $tabs_array[$prefix_key] = $new_lang;
                }


        // count will contain the number of expressions of the page
        $count = 0;
        foreach ($tabs_array as $array)
            $count += count($array);

        $this->tpl_view_vars = array_merge($this->tpl_view_vars, array(
            'count' => $count,
            'limit_warning' => $this->displayLimitPostWarning($count),
            'tabsArray' => $tabs_array,
            'missing_translations' => $missing_translations_back
        ));

        // Add js variables needed for autotranslate
        //$this->tpl_view_vars = array_merge($this->tpl_view_vars, $this->initAutoTranslate());

        $this->initToolbar();
        $this->base_tpl_view = 'translation_form.tpl';
        return parent::renderView();
    }
}

