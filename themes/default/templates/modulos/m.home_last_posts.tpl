<div>
	{if $psPostSticky}
		<div>
			<div>Posts destacados en {$psConfig.titulo}</div>
			<div>
				<img src="{$psConfig.default}/images/icons/note.png"/>
			</div>
		</div>
		<div>
			<ul>
				{foreach from=$psPostSticky item=p}
				<li>
					<a href="">{$p.post_title}</a>
				</li>
				{/foreach}
			</ul>
		</div>
	{/if}
	
</div>