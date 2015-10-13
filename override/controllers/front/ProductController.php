<?php

class ProductController extends ProductControllerCore
{
	public function initContent()
	{
		parent::initContent();

		$now = date("Y-m-d h:i:s");
        $zipcodes = array();

		if(!(($now >= $this->product->date_start)&&($now <= $this->product->date_end))){
			//echo "product not available";
		}

		// get zipcodes list
		if ($this->context->customer->id){
			$customer_adresses = $this->context->customer->getAddresses($this->context->language->id);
			foreach ($customer_adresses as $key => $address) {
				if(isset($address["postcode"]) && ($address["postcode"] != "")){
					$zipcodes[] = $address["postcode"];
				}
			}
		}
		
		$leftcol = Category::getLeftColumn($this->context->language->id, $zipcodes);
		$rightcol = Category::getRightColumn($this->context->language->id);

		$categories = Product::getProductCategoriesFull($this->product->id);

        //Tools::testVar($this->product);

        foreach ($this->product->combinations as &$comb){
            $comb['price_impact'] = $comb['price'] * (1 + $this->product->tax_rate/100);
        }

        if ($this->product->id == ID_PRODUCT_SURPRISE)
            $this->addJS(_THEME_JS_DIR_.'colis_surprise/display_colis_price.js');

        $this->context->smarty->assign(array(
            'left_col' => $leftcol,
            'right_col' => $rightcol,
            'recipes' => $this->product->getRecipes($this->context->language->id),
            'product_categories' => $categories,
            'id_product_surprise' => ID_PRODUCT_SURPRISE,
            'id_product_gift' => ID_PRODUCT_GIFT
        ));
		
	}
}

