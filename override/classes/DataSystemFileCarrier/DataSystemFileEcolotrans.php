<?php
/**
 * Created by PhpStorm.
 * User: gladisk
 * Date: 1/25/15
 * Time: 9:06 PM
 */

Class DataSystemFileEcolotrans extends AdminController{
    public  $context;
    public  $orders;

    public function __construct($orders){
        $this->context = Context::getContext();
        $this->orders = $orders;

        $this->setDeliveryHours();
        parent::__construct();
    }

    public function setDeliveryHours(){
        foreach ($this->orders as &$order){
            $order['hours_array'] = Tools::stringToDeliveryHoursArray($order['hours']);
            if (count($order['hours_array']) == 0){
                die("Error: Order #".$order['id_order'].' -> bad hours format (I.e: "Entre AAhBB et XXhYY")');
            }
        }
    }
    public function packages_etiquette(){
        //assign vars
        $this->context->smarty->assign(array(
            'path_css_folder' => _THEME_CSS_DIR_,
            'path_img_folder' => _THEME_IMG_DIR_,
            'orders'        => $this->orders
        ));
        $tpl_file = _PS_THEME_DIR_.'etiquettes/ecolotrans.tpl';

        $tpl = $this->context->smarty->createTemplate($tpl_file, $this->context->smarty);

        $tpl->display();
        return;
    }
    public function delivery_csv(){
        //column header
        $data = array(
            array(
                "Code Magasin",
                "N° de commande",
                "Nom du client",
                "Adresse 1/3",
                "Adresse 2/3",
                "Adresse 3/3",
                "Code d'accès",
                "Ascenseur (O/N)",
                "Code postal",
                "Ville",
                "Téléphone Portable",
                "Téléphone Fixe",
                "Téléphone Bureau",
                "Adresse mail",
                "Prestation",
                "Type prestation (00/10)",
                "Montage (O/N)",
                "Poids (kg)",
                "Volume(m3)",
                "CRT (?)",
                "Date de livraison  souhaitée",
                "Créneau horaire souhaité",
                "Référence Produit",
                "Designation produit",
                "Quantité",
                "Unité de manutention",
                "Nombre d'unités"
            )
        );
        foreach ($this->orders as $order){
            //set creneau
            $creneau_from= "-";
            $creneau_to= "-";
            if (count($order['hours_array'])){
                $creneau_from = $order['hours_array'][0]['from']['hour'].$order['hours_array'][0]['from']['min'];
                $creneau_to = $order['hours_array'][0]['to']['hour'].$order['hours_array'][0]['to']['min'];
            }

            //csv header
            $row = array(
                "LCDB",
                //Numero de commande
                $order['id_order'],
                //Nom du client
                $order['delivery_first_name'].' '.strtoupper($order['delivery_last_name']),
                //Adresse 1/3
                $order["delivery_address1"],
                //Adresse 2/3
                $order["delivery_address2"],
                //Adresse 3/3
                "",
                //Code d'acces
                $order["delivery_code"],
                //Assenceur O/N
                "",
                //Code postal
                $order["delivery_postcode"],
                //Ville
                $order["delivery_city"],
                //Telephone Portable
                $order["delivery_phone_mobile"],
                //Telephone fixe
                $order["delivery_phone"],
                //Telephone bureau
                "",
                //Adresse email
                $order["client_email"],
                //Prestation
                "LV1E",
                //Type de prestation 0/10
                "0",
                //Montage
                "",
                //Poids
                $order['package_weight'],
                //Volume
                "",
                //CRT (euros)
                "0",
                //Date de livraison souhaité
                date("d/m/Y", strtotime($order["delivery_date"])),
                //Créneau horraire souhaité
                $creneau_from.$creneau_to,
                //Reference produit (default null)
                "",
                //Désignation du produit
                "Les Colis du Boucher",
                //Quantité
                "1",
                //Unité de manutention
                "Colis",
                //Nombre d'unité
                "1"
            );
            $data[] = $row;
        }
        //Tools::testVar($data);
        //download
        Tools::downloadCsv($data);
    }
}