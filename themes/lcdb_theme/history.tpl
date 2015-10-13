


{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Orders'}{/capture}

<div id="columns" class="content clearfix">
    <div id="left_column">
        {include file="./account-left-col.tpl"}
    </div><!-- / #left_column -->
    <div id="center_column">
        <div class="big-bloc">
            <h1>{l s='Orders'}</h1>
            {include file="$tpl_dir./errors.tpl"}

            {if $slowValidation}
                <p id="empty-command"><span class="img-warning"></span>l s='If you have just placed an order, it may take a few minutes for it to be validated. Please refresh this page if your order is missing.'}</p>
            {/if}

            {if $orders && count($orders)}
            <hr />

            <div class="clearfix" id="mes-commandes">
                <!--Last delivered order-->
                {if count($delivered_orders)}
                    <div class="left-side">
                        <h3>Dernière(s) commande(s)</h3>
                        <hr/>

                        {foreach $delivered_orders as $delivered_order}
                            {if $delivered_order@index < $count_delivered_orders}
                                <div class="order delivered">
                                    <p>
                                        Livraison le : <span class="bold">{$delivered_order.date_delivery|date_format:"%A, %e %B, %Y"|capitalize}</span>
                                        {*{if $delivered_order.hour_delivery}
                                            / <span>{$delivered_order.hour_delivery}</span>
                                        {/if}*}
                                    </p>
                                    <a href="javascript:showOrder(1, {$delivered_order.id_order|intval}, '{$link->getPageLink('order-detail', true)}');" title="Voir le détail">
                                        &rarr;&nbsp;<span>Voir le détail</span>
                                    </a>

                                    {if (isset($delivered_order.invoice) && $delivered_order.invoice && isset($delivered_order.invoice_number) && $delivered_order.invoice_number) && isset($invoiceAllowed) && $invoiceAllowed == true}
                                        <a href="{$link->getPageLink('pdf-invoice', true, NULL, "id_order={$delivered_order.id_order}")}" title="{l s='Invoice'}" class="_blank">
                                            &rarr;&nbsp;<span>Télécharger la facture</span>
                                        </a>
                                    {else}-{/if}
                                </div>
                                <br/>
                            {/if}
                        {/foreach}
                    </div>
                {/if}

                <!--Next order to come-->
                {if count($coming_orders)}
                <div class="right-side">
                    <h3>Prochaine(s) commande(s)</h3>
                    <hr />
                    {foreach $coming_orders as $coming_order}
                        {if $coming_order@index < $count_coming_orders}
                            <!--Order-->
                            <div class="order coming">
                                <p>
                                    Livraison le : <span class="bold">{$coming_order.date_delivery|date_format:"%A, %e %B, %Y"|capitalize}</span>
                                    {*{if $coming_order.hour_delivery}
                                        / <span class="hour_delivery">{$coming_order.hour_delivery}</span>
                                    {/if}*}
                                </p>
                                <a href="javascript:showOrder(1, {$coming_order.id_order|intval}, '{$link->getPageLink('order-detail', true)}');" title="Voir le détail">
                                    &rarr;&nbsp;<span>Voir le détail</span>
                                </a>
                                <!--Display Adjust link if order is not sent yet-->
                                {if $coming_order}
                                    {if isset($coming_order.id_order_state)}
                                        {if $coming_order.isOrderDateAvailableForAdjustment AND
                                        $coming_order.id_order_state != 6 AND
                                        $coming_order.id_order_state != 8}
                                            <!-- != Annulé (id#6) AND != Erreur de paiement (id#8)-->

                                            <a href="{$link->getBaseLink()}index.php?controller=order&adjust={$coming_order.id_order}" title="Ajuster la commande">
                                                &rarr;&nbsp;<span>Ajuster la commande</span>
                                            </a>
                                        {/if}
                                    {else}
                                        Commande non ajustable
                                    {/if}
                                {/if}

                                {if (isset($coming_order.invoice) && $coming_order.invoice && isset($coming_order.invoice_number) && $coming_order.invoice_number) && isset($invoiceAllowed) && $invoiceAllowed == true}
                                    <a href="{$link->getPageLink('pdf-invoice', true, NULL, "id_order={$coming_order.id_order}")}" title="{l s='Invoice'}" class="_blank">&rarr;&nbsp;<span>Télécharger la facture</span></a>
                                {else}-{/if}
                                <br/>
                            </div>
                        {/if}
                    {/foreach}
                    {/if}

                </div>

                <hr class="clear"/>
                <div id="block-order-detail" class="hidden">
                </div>
                {else}
                <p id="empty-command"><span class="img-warning"></span>{l s='You have not placed any orders.'}</p>
                {/if}

            </div>
        </div><!-- / #center_column -->
    </div><!-- / .content -->
