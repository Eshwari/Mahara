{include file="header.tpl"}
{if $header}
<h3 align="center">{$header}</h3>
{/if}  
    		{$createcoursetemplate}

		{if $addprerequisites}
		{$addprerequisites}
		{$pagination}
		{/if}

{include file="footer.tpl"}

