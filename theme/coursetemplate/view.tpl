{include file="header.tpl"}

<div class="group-info">

<ul>
{if $outrec->description}
	<li>{$outrec->description}</li>
{/if}
{if $outprereqsdes}
	<li>{$outprereqsdes}</li>
{/if}
{if $primary_name}
	<li> <b>Primary Focus Area : </b> {$primary_name}</li>
{/if}

</ul>

</div>

{include file="footer.tpl"}
