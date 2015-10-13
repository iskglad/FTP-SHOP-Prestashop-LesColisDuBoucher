
{capture name=path}{l s='My account'}{/capture}

<div id="columns" class="content clearfix">
	<div id="left_column">
		{include file="./account-left-col.tpl"}
	</div><!-- / #left_column -->
	<div id="center_column">
		<div class="big-bloc">
			<h1>{l s='My account'}</h1>
			{if isset($account_created)}
				<p class="success">
					{l s='Your account has been created.'}
				</p>
			{/if}
			<hr class="mon-compte"/>
			<p>{l s='Welcome to your account page. You can manage your personal information, your order and your delivery address.'}</p>
			<hr />
		</div>
	</div><!-- / #center_column -->
</div><!-- / .content -->