<?php

class AdminCartRulesController extends AdminCartRulesControllerCore
{
    public function renderForm()
    {
        $this->context->smarty->assign(
            array(
                'defaultDateShippingFrom' => date('Y-m-d H:00:00'),
                'defaultDateShippingTo' => date('Y-m-d H:00:00', strtotime('+2 month'))
            )
        );
        return parent::renderForm();
    }
}

