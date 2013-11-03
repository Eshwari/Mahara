{include file="header.tpl"}

{if $cancreate}
            <div class="rbuttons">
                <a href="{$WWWROOT}courseoutcome/createrubric.php?courseoutcome={$courseoutcomeid}&offset={$offset}" class="btn">Add Rubrics</a>
            </div>
{/if}

{if $rubrics}
{foreach from=$rubrics item=rubric}
            <div class="{cycle values='r0,r1'} listing">
{if $cancreate}

			<div class="fr">
<ul class="groupuserstatus">
	<li><a href="{$WWWROOT}courseoutcome/editrubric.php?courseoutcome={$rubric->courseoutcome_id|escape}&rubric={$rubric->id}&rub_offset={$offset}" class="btn-edit">{str tag="edit"}</a></li>
	<li><a href="{$WWWROOT}courseoutcome/deleterubric.php?courseoutcome={$rubric->courseoutcome_id|escape}&rubric={$rubric->id}&offset={$offset}" class="btn-del">{str tag="delete"}</a></li>
</ul>
			</div>
{/if}
			<div>
<h3><a href="{$WWWROOT}courseoutcome/rubricview.php?courseoutcome={$rubric->courseoutcome_id|escape}&rubric={$rubric->id|escape}">{$rubric->rubric_name|escape}</a></h3>
&nbsp;
			</div>
           </div>
{/foreach}
{$pagination}
{/if}

{include file="footer.tpl"}
