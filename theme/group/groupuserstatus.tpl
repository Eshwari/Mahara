<ul class="groupuserstatus">
{if $group->membershiptype == 'member'}
	<li class="member">{str tag="youaregroupmember" section="group"}</li>
{if $group->canleave}
    <li><a href = "{$WWWROOT}group/leave.php?id={$group->id|escape}&amp;returnto={$returnto}" class="btn-leavegroup">{str tag="leavegroup" section="group"}</a></li>
{/if}
{elseif $group->membershiptype == 'admin'}
	<li><a href="{$WWWROOT}group/edit.php?id={$group->id|escape}" class="btn-edit">{str tag="edit"}</a></li>
	<li><a href="{$WWWROOT}group/delete.php?id={$group->id|escape}" class="btn-del">{str tag="delete"}</a></li>
	
{if $group->jointype == 'request' && $group->requests}
	<li>
		<a href="{$WWWROOT}group/members.php?id={$group->id|escape}&amp;membershiptype=request" class="btn-pending">{str tag="membershiprequests" section="group"} ({$group->requests})</a>
	</li>
{/if}
	
{elseif $group->membershiptype == 'invite'}
	<li>
{if $group->role}
		{assign var=grouptype value=$group->grouptype}
		{assign var=grouprole value=$group->role}
		{str tag="grouphaveinvitewithrole" section="group"}: {str tag="$grouprole" section="grouptype.$grouptype"}
{else}
		{str tag="grouphaveinvite" section="group"}
{/if}
	{$group->invite}
	</li>
	
{elseif $group->membershiptype == 'request'}
	<li>{str tag="requestedtojoin" section="group"}</li>
	
{elseif $group->jointype == 'open'}
	{$group->groupjoin}
	
{elseif $group->jointype == 'request'}
	<li><a href="{$WWWROOT}group/requestjoin.php?id={$group->id|escape}&amp;returnto={$returnto}" class="btn-req">{str tag="requestjoingroup" section="group"}</a></li>
	
{/if}
</ul>
