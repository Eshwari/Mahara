{include file="header.tpl"}
{if $header}
<h3 align="center">{$header}</h3>
{/if}  
    		{$createcoursetemplate}

		{if $addlevels}
		{$addlevels}
		{$pagination}
		{/if}

{include file="footer.tpl"}

