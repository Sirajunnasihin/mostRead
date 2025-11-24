{**
 * plugins/blocks/mostRead/block.tpl
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Most read articles block plugin
 *}
<div class="pkp_block block_most_read">
	<h2 class="title">{$blockTitle|escape}</h2>
	<div class="content">
		{if $mostReadArticles && count($mostReadArticles) > 0}
			<ul>
				{foreach from=$mostReadArticles item=article}
					<li>
						<a href="{$article.url}">
							{$article.title|strip_unsafe_html}
						</a>
						<div class="views">
							<i class="fa fa-eye" aria-hidden="true"></i>
							<span class="view-count">{$article.views|escape}</span>
						</div>
					</li>
				{/foreach}
			</ul>
		{else}
			<p>{translate key="plugins.blocks.mostRead.noArticles"}</p>
		{/if}
	</div>
</div>

<style>
.block_most_read ul {
	list-style: none;
	padding: 0;
	margin: 0;
}

.block_most_read li {
	margin-bottom: 1em;
	padding-bottom: 0.5em;
	border-bottom: 1px solid #ddd;
}

.block_most_read li:last-child {
	border-bottom: none;
}

.block_most_read .views {
	margin-top: 0.25em;
	font-size: 0.9em;
	color: #666;
}

.block_most_read .view-count {
	margin-left: 0.25em;
}
</style>