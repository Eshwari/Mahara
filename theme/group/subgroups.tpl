{include file="header.tpl"}
{if $group->parent_group}
<br/>
<a class="btn-reply" href="{$WWWROOT}group/view.php?id={$group->parent_group}">
{str tag=back} to Parent Group</a>
{/if}
{if $cancreate}
            <div class="rbuttons">
                <a href="{$WWWROOT}group/create.php?id={$groupid}&outcome={$outcome}" class="btn">{str tag="createsubgroup" section="group"}</a>
		&nbsp;&nbsp;&nbsp;&nbsp;
            </div>
{/if}
<br>
{if $subgroups}
{foreach from=$subgroups item=subgroup}
            <div class="{cycle values='r0,r1'} listing">
{if $cancreate}
			<div class="fr">
                     {include file="group/updatesubgroups.tpl" subgroup=$subgroup returnto='find'}
			</div>
{/if}
			<div>
<h4	><a href="{$WWWROOT}group/view.php?id={$subgroup->id|escape}&offset={$offset}">{$subgroup->name|escape}</a></h4>&nbsp;

			</div>
           </div>
{/foreach}
{$pagination}
{/if}


{include file="footer.tpl"}
