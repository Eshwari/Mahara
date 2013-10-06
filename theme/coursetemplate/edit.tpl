{include file="header.tpl"}
{if $header}
<h3 align="center">{$header}</h3>
{/if}  
    		{$createcoursetemplate}

		{if $add_prerequisites}
		{$add_prerequisites}
		{$pagination}
		{/if}

{include file="footer.tpl"}

