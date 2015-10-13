
{$HOOK_RIGHT_COLUMN}

<div class="small-bloc frais-livraison">
	<span class="bloc-title ribbon-frais-livraison"></span>
	<div class="header">
		<p><span class="img-livraison"></span>Frais de livraison de 0 à 25 €</p>
		<hr />
		<p class="livraison-small">Entrez votre code postal pour connaitre vos frais de livraison</p>
	</div>
	
	<form id="form-code-postal" method="post" action="{$link->getPageLink('delivery', true)}">
		<input id="code-postal" type="text" placeholder="Code postal..." name="code_postal">
		<button type="submit" name="bouton_carre">OK</button>
	</form>

	<div class="response">
	</div>
</div>

<div class="small-bloc besoin-aide">
    <span class="bloc-title ribbon-besoin-aide"></span>
    <h3 style="font-size:1.2em;color:#22D700;text-align:center;">Besoin d'aide ?</h3>
    <p style="font-style:italic;text-align:center;"><span style="font-weight:bold;font-size:1.3em;">09 72 42 51 66</span> <br>(appel non surtaxé)</p>
</div>

<div class="small-bloc mot-boucher">
    <span class="bloc-title ribbon-mot-boucher"></span>
    <h3>Le saviez-vous ?</h3>
    <!-- { $right_col.tips[0].content} -->

    <img style="margin-bottom:10px;margin-top:-15px;" src="http://lescolisduboucher.com/themes/lcdb_theme/img/asset/img_solo/labelrouge.png" alt="Label Rouge" />

    <p style="font-style:italic;font-size:0.9em;">Le Bio défend l’élevage le plus respectueux de l’environnement.
        Le Label Rouge garantit un plaisir gustatif fort en sélectionnant les meilleurs produits de nos terroirs, tout en défendant un élevage traditionnel.
        <br><br><a style="font-style:italic;font-size:1em;" href="index.php?id_cms=11&controller=cms">En savoir plus sur les labels</a>
    </p>
</div>
