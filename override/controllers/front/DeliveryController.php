<?php

class DeliveryControllerCore extends FrontController
{
    public $php_self = 'delivery';

    public function setMedia()
    {
        parent::setMedia();

        $this->addJS(_THEME_JS_DIR_.'delivery.js');
        $this->addCSS(_THEME_CSS_DIR_.'delivery.css');
        $this->addJS('http://maps.googleapis.com/maps/api/js?key=AIzaSyDvWSB_8JhCl-0moGJVn2iMPt8-9xlP2r8&amp;sensor=true');
        $this->addJS(_THEME_JS_DIR_.'plugins/infobox_packed.js');
        $this->addJS(_THEME_JS_DIR_.'tools.js');
        $this->addJS(_THEME_JS_DIR_.'relayhome.js');
    }
    protected function _assignRelays()
    {
        $relays = Order::getRelays();
        // $vars[0]['name'] = str_replace(' ', '_', strtolower($vars[0]['name']));
        $this->context->smarty->assign(
            array(
                'relays' => json_encode($relays),
                'ID_RELAY_CARRIER' => ID_RELAY_CARRIER
            )
        );
    }
    public function initContent()
    {
        parent::initContent();
        $this->postProcess();
        $this->_assignRelays();
        $this->setTemplate(_PS_THEME_DIR_.'delivery.tpl');
    }

    public function postProcess()
    {
        //init deliery var
        $delivery['error'] = '';
        $id_zone = 0;
        $postcode = Tools::getValue("code_postal");

        //Manage postcode format error
        if($postcode){
            if (strlen($postcode) != 5 || !is_numeric($postcode)){
                //this ENUM will display a "bad format error" text in delivery.tpl line 26
                $delivery['error'] = 'bad_postcode_format';
            }
        }

        //get idZone
            //Right block in "produit a la carte" process
        if (Tools::isSubmit('bouton_carre') || $this->ajax) {
            if ($postcode && !$delivery['error'])
                $id_zone = Address::getZoneByZipCode($postcode);
        }

        //get Zone object
        $zone = New Zone($id_zone);

        $delivery['zone'] = $id_zone;

        if($id_zone == ID_ZONE_JET){
            // paris

            //get ecolo free shipping (to manage "livraison Jet soir" special case)
            $ecolo_zone = new Zone(ID_ZONE_ECOLOTRANS);

            $delivery['minimum_order'] = $zone->minimum_order."€";
            $delivery['infos'] = array(
                //Journée
                array(
                    "price" => $zone->minimum_order."€ à ".$zone->free_shipping."€",
                    "ship" => "Journée 5€"
                ),
                //Journée free shipping
                array(
                    "price" => "> ".$zone->free_shipping."€ ou <br>point relais",
                    "ship" => ($this->ajax) ? "<b class='rouge'>Offerte</b>" : "Offerts"
                ),
                //Soir (ecolo)
                array(
                    "price" => $zone->minimum_order."€ à ".$ecolo_zone->free_shipping."€",
                    "ship" => "Soir 14€"
                ),
                //Soir (ecolo) free shipping
                array(
                    "price" =>"> ".$ecolo_zone->free_shipping."€",
                    "ship" => ($this->ajax) ? "<b class='rouge'>Offerte</b>" : "Offerts"
                )
            );
            $delivery['content'] = "<br><p class='titre_vert_2' style='margin-top:10px;'>Pour une livraison à domicile ou au bureau</p><p>Quoi de mieux que de se faire livrer chez soi, directement
				d'Auvergne dans son frigo? <strong>Nous livrons à domicile ou au bureau de 8h30 à 22h</strong>, dans un créneau horaire  d'une heure minimum que vous
				nous communiquez lors de la commande.</p><p class='titre_vert_2'>Pour une livraison en 
				Point Relais</p><p>Les Colis du Boucher veulent vous offrir le maximum de service et de souplesse. Pour cela nous vous proposons une livraison à domicile, 
				à votre bureau ou en Point Relais.<br><br>Le Point Relais donne l'avantage des horaires beaucoup plus souples. <strong>Vous récupérez votre Colis quand 
				cela vous arrange à partir de 12h</strong>. Les horaires de retrait de colis  varient selon le point relais.</p>
				<div class='lien_vert italique'>
					<a href='#' class='decortvert2' title='Voir la carte des points relais' id='show-map'><span>Voir la carte des points relais</span></a>
				</div>
				<div id='relays'>
					<div class='popin'>
						<a href='#' title='Fermer' class='popin-close'></a>
						<p class='popin-title'>Points relais</p>
						<div class='clearfix content-wrapper'>
							<div id='left-side'><ul id='relay-list'></ul></div>
							<div id='right-side'><div id='map'></div></div>
						</div>
					</div>
				</div>";
        }elseif($id_zone == ID_ZONE_ECOLOTRANS){
            // proche banlieue
            $delivery['minimum_order'] = $zone->minimum_order."€";
            $delivery['infos'] = array(
                array(
                    "price" => $zone->minimum_order."€ à ".$zone->free_shipping."€",
                    "ship" => "14€"
                ),array(
                    "price" =>"> ".$zone->free_shipping."€",
                    "ship" => ($this->ajax) ? "<b class='rouge'>Offerte</b>" : "Offerts"
                )
            );
            $delivery['content'] = "<br><p class='titre_vert_2' style='margin-top:10px;'>Pour une livraison à domicile ou au bureau</p><p>Quoi de mieux que de se faire livrer chez soi,
				 directement d'Auvergne dans son frigo? <strong>Nous livrons à domicile ou au bureau de 15h à 22h</strong>, dans un créneau horaire de "."2h"." minimum
				 que vous nous communiquez lors de la commande.</p";
        }elseif($id_zone == ID_ZONE_UPS){
            // province
            $delivery['minimum_order'] = $zone->minimum_order."€";
            $delivery['infos'] = array(
                //Saver
                array(
                    "price" => $zone->minimum_order."€ à ".$zone->free_shipping."€",
                    "ship" => "Journée 14€"
                ),
                //Saver free shipping
                array(
                    "price" =>"> ".$zone->free_shipping."€",
                    "ship" => ($this->ajax) ? "<b class='rouge'>Offerte</b>" : "Offerts"
                ),
                //Express
                array(
                    "price" => $zone->minimum_order."€ à ".DELIVERY_FREE_SHIPPING_UPS_EXPRESS."€",
                    "ship" => "Matin 25€"
                ),
                //Express free shipping
                array(
                    "price" =>"> ".DELIVERY_FREE_SHIPPING_UPS_EXPRESS."€",
                    "ship" => ($this->ajax) ? "<b class='rouge'>Offerte</b>" : "Offerts"
                )
            );
            $delivery['content'] = "<br><p class='titre_vert_2'>Pour une livraison à domicile ou au bureau</p><p>Parlez des Colis du Boucher à vous voisins ou au bureau
				et économisez les frais de livraison. En commandant à plusieurs pour le même jour et à la même adresse de livraison vous pourrez ainsi
				plus facilement faire baisser les frais de livraison, voire les annuler complètement.</p>";
        }

        $delivery['more'] = '<span class="lien_vert italique"><a href="'._PS_BASE_URL_.__PS_BASE_URI__.'index.php?id_cms_category=4&controller=cms">En savoir plus sur la livraison</a></span>';

        if($this->ajax){
            echo json_encode($delivery);
            exit();
        }else{
            if ($postcode)
                $this->context->smarty->assign('delivery', $delivery);
        }
    }
}

