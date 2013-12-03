{include file="header.tpl"}

{if $cancreate}
            <div class="rbuttons">
                <a href="{$WWWROOT}courseoffering/createactivity.php?courseoffering={$courseofferingid}&offset={$offset}" class="btn">Add Activities</a>
            </div>


{/if}

{if $activities}
{foreach from=$activities item=activity}
            <div class="{cycle values='r0,r1'} listing">
{if $cancreate}

			<div class="fr">
<ul class="groupuserstatus">
	<li><a href="{$WWWROOT}courseoffering/editactivity.php?courseoffering={$activity->courseoffering_id|escape}&activity={$activity->id}&act_offset={$offset}" class="btn-edit">{str tag="edit"}</a></li>
	<li><a href="{$WWWROOT}courseoffering/deleteactivity.php?courseoffering={$activity->courseoffering_id|escape}&activity={$activity->id}&offset={$offset}" class="btn-del">{str tag="delete"}</a></li>
</ul>
			</div>
{/if}
			<div>
<h3><a href="{$WWWROOT}courseoffering/viewactivity.php?courseoffering={$activity->courseoffering_id|escape}&activity={$activity->id|escape}">{$activity->activity_name|escape}</a></h3>
&nbsp;
			</div>
           </div>
{/foreach}
{$pagination}
{/if}

{include file="footer.tpl"}