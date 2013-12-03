{include file="header.tpl"}

<div class="group-info">

<ul>
{if $act_name}
	<li><b>Activity Name:</b>&nbsp;&nbsp;{$act_name}</li>
	<li><b>Description:</b>&nbsp;{$act_desc}</li>
{/if}

{if $outlevelsdes}
	<li>{$outlevelsdes}</li>
{/if}
</ul>

</div>

{include file="footer.tpl"}