<?php

class AddressController extends AddressControllerCore
{
    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS(_THEME_CSS_DIR_.'address.css');
    }
}

