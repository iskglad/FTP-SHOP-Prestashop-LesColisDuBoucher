<?php
/**
 * Created by PhpStorm.
 * User: gladisk
 * Date: 1/25/15
 * Time: 9:06 PM
 */

class DataSystemFileJet extends AdminController{
    public  $context;
    public  $orders;

    public function __construct($orders){
        $this->context = Context::getContext();
        $this->orders = $orders;

        //set delivery hours
        foreach ($this->orders as &$order){
            $order['hours_array'] = Tools::stringToDeliveryHoursArray($order['hours']);
        }
        parent::__construct();
    }

    public function packages_etiquette(){
        //assign vars
        $this->context->smarty->assign(array(
            'path_css_folder' => _THEME_CSS_DIR_,
            'orders'        => $this->orders
        ));
        $tpl_file = _PS_THEME_DIR_.'etiquettes/jet.tpl';

        $tpl = $this->context->smarty->createTemplate($tpl_file, $this->context->smarty);

        $tpl->display();
        return;
    }
    public function delivery_system_file(){
        //===================================================================================
        //Open File
        //===================================================================================
        $file_path = _PS_ADMIN_DIR_.'/import/';
        //set default filename
        $filename = 'jet_command_file';
        //set correct file name
        if (count($this->orders)){
            foreach ($this->orders as $order){
                if ($order){
                    $filename = date("Ymd", strtotime($order['delivery_date'])).".001";
                    break;
                }
            }

        }
        $myfile = fopen($file_path.$filename, "w") or die("Unable to open file!");


        //===================================================================================
        //Main Loop
        //===================================================================================
        foreach ($this->orders as $order){
            //===================================================================================
            //Init special commandes vars
            //===================================================================================

            $empty_character = " "; //character space
            $cmd_line = str_repeat($empty_character, 1032); //empty string with 1032 char

            //Init special field vars
            //=======================
            //payment
            $payment = "";
            if (!$order['is_payment_done'])
                $payment = 'Paiement a effectuer : '.$order['total_products_wt'].' euros';

            //heure
            if (count($order['hours_array']) == 0){
                  echo "Error: Commande #".$order['id_order']." -> créneau incorrecte";
                  exit;
            }
            $hour_start = $order['hours_array'][0]['from']['hour'].$order['hours_array'][0]['from']['min'];
            $hour_end = $order['hours_array'][0]['to']['hour'].$order['hours_array'][0]['to']['min'];

            //END init special vars
            //=====================


            //===================================================================================
            //Fill Commande Fields
            //===================================================================================
            $fields = array(
                //action
                array(
                    "val"           =>  "C",
                    "max_length"    =>  1,
                    "pos"           =>  1,
                    "end"           =>  1
                ),
                //date AAAMMJJ
                array(
                    "val"           =>  str_replace("-", "", $order['delivery_date']), //AAAA-MM-JJ to AAAAMMJJ
                    "max_length"    =>  8,
                    "pos"           =>  18,
                    "end"           =>  25
                ),
                //Heure HHMM
                array(
                    "val"           =>  $hour_start,
                    "max_length"    =>  4,
                    "pos"           =>  26,
                    "end"           =>  29
                ),
                //Demandeur
                array(
                    "val"           =>  "LCDB",
                    "max_length"    =>  20,
                    "pos"           =>  30,
                    "end"           =>  49
                ),
                //ENL Societe
                array(
                    "val"           =>  "Les colis du boucher",
                    "max_length"    =>  20,
                    "pos"           =>  50,
                    "end"           =>  69
                ),
                //ENL Adresse
                array(
                    "val"           =>  "82 Rue de Rome",
                    "max_length"    =>  40,
                    "pos"           =>  70,
                    "end"           =>  109
                ),
                //ENL Code Postal
                array(
                    "val"           =>  "75008",
                    "max_length"    =>  5,
                    "pos"           =>  110,
                    "end"           =>  114
                ),
                //ENL Ville
                array(
                    "val"           =>  "Paris",
                    "max_length"    =>  60,
                    "pos"           =>  115,
                    "end"           =>  174
                ),
                //LIV Societe
                array(
                    "val"           =>  $order['delivery_first_name'].' '.$order['delivery_last_name'],
                    "max_length"    =>  20,
                    "pos"           =>  235,
                    "end"           =>  354
                ),
                //LIV Adresse
                array(
                    "val"           =>  $order['delivery_address1'],
                    "max_length"    =>  40,
                    "pos"           =>  255,
                    "end"           =>  294
                ),
                //LIV Postal
                array(
                    "val"           =>  $order['delivery_postcode'],
                    "max_length"    =>  5,
                    "pos"           =>  295,
                    "end"           =>  299
                ),
                //LIV Ville
                array(
                    "val"           =>  $order['delivery_city'],
                    "max_length"    =>  60,
                    "pos"           =>  300,
                    "end"           =>  359
                ),
                //LIV Message
                array(
                    "val"           =>  $payment,
                    "max_length"    =>  60,
                    "pos"           =>  360,
                    "end"           =>  419
                ),
                //Code Client ENL
                array(
                    "val"           =>  "LCDB",
                    "max_length"    =>  8,
                    "pos"           =>  721,
                    "end"           =>  728
                ),
                //Type de livraison
                array(
                    "val"           =>  "ME",
                    "max_length"    =>  4,
                    "pos"           =>  748,
                    "end"           =>  751
                ),
                //Heure Fin HHMM
                array(
                    "val"           =>  $hour_end,
                    "max_length"    =>  4,
                    "pos"           =>  792,
                    "end"           =>  795
                ),
                //ENL Telephone
                array(
                    "val"           =>  "0972425166",
                    "max_length"    =>  20,
                    "pos"           =>  813,
                    "end"           =>  832
                ),
                //ENL Code Porte
                array(
                    "val"           =>  "Magazin",
                    "max_length"    =>  20,
                    "pos"           =>  833,
                    "end"           =>  852
                ),
                //ENL Contact
                array(
                    "val"           =>  "Christella",
                    "max_length"    =>  50,
                    "pos"           =>  873,
                    "end"           =>  922
                ),
                //LIV Telephone
                array(
                    "val"           =>  ($order['delivery_phone'])?$order['delivery_phone']:$order['delivery_phone_mobile'],
                    "max_length"    =>  20,
                    "pos"           =>  923,
                    "end"           =>  942
                ),
                //LIV Code porte
                array(
                    "val"           =>  $order['delivery_code'],
                    "max_length"    =>  20,
                    "pos"           =>  943,
                    "end"           =>  962
                ),
                //LIV Contact
                array(
                    "val"           =>  $order['delivery_last_name'],
                    "max_length"    =>  50,
                    "pos"           =>  983,
                    "end"           =>  1032
                )
            );

            //===================================================================================
            //Make Commande line
            //===================================================================================

            foreach ($fields as $field) {
                //get vars
                $info_max_length = $field["max_length"];

                //clean charriotReturn
                $field["val"] = str_replace("<br/>", "-", $field['val']);
                $field["val"] = str_replace("<br>", "-", $field['val']);
                $field["val"] = str_replace("\r\n", "-", $field['val']);
                $field["val"] = str_replace("\n", "-", $field['val']);
                $field["val"] = str_replace("\r", "-", $field['val']);
                $field["val"] = str_replace("\t", "-", $field['val']);

                //replace non-printable character by @
                if (!ctype_print($field["val"])){
                    $field['val'] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '@', $field['val']);
                }
                $val = substr($field["val"], 0, $info_max_length);
                $pos = $field["pos"] - 1;

                //init string
                $info = str_repeat($empty_character, $info_max_length);    //init empty string with max size
                $info = substr_replace($info, $val, 0, strlen($val));       //put val at the begining of the empty string

                //add in command line
                $cmd_line = substr_replace($cmd_line, $info, $pos, $info_max_length);
            }

            //===================================================================================
            //Write commande in file
            //===================================================================================
            fwrite($myfile, $cmd_line."\r\n");
            //fwrite($myfile, "\r\n");
        }

        //===================================================================================
        //Close file
        //===================================================================================

        fclose($myfile);
        //===================================================================================
        //Download file
        //===================================================================================
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Length: ". filesize("$file_path$filename").";");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/octet-stream; ");
        header("Content-Transfer-Encoding: binary");

        readfile($file_path.$filename);
    }
}