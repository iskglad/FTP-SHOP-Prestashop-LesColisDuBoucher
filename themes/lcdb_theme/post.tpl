<div id="columns" class="content clearfix">
	<div id="center_column" class="single">
		
		<div class="big-bloc">
			<h1>Presse</h1>
			<p class="italique">La Presse parle des "Colis du Boucher" !</p>
			{foreach from=$posts item=post}
				<div class="article_presse">
					<img src="{$img_dir}po/{$post.id_post}-default-large_default.jpg" alt="{$post.title}">
					<div class="texte_presse">
						<span>{$post.title}</span>
						<div>{$post.content}</div>
						<a href="{$post.link}">Terra Femina</a>
					</div>
				</div>
			{/foreach}
			<div class="pagination_presse"><a href="#">1</a> <a href="#">2</a> <a href="#">3</a> <a href="#">4</a> <a href="#">5</a> ... <a href="#">10</a> <a href="#">Page suivante</a></div>
			{include file="./pagination.tpl"}
		</div>
	</div><!-- / #center_column -->
</div><!-- / .content -->
