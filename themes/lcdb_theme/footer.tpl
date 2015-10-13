
	{if !$content_only}
			<footer>
				<div class="footer-top">
					{if $page_name=="index"}
						<span class="separation"></span>
					{/if}
					<nav class="more-information">
						<ul>
							<li class="livraison">
								<a href="{$link->getPageLink('Delivery')}" title="En savoir plus sur la livraison réfrigérée">
									<span class="illustration"></span>
									<span class="push">Livraison réfrigérée</span>
									<span class="cta">découvrir</span>
								</a>
							</li>
							<li class="paiement">
								<a href="{$link->getCMSLink(16)}" title="En savoir plus sur le paiement sécurisé">
									<span class="illustration"></span>
									<span class="push">Paiement sécurisé</span>
									<span class="cta">découvrir</span>
								</a>
							</li>
							<li class="sav">
								<a href="{$link->getPageLink('Contact')}" title="En savoir plus sur le SAV">
									<span class="illustration"></span>
									<span class="push">
                                        Contactez-<br/>
                                        nous</span>
									<span class="cta">découvrir</span>
								</a>
							</li>
							<li class="faq">
								<a href="{$link->getCMSCategoryLink(4)}" title="Consulter la foire aux questions">
									<span class="illustration"></span>
									<span class="push">Foire aux questions</span>
									<span class="cta">découvrir</span>
								</a>
							</li>
						</ul>
					</nav>
				</div>
				<div class="footer-bottom">
					{$HOOK_FOOTER}
					<div class="clearfix"></div>
					<p class="copyright"><span>&copy;</span> 2012 - Les Colis du Boucher - Tous droits réservés</p>
					<div class="spe-clearfix-ie7"></div>
				</div>
			</footer>
			<span class="shadow-bottom"></span>
		</div>
	{/if}
	</body>
</html>
