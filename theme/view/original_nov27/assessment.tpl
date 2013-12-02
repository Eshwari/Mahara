{include file="viewmicroheader.tpl"}

{if $first != $outcome_id}
{if $main_outcome}
	<a class="btn-reply" href="{$WWWROOT}view/listassessments.php?id={$viewid}&outcome={$main_outcome}&first={$first}">{str tag=back}</a>&nbsp;&nbsp;&nbsp;&nbsp;
	{if $first != $main_outcome}

	<a class="btn-reply" href="{$WWWROOT}view/listassessments.php?id={$viewid}&outcome={$first}&first={$first}">{str tag=back} to Main Outcomes</a>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
{/if}
{/if}
{if !$subgroup}<a class="btn-reply" href="{$WWWROOT}view/view.php?id={$viewid}">
{else}<a class="btn-reply" href="{$WWWROOT}view/view.php?id={$viewid}&group={$subgroup}">{/if}
{str tag=back} to View Page</a>&nbsp;

{if $mainoutcomename}
<h2> {$mainoutcomename}</h2>
<h3> {$outcomename}</h3>
{else}
<h2> {$outcomename} </h2>
{/if}

{if $hdravail}
<div class="group-info">

{$outheader}
</div>
{/if}
{if $comevaluation}
{if $rubricsfound}
<h3 align="center"> Rubrics </h3>
{/if}
{$comevaluation}
{/if}

{if $evalrequired}
{if $suboutcomes}
<h3 align = "center"> View Sub outcomes and Evaluation </h3>
<br/>
{section name=i loop=$suboutcomes} 
	<a href="{$WWWROOT}view/listsubassessments.php?id={$viewid}&outcome={$suboutcomes[$i].id}&first={$first}&sub={$outcome_id}"> <h6 align="center"> {$suboutcomes[$i].outcome_name} </h6></a>
	<br/>
{/section}
{/if}
{else}
	{if $suboutcomes}
		<h3 align = "center"> View Sub outcomes </h3>
		<br/>
		{section name=i loop=$suboutcomes}
			<a href="{$WWWROOT}view/listassessments.php?id={$viewid}&outcome={$suboutcomes[$i].id}&first={$first}"> <h6 align="center"> {$suboutcomes[$i].outcome_name} </h6> </a>
			<br/>
		{/section}
	{/if}
{/if}

<br/>

{include file="microfooter.tpl"}
