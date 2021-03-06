<?php

class AboCronControllerCore extends FrontController
{

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

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }

    public function displayContent(){
        global $cookie;

        // Recupeation de tous les abonnes pour le prelevement d'aujourd'hui
        $sql = sprintf("SELECT * FROM %sabo WHERE script_parsing_day = '%s'", _DB_PREFIX_, date('Y-m-d'));
        $results = Db::getInstance()->ExecuteS($sql);

        if(!empty($results)){

            foreach ($results as $result) {

                // On recupera les types de produit au quel il veut recevoir
                $product_type = explode(",", $result["product_type"]);

                // Recuperation du client actuel et Creation de la commande par default
                $customer = new Customer($result["customer_id"]);
                $order = new Order();

                if (!Validate::isLoadedObject($customer))throw new PrestaShopException('Can\'t load Customer object');

                //Nous recuperons l'address actuelle pour informer directement ID sur la nouvelle commande
                $sql_address = sprintf("SELECT id_address FROM %saddress WHERE id_customer = %s", _DB_PREFIX_, $customer->id);
                $row_address = Db::getInstance()->getRow($sql_address);
                $id_address = !empty($row_address["id_address"]) ? $row_address["id_address"] : 1;

                //On recupere aussi le type de payement pour injecter
                if($result["payment_mode"] == "vir") $order->payment = "Virement bancaire";
                if($result["payment_mode"] == "che") $order->payment = "Chèque ou Espèces";
                if($result["payment_mode"] == "cat") $order->payment = "Carte bancaire";

                // On popule la commande avec les valeurs necessaires
                $order->id_customer = $customer->id; // dynamics
                $order->id_carrier = defined('PS_CARRIER_DEFAULT') ? PS_CARRIER_DEFAULT : 1; // dynamics
                $order->id_lang = $cookie->id_lang;
                $order->id_address_delivery = $id_address; // dynamics
                $order->id_shop = $customer->id_shop;
                $order->id_shop_group = $customer->id_shop_group;
                $order->id_currency = $cookie->id_currency;
                $order->id_address_invoice = $id_address; // dynamics
                $order->total_paid = "0.00";
                $order->total_paid_real = "0.00";
                $order->total_products = "0";
                $order->total_products_wt = "0.00";
                $order->conversion_rate = "0.00";

                // Creation d'un panier pour la commande en cours
                $cart = new Cart();
                $cart->id_shop_group = $order->id_shop_group;
                $cart->id_shop = $order->id_shop;
                $cart->id_customer = $order->id_customer;
                $cart->id_carrier = $order->id_carrier;
                $cart->id_address_delivery = $order->id_address_delivery;
                $cart->id_address_invoice = $order->id_address_invoice;
                $cart->id_currency = $order->id_currency;
                $cart->id_lang = $order->id_lang;
                $cart->secure_key = $order->secure_key;
                $cart->add();

                //Ajout du panier a la commande et sauvegarde de la commande
                $order->id_cart = $cart->id_cart;

                // Nous stock les details de la commande en memoire, car si le total ne conrespond pas
                // a la demande, nous ne sauvegardons pas la commande
                $order_detail_memory = array();

                //Cette variable sert a verifie si la commande peut etre sauvegarder
                $is_ok = true;

                // recuperation de la portion voulue pour le client.
                $portion = $result["portion"];

                // la proportionnal sert pour la portion de 18 avec 4 part
                $proportional = true;

                // on applique les portions par part
                foreach ($product_type as $type) {
                    if($portion == 12){
                        $portion_total[$type] = 12 / count($product_type);
                    }

                    //si plus 4, il y a plus de proportion donct nous partageons en 4 (16) et le reste est pour les suivent
                    if($portion == 18 && count($product_type) <= 3){
                        if(count($product_type) <= 3){
                            $portion_total[$type] = 18 / count($product_type);
                        }
                    }else{
                        $proportional = false;
                        $portion_total[$type] = 4;
                    }
                }

                //Application de la proportion en mode portion 18 avec 4 parts
                if(!$proportional){
                    $portion_total_first = $portion_total;
                    // nous mettons le pointeur sur le premier element et on ajoute
                    reset($portion_total_first);
                    $first_key = key($portion_total_first);
                    $portion_total[$first_key] += 1;

                    //On ajoute sur le deuxieme element aussi
                    next($portion_total_first);
                    $seconde_key = key($portion_total_first);
                    $portion_total[$seconde_key] += 1;
                }

                // Nous recuperons tous les produits pour appliquer directement les parts actuelles
                $sql_products = sprintf("SELECT id_product FROM %sproduct", _DB_PREFIX_);
                $results_products = Db::getInstance()->ExecuteS($sql_products);

                foreach ($results_products as $results_product) {

                    // products
                    $product = new Product((int) $results_product["id_product"]);

                    // recuperation des deux attributs type et portion du produit
                    $product_type_row = $product["attributes"]["type"];
                    $product_portion_row = $product["attributes"]["portion"];

                    //on regarde si colis du produit conrespond bien aux colis demander par le client
                    if(in_array($product_type_row, $product_type)){

                        // Check si les colis sont servit pour ce type de portion
                        // et que il y a suffisament de portions dans le produit afin servir la commande
                        if($portion_total[$product_type_row] > 0 && ((int)$portion_total[$product_type_row] - (int)$product_portion_row >= 0)){

                            //orders details
                            $order_detail = new OrderDetail();
                            $order_detail->id_order = $order->id;
                            $order_detail->id_warehouse = 0;
                            $order_detail->id_shop = $customer->id_shop;

                            $order_detail->product_name = !empty($product->name[0]) ? $product->name[0] : $product->name[1];
                            $order_detail->product_quantity = 1; // important

                            $order_detail->product_price = $product->price;

                            //stockage en memoire
                            $order_detail_memory[] = $order_detail;

                            // vue que la demande de portion est faites on soustrait de la demainde
                            // On control le cas de figure superieur ou egal
                            if((int) $product_portion_row >= (int) $portion_total[$product_type_row]){
                                $portion_total[$product_type_row] = 0;
                            }else{
                                $portion_total[$product_type_row] -= $product_portion_row;
                            }

                        }

                    }
                }

                // Cette loup check si tous les parts de portions ont ete servir
                foreach ($portion_total as $portion_current) {
                    if($portion_current != 0){
                        $is_ok = false;
                    }
                }

                //Si les portions sont correct nous sauvegardons la commande et les details de la commande
                if($is_ok){
                    $order->save();
                    foreach ($order_detail_memory as $order_detail_m) {
                        $order_detail_m->save();
                    }
                }
                //Mise a jour du script day pour le prochain passage
                $this->updateCustomerAbo($result);
            }
        }
        exit();
    }

    public function updateCustomerAbo($data){
        // mise a jour du script d'abonnement
        $customer_id = $data['customer_id'];
        $day = $this->days[$data["day_name"]];

        // include date
        switch (strtolower($data["frequency"])) {
            case 'hebdomadaire':
                // pour le prochaine passage set: date('d-m-Y', strtotime("+1 week"));
                $data["script_parsing_day"] = date('d-m-Y', strtotime("+1 week"));
            break;
            case 'bi-mensuelle':
                // pour le prochaine passage set: date('d-m-Y', strtotime("+2 week"));
                $data["script_parsing_day"] = date('d-m-Y', strtotime("+2 week"));
            break;
            case 'mensuelle':
                // pour le prochaine passage set: date('d-m-Y', strtotime("$position $day of next month"));
                $position = $this->position_days[$data["day_number"]];
                $data["script_parsing_day"] = date('d-m-Y', strtotime("$position $day of next month"));
            break;
        }

        Db::getInstance()->update('abo', $data, "customer_id = $customer_id");
    }

}