{include file="viewmicroheader.tpl"}

{if $first != $courseoutcome_id}
{if $main_courseoutcome}
	<a class="btn-reply" href="{$WWWROOT}view/listassessments.php?id={$viewid}&courseoutcome={$main_courseoutcome}&first={$first}">{str tag=back}</a>&nbsp;&nbsp;&nbsp;&nbsp;
	{if $first != $main_courseoutcome}

	<a class="btn-reply" href="{$WWWROOT}view/listassessments.php?id={$viewid}&courseoutcome={$first}&first={$first}">{str tag=back} to Main courseoutcomes</a>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
{/if}
{/if}
{if !$subgroup}<a class="btn-reply" href="{$WWWROOT}view/view.php?id={$viewid}">
{else}<a class="btn-reply" href="{$WWWROOT}view/view.php?id={$viewid}&group={$subgroup}">{/if}
{str tag=back} to View Page</a>&nbsp;

{if $maincourseoutcomename}
<h2> {$maincourseoutcomename}</h2>
<h3> {$courseoutcomename}</h3>
{else}
<h2> {$courseoutcomename} </h2>
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
{if $subcourseoutcomes}
<h3 align = "center"> View Sub courseoutcomes and Evaluation </h3>
<br/>
{section name=i loop=$subcourseoutcomes} 
	<a href="{$WWWROOT}view/listsubassessments.php?id={$viewid}&courseoutcome={$subcourseoutcomes[$i].id}&first={$first}&sub={$courseoutcome_id}"> <h6 align="center"> {$subcourseoutcomes[$i].courseoutcome_name} </h6></a>
	<br/>
{/section}
{/if}
{else}
	{if $subcourseoutcomes}
		<h3 align = "center"> View Sub courseoutcomes </h3>
		<br/>
		{section name=i loop=$subcourseoutcomes}
			<a href="{$WWWROOT}view/listassessments.php?id={$viewid}&courseoutcome={$subcourseoutcomes[$i].id}&first={$first}"> <h6 align="center"> {$subcourseoutcomes[$i].courseoutcome_name} </h6> </a>
			<br/>
		{/section}
	{/if}
{/if}

<br/>

{include file="microfooter.tpl"}
