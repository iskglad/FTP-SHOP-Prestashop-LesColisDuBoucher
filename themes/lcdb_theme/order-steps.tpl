
{* Assign a value to 'current_step' to display current style *}
{capture name="url_back"}
{if isset($back) && $back}back={$back}{/if}
{/capture}

{if !isset($multi_shipping)}
	{assign var='multi_shipping' value='0'}
{/if}

<div id="breadcrumb-checkout">
	<ol>
		<li class="first identification {if $current_step=='login'}item-active step_current{else}{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address'}step_done{else}step_todo{/if}{/if}">
			<a href="#">Identification</a>
			<span class="right-triangle"></span>
		</li>
		
		<li class="adresse-livraison {if $current_step=='address'}item-active step_current{else}{if $current_step=='payment' || $current_step=='shipping'}step_done{else}step_todo{/if}{/if}">
			<a href="#" title="Adresse et service de livraison">Adresse de livraison</a>
			<span class="right-triangle"></span>
		</li>
		
		<li class="date-livraison {if $current_step=='shipping'}item-active step_current{else}{if $current_step=='payment'}step_done{else}step_todo{/if}{/if}">
			<a href="#" title="Date de livraison">Date de livraison</a>
			<span class="right-triangle"></span>
		</li>
		
		<li class="last paiement {if $current_step=='payment'}item-active step_current_end{else}step_todo{/if}">
			<a href="#" title="Paiement">Paiement</a>
		</li>
	</ol>
</div>