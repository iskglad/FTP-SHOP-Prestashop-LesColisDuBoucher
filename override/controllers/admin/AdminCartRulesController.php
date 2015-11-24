<?php

class AdminCartRulesController extends AdminCartRulesControllerCore
{
    public function renderForm()
    {
        $this->context->smarty->assign(
            array(
                'defaultDateShippingFrom' => date('Y-m-d H:00:00'),
                'defaultDateShippingTo' => date('Y-m-d H:00:00', strtotime('+11 month'))
            )
        );
        return parent::renderForm();
    }
}

