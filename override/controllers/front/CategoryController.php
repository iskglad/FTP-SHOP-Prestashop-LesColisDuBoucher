<?php

class CategoryController extends CategoryControllerCore
{
	public function init()
	{
		// Get category ID
		$id_category = (int)Tools::getValue('id_category');
		
		// redirect category
		switch ($id_category) {
            case ID_CATEGORY_MAIN:
                if (Product::shopHasAvailablePromoProducts())
                    //Tools::redirect('index.php?id_category='.ID_CATEGORY_PROMOTIONS.'&controller=category');
					Tools::redirect(ID_CATEGORY_REPAS_DE_FETE.'-repas-de-fetes');
                else
                    Tools::redirect('index.php?id_category='.ID_CATEGORY_BOEUF.'&controller=category');
                break;
			case ID_CATEGORY_SURPRISE:
				Tools::redirect('index.php?id_product='.ID_PRODUCT_SURPRISE.'&controller=product');
				break;
			case ID_CATEGORY_GIFT:
				Tools::redirect('index.php?id_product='.ID_PRODUCT_GIFT.'&controller=product');
				break;
			case ID_CATEGORY_MATUREES:
				Tools::redirect(ID_CATEGORY_YMLB.'-le-bourdonnec');
				break;
			case ID_CATEGORY_SPECIAL_FETE:
				Tools::redirect(ID_CATEGORY_REPAS_DE_FETE.'-repas-de-fetes');
				break;
			default: 
				parent::init();
				break;
		}
		
	}
	
	public function initContent()
	{
		parent::initContent();
        //add JS
        $this->addJS(_THEME_JS_DIR_.'jquery.easing.1.3.js');
        $this->addJqueryUI('ui.accordion');
        $this->addJS(_THEME_JS_DIR_.'product-list.js');

		$zipcodes = array();

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


        $this->context->smarty->assign(array(
            'left_col' => $leftcol,
            'right_col' => $rightcol
        ));

        //get all promotions product
        if ((int)Tools::getValue('id_category') == ID_CATEGORY_PROMOTIONS){
            $orderBy = Tools::getProductsOrder('by', Tools::getValue('orderby'));
            $orderWay = Tools::getProductsOrder('way', Tools::getValue('orderway'));
            $all_products = Product::getProducts((int)Context::getContext()->language->id, 0, 0, $orderBy, $orderWay, ID_CATEGORY_MAIN, true);
            $promo_products = array();
            foreach ($all_products as $product){
                if (Product::combinationListHasPromo($product['combinations'])){
                        $product['link'] = $this->context->link->getProductLink($product['id_product']);
                        $promo_products[] = $product;
				}
            }

            $this->context->smarty->assign(array(
                'promo_products' => $promo_products,
                'promoFilter'   => true
            ));
        }
		//get ymlb product
		if ((int)Tools::getValue('id_category') == ID_CATEGORY_YMLB){
            $orderBy = Tools::getProductsOrder('by', Tools::getValue('orderby'));
            $orderWay = Tools::getProductsOrder('way', Tools::getValue('orderway'));
            $products = Product::getProducts((int)Context::getContext()->language->id, 0, 0, $orderBy, $orderWay, ID_CATEGORY_YMLB, true);
			$ymlb_products = array();
            foreach ($products as $product){
                        $product['link'] = $this->context->link->getProductLink($product['id_product']);
                        $ymlb_products[] = $product;
            }
			
            $this->context->smarty->assign(array(
                'ymlb_products' => $ymlb_products,
            ));
        }
	}

	protected function assignSubcategories()
	{
        $this->orderBy = 'name';

        $subCategories = $this->category->getFullSubCategories($this->context->language->id, true, $this->orderBy, $this->orderWay);
        if ($subCategories){
			$this->context->smarty->assign(array(
				'subcategories' => $subCategories,
				'subcategories_nb_total' => count($subCategories),
				'subcategories_nb_half' => ceil(count($subCategories) / 2)
			));
		}
	}
}

