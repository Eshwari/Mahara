<h4><a href="{$WWWROOT}coursetemplate/viewcourse.php?id={$group->id|escape}">{$group->name|escape}</a></h4>
<!-- Start - Anusha -->
{if $group->courseoutcome != null}
	{if $group->role != "member"}
		<h6>{foreach name=admins from=$group->admins item=id}<a href="{$WWWROOT}user/courseview.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</h6>
	{/if}
{else}
<!-- End - Anusha -->
	<h6>{foreach name=admins from=$group->admins item=id}<a href="{$WWWROOT}user/courseview.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</h6>
<!-- Start - Anusha -->
{/if}
<!-- End - Anusha -->
{$group->description}
<!-- Start - Anusha -->
{if $group->courseoutcome != null}
	{if $group->role != "member"}
		<div>{str tag="memberslist" section="group"}
		{foreach name=members from=$group->members item=member}
			<a href="{$WWWROOT}user/courseview.php?id={$member->id|escape}">{$member->name|escape}</a>{if !$.foreach.members.last}, {/if}
		{/foreach}
		{if $group->membercount > 3}<a href="{$WWWROOT}group/members.php?id={$group->id|escape}">...</a>{/if}
		</div>
	{/if}
{else}
<!-- End - Anusha -->
	<div>{str tag="memberslist" section="group"}
	{foreach name=members from=$group->members item=member}
		<a href="{$WWWROOT}user/courseview.php?id={$member->id|escape}">{$member->name|escape}</a>{if !$.foreach.members.last}, {/if}
	{/foreach}
	{if $group->membercount > 3}<a href="{$WWWROOT}group/members.php?id={$group->id|escape}">...</a>{/if}
	</div>	
<!-- Start - Anusha -->
{/if}
<!-- End - Anusha -->

