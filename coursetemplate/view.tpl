{include file="header.tpl"}

<div class="group-info">

<ul>
{if $outrec->description}
	<ul><b>Description: </b>{$outrec->description}</ul>
{/if}
{if $outrec->college_offering}
	<ul><b>Collge Offering: </b>{$outrec->college_offering}</ul>
{/if}
{if $outrec->dept_offering}
	<li><b>Department: </b>{$outrec->dept_offering}</li>
{/if}
{if $outrec->degree_id}
	<li><b>Degree </b>{$outrec->degree_id}</li>
{/if}
{if $outprereqsdes}
	<li>{$outprereqsdes}</li>
{/if}


</ul>

</div>

{include file="footer.tpl"}
