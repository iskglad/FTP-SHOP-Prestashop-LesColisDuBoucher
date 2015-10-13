{*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}{l s='Order confirmation'}{/capture}

<div id="columns" class="content clearfix">
    <div class="bloc-checkout">
        
        {assign var='current_step' value='payment'}
        {include file="$tpl_dir./order-steps.tpl"}
        {include file="$tpl_dir./errors.tpl"}
        <div class="bloc">
            {$HOOK_ORDER_CONFIRMATION}
            {$HOOK_PAYMENT_RETURN}

            <br />
            {if $is_guest}
                <p>{l s='Your order ID is:'} <span class="bold">{$id_order_formatted}</span> . {l s='Your order ID has been sent to you via e-mail.'}</p>
                <a href="{$link->getPageLink('guest-tracking', true, NULL, "id_order={$reference_order}&email={$email}")}" title="{l s='Follow my order'}">{l s='Follow my order'}</a>
            {else}
                <a href="{$link->getPageLink('history', true)}" title="{l s='Back to orders'}" class="green">→&nbsp;<span>{l s='Back to orders'}</span></a>
            {/if}
        </div>
    </div>
</div>
<!-- Google Code for Achat Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1071670646;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "MB4cCPb24V4Q9sqB_wM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript"  src="//www.googleadservices.com/pagead/conversion.js"></script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt=""  src="//www.googleadservices.com/pagead/conversion/1071670646/?label=MB4cCPb24V4Q9sqB_wM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
