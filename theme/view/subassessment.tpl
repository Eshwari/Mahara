{include file="viewmicroheader.tpl"}
{if $main_outcome}
{if $sub  == $main_outcome}
	<a class="btn-reply" href="{$WWWROOT}view/listassessments.php?id={$viewid}&outcome={$main_outcome}&first={$first}">{str tag=back}</a>&nbsp;&nbsp;&nbsp;&nbsp;
	{if $first  != $main_outcome}
	<a class="btn-reply" href="{$WWWROOT}view/listassessments.php?id={$viewid}&outcome={$first}&first={$first}">{str tag=back} to Main Outcomes</a>&nbsp;
	{/if}
{else}
	<a class="btn-reply" href="{$WWWROOT}view/listsubassessments.php?id={$viewid}&outcome={$main_outcome}&first={$first}&sub={$sub}">{str tag=back}</a>&nbsp;&nbsp;&nbsp;&nbsp;
	<a class="btn-reply" href="{$WWWROOT}view/listassessments.php?id={$viewid}&outcome={$first}&first={$first}">{str tag=back} to Main Outcomes</a>&nbsp;
{/if}
{/if}

<h2> {$mainoutcomename} </h2>
<h3> {$outcomename} </h3>
{if $hdravail}
<div class="group-info">

{$outcomedetails}
</div>
{/if}
{if $comevaluation}
{section name=i loop=$comevaluation} 
{if $evaltype == "Level"}
<table class="fullwidth table">
<thead>
<tr>
<th>{$comevaluation[$i].name}</th>
</tr>
</thead>
</table>
<br/>
<b>Level 1 : </b>{$comevaluation[$i].level1}
<br/>
<b>Level 2 : </b>{$comevaluation[$i].level2}
<br/>
<b>Level 3 : </b>{$comevaluation[$i].level3}
<br/>
<b>Level 4 : </b>{$comevaluation[$i].level4}
{else}
<br/>
<h6> {$i+1}. {$comevaluation[$i].name} </h6>
{/if}
{if $assessmentdone}
<table class="fullwidth listing">
 <th class="{cycle values='r0'}">
  <tr>
   <td><b>Committee Member</b></td>
   <td><b>{$evaltype}</b></td>
   <td><b>Comments</b></td>
  </tr>
 </th>
{foreach from=$submembers item=submember}
    <tr class="{cycle values='r0,r1'}">
    <td>{$submember->username}</td>
    <td>{$comevaluation[$i][$submember->member].level}</td>
    <td>{$comevaluation[$i][$submember->member].comments}</td>
    </tr>
{/foreach}
</table>
{/if}
{/section}
{/if}
<br/>

{if $finalresults}
Outcome Assessment
<table class="fullwidth listing">
 <th class="{cycle values='r0'}">
  <tr>
   <td><b>Committee Member</b></td>
   <td><b>{$evaltype}</b></td>
   <td><b>Comments</b></td>
  </tr>
 </th>
{foreach from=$submembers item=submember}
{if $finalresults[$submember->member].level}
<tr class="{cycle values='r0,r1'}">
    <td>{$submember->username}</td>
    <td>{$finalresults[$submember->member].level}</td>
    <td>{$finalresults[$submember->member].comments}</td>
</tr>
{/if}
{/foreach}
</table>
{/if}
<br/>

{if $suboutcomes}
<h3 align = "center"> View Sub outcomes </h3>
<br/>
{section name=i loop=$suboutcomes}
<a href="{$WWWROOT}view/listsubassessments.php?id={$viewid}&outcome={$suboutcomes[$i].id}&first={$first}&sub={$sub}"> <h6 align="center"> {$suboutcomes[$i].outcome_name} </h6></a>
<br/>
{/section}
<br/>
{/if}
{include file="microfooter.tpl"}
