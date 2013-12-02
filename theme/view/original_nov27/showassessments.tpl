{include file="viewmicroheader.tpl"}

{if $first == $outcome_id}
{if !$subgroup}<a class="btn-reply" href="{$WWWROOT}view/view.php?id={$viewid}">
{else}<a class="btn-reply" href="{$WWWROOT}view/view.php?id={$viewid}&group={$subgroup}">{/if}
{str tag=back}</a>&nbsp;
{else}
{if $main_outcome}
	<a class="btn-reply" href="{$WWWROOT}view/showassessments.php?id={$viewid}&outcome={$main_outcome}&first={$first}">{str tag=back}</a>&nbsp;
{/if}
{/if}

<h2 align='center'> {$outname} </h2>
{if $comm}
{foreach from=$committee item=eachmember}
<h3> <b> Commitee Member : </b> {$eachmember->username} </h3>
 {$mem = $eachmember->member}
 {$fincomm = $finalcomm.$mem}
{if $comm.$mem}
<table class="fullwidth listing">
 <th class="{cycle values='r0'}">
  <tr>
   <td><b>Rubric</b></td>
   <td><b>Assigned Level</b></td>
   <td><b>Comments</b></td>
  </tr>
 </th>
 
  {foreach from=$comm.$mem item=eachcomm}
   <tr class="{cycle values='r0,r1'}">
    <td>{$eachcomm->rubric_name}</td>
	<td>{$eachcomm->level_assigned}</td>
    <td>{$eachcomm->comments}</td>
   </tr>
  {/foreach}
  {if $fincomm}
	<tr class="{cycle values='r0,r1'}">
		{if $evaltype == "Level"}
		<td><b>Final {$evaltype}:</b></td>
		{else}
		<td><b>{$evaltype}:</b></td>
		{/if}
		<td><b>{$fincomm->level_assigned}</b></td>
		<td></td>
	</tr>
	<tr class="{cycle values='r0,r1'}">
		<td><b>Final Comments:</b></td>
		<td>{$fincomm->comments}</td>
		<td></td>
	</tr>
  {/if}
</table>
{else}
  {if $fincomm}
<table class="fullwidth listing">
 <th class="{cycle values='r0'}">
  <tr>
   <td><b>Final Assigned Level</b></td>
   <td><b>Final Comments</b></td>
  </tr>
 </th>
	<tr class="{cycle values='r0,r1'}">
		<td><b>{$fincomm->level_assigned}</b></td>
		<td>{$fincomm->comments}</td>
	</tr>
</table>
  {/if}
{/if}
{if $eachmember->rework}
	{$eachmember->rework}
{/if}
{/foreach}
{/if}
<br/>

{if $suboutcomes}
<h3 align = "center"> View Sub outcomes </h3>
<br/>
{section name=i loop=$suboutcomes}
<a href="{$WWWROOT}view/showassessments.php?id={$viewid}&outcome={$suboutcomes[$i].id}&first={$first}"> <h6 align="center"> {$suboutcomes[$i].outcome_name} </h6></a>
<br/>
{/section}
<br/>
{/if}
{include file="microfooter.tpl"}

