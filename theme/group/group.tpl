<h4><a href="{$WWWROOT}group/view.php?id={$group->id|escape}">{$group->name|escape}</a></h4>
<!-- Start - Anusha -->
{if $group->outcome != null}
	{if $group->role != "member"}
		<h6>{foreach name=admins from=$group->admins item=id}<a href="{$WWWROOT}user/view.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</h6>
	{/if}
<!--Start-Eshwari-->
{elseif $group->courseoffering != null}	
    {if $group->role != "member"}
		<h6>{foreach name=admins from=$group->admins item=id}<a href="{$WWWROOT}user/view.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</h6>
	{/if}
<!-- End - Eshwari -->	
{else}
<!-- End - Anusha -->
	<h6>{foreach name=admins from=$group->admins item=id}<a href="{$WWWROOT}user/view.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</h6>
<!-- Start - Anusha -->
{/if}
<!-- End - Anusha -->
{$group->description}
<!-- Start - Anusha -->
{if $group->outcome != null}
	{if $group->role != "member"}
		<div>{str tag="memberslist" section="group"}
		{foreach name=members from=$group->members item=member}
			<a href="{$WWWROOT}user/view.php?id={$member->id|escape}">{$member->name|escape}</a>{if !$.foreach.members.last}, {/if}
		{/foreach}
		{if $group->membercount > 3}<a href="{$WWWROOT}group/members.php?id={$group->id|escape}">...</a>{/if}
		</div>
	{/if}
<!--Start-Eshwari-->
{elseif $group->courseoffering != null}
{if $group->role != "member"}
		<div>{str tag="memberslist" section="group"}
		{foreach name=members from=$group->members item=member}
			<a href="{$WWWROOT}user/view.php?id={$member->id|escape}">{$member->name|escape}</a>{if !$.foreach.members.last}, {/if}
			{/foreach}
		{if $group->membercount > 3}<a href="{$WWWROOT}group/members.php?id={$group->id|escape}">...</a>{/if}
		</div>
	{/if}
<!-- End - Eshwari -->		
{else}
<!-- End - Anusha -->
	<div>{str tag="memberslist" section="group"}
	{foreach name=members from=$group->members item=member}
		<a href="{$WWWROOT}user/view.php?id={$member->id|escape}">{$member->name|escape}</a>{if !$.foreach.members.last}, {/if}
	{/foreach}
	{if $group->membercount > 3}<a href="{$WWWROOT}group/members.php?id={$group->id|escape}">...</a>{/if}
	</div>	
<!-- Start - Anusha -->
{/if}
<!-- End - Anusha -->

