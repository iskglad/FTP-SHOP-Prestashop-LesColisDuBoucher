<?php

class AboControllerCore extends FrontController
{

    public $php_self = 'abo';

    public $days = array(
            'lundi' => "monday",
            'mardi' => "tuesday",
            'mercredi' => "wennesday",
            'jeudi' => "thursday",
            'vendredy' => "friday",
            'samedi' => "saturday",
            'dimanche' => "sunday"
        );

    public $position_days = array(
            "1" => "fisrt",
            "2" => "second",
            "3" => "third",
            "4" => "fourth",
    );

    public function __construct(){
        parent::__construct();
    }


    public function setMedia()
    {
        parent::setMedia();

        $this->addJS(_THEME_JS_DIR_.'abo.js');
        $this->addCSS(_THEME_CSS_DIR_.'abo.css');
    }

    public function displayContent()
    {
        parent::displayContent();

        // liste des elements que l'on récupère dynamiquement
        self::$smarty->display(_PS_THEME_DIR_.'abo.tpl');
        //assign Var Abo
        $this->assignAboVar();
    }

    public function postProcess(){
        // Manipulation des variables POST pour abonnement.
        // Stockage en base de donnee des champs abonnnement.
        $this->postProcessAbo();

        // Pour manipuler les autres variables addresses, villes etc...
        // il faut contruire une nouvelle methode
        // Exemple : $this->postProcessAddress()
    }

    public function postProcessAbo(){
        if (Tools::isSubmit('submitAbo')){

            global $cookie;
            $product_type_default = array("colis_sans_port", "colis_sans_agneau", "colis_100_bio", "colis_cuisine_facile");
            $product_type         = array();
            $data                 = array();
            $abo_delay_livration  = _PS_ABO_DELAY_LIVRASION_DAY_;

            if(!$cookie->id_customer){
                 $this->errors[] = Tools::displayError('Customer not define.');
            }

            $customer_id             = (int) $cookie->id_customer;
            $colis_sans_port         = pSQL(Tools::getValue('colis_sans_port'));
            $colis_sans_agneau       = pSQL(Tools::getValue('colis_sans_agneau'));
            $colis_100_bio           = pSQL(Tools::getValue('colis_100_bio'));
            $colis_cuisine_facile    = pSQL(Tools::getValue('colis_cuisine_facile'));
            $day_number              = pSQL(Tools::getValue('day-number'));

            //Fields Required
            $portion                 = pSQL(Tools::getValue('portion'));
            $frequency               = pSQL(Tools::getValue('frequency'));
            $day_name                = pSQL(Tools::getValue('day-name'));
            $payment_mode            = pSQL(Tools::getValue('payment_mode'));

            if(!$portion){
                 $this->errors[] = Tools::displayError('Portion required');
            }

            if(!$frequency){
             $this->errors[] = Tools::displayError('Frequency required');
            }else{
                if ($frequency == "mensuelle") {
                    if(!$day_number){
                        Tools::displayError('Day number required for monthly frequency');
                    }
                }
            }

            if(!$day_name){
                $this->errors[] = Tools::displayError('Day name required');
            }

            if(!$payment_mode){
                $this->errors[] = Tools::displayError('Payment mode required');
            }

            if (!count($this->errors)){

                #clean customers into abo table
                Db::getInstance()->delete('abo', sprintf('customer_id = %s', $customer_id));

                #set customer
                $data['customer_id'] = $customer_id;

                #set product type
                $product_type["colis_sans_port"] = $colis_sans_port ? "colis_sans_port" : "";
                $product_type["colis_sans_agneau"] = $colis_sans_agneau ? "colis_sans_agneau" : "";
                $product_type["colis_100_bio"] = $colis_100_bio ? "colis_100_bio" : "";
                $product_type["colis_cuisine_facile"] = $colis_cuisine_facile ? "colis_cuisine_facile" : "";

                foreach ($product_type as $key => $value) {
                    if(!trim($value)){
                        unset($product_type[$key]);
                    }
                }
                $product_type = implode(",", $product_type);
                $data["product_type"] = !empty($product_type) ? $product_type : implode(",", $product_type_default);

                # set portion
                $data["portion"] = $portion;
                $data["frequency"] = $frequency;
                $data["day_name"] = $day_name;
                $day = $this->days[$day_name];

                // include date
                switch (strtolower($frequency)) {
                    case 'hebdomadaire':
                        // pour le prochaine passage set: date('d-m-Y', strtotime("+1 week"));
                        $data["script_parsing_day"] = date('d-m-Y', strtotime("next $day +1 week"));
                    break;
                    case 'bi-mensuelle':
                        // pour le prochaine passage set: date('d-m-Y', strtotime("+2 week"));
                        $data["script_parsing_day"] = date('d-m-Y', strtotime("next $day +2 week"));
                    break;
                    case 'mensuelle':
                        // pour le prochaine passage set: date('d-m-Y', strtotime("$position $day of next month"));
                        $data["day_number"] = $day_number;
                        $position = $this->position_days[$day_number];
                        $data["script_parsing_day"] = date('d-m-Y', strtotime("$position $day of next month -1 month"));
                    break;
                }

                // set payment mode
                $data["payment_mode"] = $payment_mode;
                Db::getInstance()->insert('abo', $data);
                $this->assignAboVar($data);
            }
        }
    }

    public function assignAboVar($data = NULL){
        global $cookie;
        $customer_id = (int) $cookie->id_customer;

        $default = array(
            "id" => NULL,
            "customer_id" => $customer_id,
            "product_type" => array(),
            "portion" => NULL,
            "frequency" => NULL,
            "payment_mode" => NULL,
            "script_parsing_day" => NULL,
            "day_number" => NULL,
            "day_name" => NULL,
        );

        if(!$data){
            $sql = "SELECT * FROM " ._DB_PREFIX_."abo WHERE customer_id = $customer_id";
            $data = Db::getInstance()->getRow($sql);
        }

        if($data){
            $data['product_type'] = explode("," , $data["product_type"]);
            $this->context->smarty->assign('abo_fields', $data);
        }else{
            $this->context->smarty->assign('abo_fields', $default);
        }
    }

}


