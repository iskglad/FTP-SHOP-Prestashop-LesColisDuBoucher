<div id="columns" class="content clearfix">
	<div id="left_column">
		{include file="./account-left-col.tpl"}
	</div><!-- / #left_column -->
	<div id="center_column">
		<div class="big-bloc">
			{if !isset($confirmation)}
				<h1>Abonnez-vous !</h1>
				<p>Simplifiez-vous la vie avec nos divers abonnements.</p>
				<div class="clearfix">
					<img src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/abonnement-box.png" alt="abonnement colis du boucher" title="abonnement colis du boucher" id="abonnement-box" />
					<p class="justified">Plus besoin de penser à passer commande, courir au supermarché ou sortir un plat surgelé (sic)…En vous abonnant vous décidez de recevoir régulièrement chez vous les meilleures viandes d’Auvergne, Bio ou Label Rouge. Parmi tous nos différents abonnements, vous pourrez opter pour du sur-mesure !</p>
				</div>
				<hr />
				<form method="post" action="{$link->getPageLink('abo')}" >
					<div class="step clearfix" id="step1">
						<h2 class="green-title"><span class="img-step img-step-1"></span>Composition et nombre de produits</h2>
						<div class="left-side">
							<p class="colis-label">Colis :</p>
							<ul>
								<li>
									<label class="checkbox" for="composition-sans-porc"><input type="checkbox" id="composition-sans-porc" name="colis_sans_port" />sans porc</label>
								</li>
								<li>
									<label class="checkbox" for="composition-sans-agneau"><input type="checkbox" id="composition-sans-agneau" name="colis_sans_agneau" />sans agneau</label>
								</li>
								<li>
									<label class="checkbox" for="composition-bio"><input type="checkbox" id="composition-bio" name="colis_100_bio" />100% BIO</label>
								</li>
								<li>
									<label class="checkbox" for="composition-cuisine-facile"><input type="checkbox" id="composition-cuisine-facile" name="colis_cuisine_facile" />cuisine facile*</label>
								</li>
							</ul>
						</div>
						<div class="right-side">
							<div class="clearfix">
								<label for="nombre-portions">Nombre de portions par livraison**:</label>
								<select id="nombre-portions" name="portion" >
									<option value="undefined" >-</option>
									<option value="12">12</option>
									<option value="18">18</option>
								</select>
							</div>
							<p><span class="bold">Prix unitaire*** TTC</span> de votre colis en abonnement : <span class="price">35</span><span class="euro">&euro;</span></p>
						</div>
						<ul class="notes">
							<li>* Colis composé uniquement de viandes à griller ou à rôtir.</li>
							<li>** 6 portions équivalent à 3 repas pour 2 personnes ou bien 2 repas pour 3 personnes.</li>
							<li>*** Le montant unitaire de votre colis est fixe et calculé en fonction des options cochées et du nombre de portions souhaité.</li>
						</ul>
					</div>
					<hr />
					<div class="step clearfix" id="step2">
						<h2 class="green-title"><span class="img-step img-step-2"></span>Adresse</h2>
						<div class="left-side">
							<ul id="saved-adresse">
								{foreach from=$dlv_adr_fields name=dlv_loop item=field_item}
								{if $field_item eq "company" && isset($address_delivery->company)}
									<li class="address_company">
										{$address_delivery->company|escape:'htmlall':'UTF-8'}
									</li>
								{elseif $field_item eq "address2" && $address_delivery->address2}
									<li class="address_address2">
										{$address_delivery->address2|escape:'htmlall':'UTF-8'}
									</li>
								{elseif $field_item eq "phone_mobile" && $address_delivery->phone_mobile}
									<li class="address_phone_mobile">
										{$address_delivery->phone_mobile|escape:'htmlall':'UTF-8'}
									</li>
								{else}
									{assign var=address_words value=" "|explode:$field_item} 
									<li>
										{foreach from=$address_words item=word_item name="word_loop"}
											{if !$smarty.foreach.word_loop.first} {/if}
											<span class="address_{$word_item|replace:',':''}">
													{$deliveryAddressFormatedValues[$word_item|replace:',':'']|escape:'htmlall':'UTF-8'}
											</span>
										{/foreach}
									</li>
								{/if}
								{/foreach}
							</ul>
							<a href="#" title="modifier votre adresse de livraison" id="modify-address">modifier votre adresse de livraison</a>
						</div>
						<div class="right-side">
							<p class="justified">L'abonnement des Colis du Boucher n'est actuellement disponible que dans certaines villes d'Ile-de-France. En modifiant votre adresse principale vous risquez de ne plus avoir accès à ce service.</p>
							<p>Pour accéder à la liste de toutes les villes bénéficiant de l'abonnement, <a href="#" title="villes bénéficiant de l'abonnement" id="villes">cliquez ici</a>.</p>
							<div id="villes-abonnees">
								<div class="popin">
									<a href="#" title="Fermer" class="popin-close"></a>
									<p>Villes bénéficiant de l'offre "abonnement" des Colis du Boucher :</p>
									<select size="12">
										<option value="">75001, Paris I</option>
										<option value="">75002, Paris II</option>
										<option value="">75003, Paris III</option>
										<option value="">75004, Paris IV</option>
										<option value="">75005, Paris V</option>
										<option value="">75006, Paris VI</option>
										<option value="">75007, Paris VII</option>
										<option value="">75008, Paris VIII</option>
										<option value="">75009, Paris IX</option>
										<option value="">75010, Paris X</option>
										<option value="">75011, Paris XI</option>
										<option value="">75012, Paris XII</option>
										<option value="">75013, Paris XIII</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<hr />
					<div class="step" id="step3">
						<h2 class="green-title"><span class="img-step img-step-3"></span>Fréquence de livraison</h2>
						<div class="clearfix">
							<div class="left-side">
								<p>Fr&eacute;quence :</p>
								<ul class="frequence-list">
									<li>
										<label class="radio" for="hebdomadaire"><input type="radio" name="frequency" id="hebdomadaire" value="hebdomadaire" />hebdomadaire</label>
									</li>
									<li>
										<label class="radio" for="bi-mensuelle"><input type="radio" name="frequency" id="bi-mensuelle" value="bi-mensuelle" />bi-mensuelle</label>
									</li>
									<li>
										<label class="radio" for="mensuelle"><input type="radio" name="frequency" id="mensuelle" value="mensuelle" />mensuelle</label>
									</li>
								</ul>
								<div class="clearfix" id="days-choice">
									<div id="day-name" >
										<select name="day-name">
											<option value="undefined">-</option>
											<option value="mardi">mardi</option>
											<option value="jeudi">jeudi</option>
											<option value="vendredi">vendredi</option>
										</select>
									</div>
									<div id="day-number" >
										<select name="day-number">
											<option value="undefined">-</option>
											<option value="1">1<sup>er</sup></option>
											<option value="2">2<sup>e</sup></option>
											<option value="3">3<sup>e</sup></option>
											<option value="4">4<sup>e</sup></option>
										</select>
									</div>
									<div class="pronom">Le</div>
									<div id="bi-mensuelle-phrase">une semaine sur deux</div>
									<div id="mensuelle-phrase">de chaque mois</div>
								</div>
	
							</div>
							<div class="right-side">
								<p>Merci de préciser le ou les créneau(x) horaires de livraison souhaité(s) :</p>
								<span class="comment">(avec, si possible, au moins un créneau de 2 heures ou plus)</span>
								<ul id="creneau">
									<li id="error" class="hidden"></li>
									<li class="clearfix">
										<label for="entre">Entre :</label>
										<select id="entre" name="expedition_date_1_start">
											<option value="undefined">-</option>
											<option value="7h30">7h30</option>
											<option value="8h00">8h00</option>
											<option value="8h30">8h30</option>
											<option value="9h00">9h00</option>
											<option value="9h30">9h30</option>
											<option value="10h00">10h00</option>
											<option value="10h30">10h30</option>
											<option value="11h00">11h00</option>
											<option value="11h30">11h30</option>
											<option value="12h00">12h00</option>
											<option value="12h30">12h30</option>
											<option value="13h00">13h00</option>
											<option value="13h30">13h30</option>
											<option value="14h00">14h00</option>
											<option value="14h30">14h30</option>
											<option value="15h00">15h00</option>
											<option value="15h30">15h30</option>
											<option value="16h00">16h00</option>
											<option value="16h30">16h30</option>
											<option value="17h00">17h00</option>
											<option value="17h30">17h30</option>
											<option value="18h00">18h00</option>
											<option value="18h30">18h30</option>
											<option value="19h00">19h00</option>
											<option value="19h30">19h30</option>
											<option value="20h00">20h00</option>
											<option value="20h30">20h30</option>
										</select>
										<label for="et">et</label>
										<select id="et" name="expedition_date_1_end">
											<option value="undefined">-</option>
											<option value="7h30">7h30</option>
											<option value="8h00">8h00</option>
											<option value="8h30">8h30</option>
											<option value="9h00">9h00</option>
											<option value="9h30">9h30</option>
											<option value="10h00">10h00</option>
											<option value="10h30">10h30</option>
											<option value="11h00">11h00</option>
											<option value="11h30">11h30</option>
											<option value="12h00">12h00</option>
											<option value="12h30">12h30</option>
											<option value="13h00">13h00</option>
											<option value="13h30">13h30</option>
											<option value="14h00">14h00</option>
											<option value="14h30">14h30</option>
											<option value="15h00">15h00</option>
											<option value="15h30">15h30</option>
											<option value="16h00">16h00</option>
											<option value="16h30">16h30</option>
											<option value="17h00">17h00</option>
											<option value="17h30">17h30</option>
											<option value="18h00">18h00</option>
											<option value="18h30">18h30</option>
											<option value="19h00">19h00</option>
											<option value="19h30">19h30</option>
											<option value="20h00">20h00</option>
											<option value="20h30">20h30</option>
										</select>
									</li>
									<li class="clearfix">
										<label for="ouentre">Ou entre :</label>
										<select id="ouentre" name="expedition_date_2_start">
											<option value="undefined">-</option>
											<option value="7h30">7h30</option>
											<option value="8h00">8h00</option>
											<option value="8h30">8h30</option>
											<option value="9h00">9h00</option>
											<option value="9h30">9h30</option>
											<option value="10h00">10h00</option>
											<option value="10h30">10h30</option>
											<option value="11h00">11h00</option>
											<option value="11h30">11h30</option>
											<option value="12h00">12h00</option>
											<option value="12h30">12h30</option>
											<option value="13h00">13h00</option>
											<option value="13h30">13h30</option>
											<option value="14h00">14h00</option>
											<option value="14h30">14h30</option>
											<option value="15h00">15h00</option>
											<option value="15h30">15h30</option>
											<option value="16h00">16h00</option>
											<option value="16h30">16h30</option>
											<option value="17h00">17h00</option>
											<option value="17h30">17h30</option>
											<option value="18h00">18h00</option>
											<option value="18h30">18h30</option>
											<option value="19h00">19h00</option>
											<option value="19h30">19h30</option>
											<option value="20h00">20h00</option>
											<option value="20h30">20h30</option>
										</select>
										<label for="et2">et</label>
										<select id="et2" name="expedition_date_2_end">
											<option value="undefined">-</option>
											<option value="7h30">7h30</option>
											<option value="8h00">8h00</option>
											<option value="8h30">8h30</option>
											<option value="9h00">9h00</option>
											<option value="9h30">9h30</option>
											<option value="10h00">10h00</option>
											<option value="10h30">10h30</option>
											<option value="11h00">11h00</option>
											<option value="11h30">11h30</option>
											<option value="12h00">12h00</option>
											<option value="12h30">12h30</option>
											<option value="13h00">13h00</option>
											<option value="13h30">13h30</option>
											<option value="14h00">14h00</option>
											<option value="14h30">14h30</option>
											<option value="15h00">15h00</option>
											<option value="15h30">15h30</option>
											<option value="16h00">16h00</option>
											<option value="16h30">16h30</option>
											<option value="17h00">17h00</option>
											<option value="17h30">17h30</option>
											<option value="18h00">18h00</option>
											<option value="18h30">18h30</option>
											<option value="19h00">19h00</option>
											<option value="19h30">19h30</option>
											<option value="20h00">20h00</option>
											<option value="20h30">20h30</option>
										</select>
										<label>(facultatif)</label>
									</li>
									<li><p>Votre boucher est plutôt du matin <img src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/smiley.png" title=":)" alt="smiley content" /></p></li>
								</ul>
							</div>
						</div>
						<p class="step3-tel">Après avoir confirmé votre abonnement en bas de page, nous vous contacterons par téléphone pour voir ensemble les derniers détails liés à vos préférences de livraison.</p>
					</div>
					<hr />
					<div class="step clearfix" id="step4">
						<h2 class="green-title"><span class="img-step img-step-4"></span>Mode de paiement</h2>
						<div class="left-side last-step">
							<p>Paiement :</p>
							<ul>
								<li class="clearfix">
									<label class="radio" for="cheque-espece">
										<input type="radio" name="payment_mode" id="cheque-espece" value="cheque_espece" />
										<span class="bold">par chèque ou espèces</span>, à la réception du colis
									</label>
								</li>
								<li class="clearfix">
									<label class="radio" for="virement">
										<input type="radio" name="payment_mode" id="virement" value="virement" />
										<span class="bold">par virement</span>, paiement de l'ensemble des colis en fin de mois
									</label>
								</li>
								<li class="clearfix">
									<label class="radio" for="cb">
										<input type="radio" name="payment_mode" id="cb" value="cb" />
										<span class="bold">par carte bancaire</span> (en ligne), après réception de l'email vous indiquant que votre commande 
										a bien été passée auprès des éleveurs
									</label>
								</li>
							</ul>
						</div>
					</div>
					<hr />
					<input type="submit" id="abonnement-submit" class="red-button gradient" value="JE M'ABONNE !" />
					<p>Une fois abonné, vous pourrez à tout moment modifier votre abonnement depuis votre espace membre, rubrique "Mon abonnement".</p>
				</form>
			{else}
				<p>Merci, votre abonnement à été pris en compte !</p>
			{/if}
		</div>
	</div><!-- / #center_column -->
</div><!-- / .content -->