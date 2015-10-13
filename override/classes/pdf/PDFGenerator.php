<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/



/**
 * @since 1.5
 */
class PDFGenerator extends PDFGeneratorCore
{
    // Tableau simple
function BasicTable($header, $data)
{
    // En-tête
    $i=0;
    foreach($header as $col){
        if($i==1 || $i==4 || $i==3)
        $this->Cell(40,7,$col,1);
        else
            $this->Cell(40,7,$col,1);
        $i++;
    }
//    $this->Ln();
    
    // Données
    foreach($data as $row)
    {
        $i=0;
        foreach($row as $col){
            if($i==1 || $i==4 || $i==3)
            $this->Cell(40,6,$col,1);
            else
                $this->Cell(40,6,$col,1);
            $i++;
        }
        $this->Ln();
    }
}



}
