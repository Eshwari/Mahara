{include file="header.tpl"}
{if $degrees}
{foreach from=$degrees item=degree}
<div class="{cycle values='r0,r1'} listing">
<h4><a href="{$WWWROOT}courseoffering/courseofferings.php?coursetemplate={$degree->id}">{$degree->coursetemplate_name|escape}</a></h4>&nbsp;
</div>
{/foreach}
{else}

{if $cancreate}
            <div class="rbuttons">
			{if $samples}
			{if $courseofferingid == 0}

                <a href="{$WWWROOT}courseoffering/create.php?offset={$offset}" class="btn">Create Course</a>
			{/if}
		   {else}
		   <a href="{$WWWROOT}courseoffering/create.php?coursetemplate={$samplesdes}&offset={$offset}" class="btn">Create Course</a>
                

		   {/if}


            </div>
{/if}

{if $courseofferings}


{foreach from=$courseofferings item=courseoffering}

            <div class="{cycle values='r0,r1'} listing">
{if $cancreate}
			<div class="fr">
                     {include file="courseoffering/updatecourseoffering.tpl" courseoffering=$courseoffering returnto='find'}

			</div>
{/if}
			<div>
<h4	><a href="{$WWWROOT}courseoffering/view.php?courseoffering={$courseoffering->id|escape}&offset={$offset}">{$courseoffering->courseoffering_name|escape}</a></h4>&nbsp;

			</div>
           </div>
{/foreach}
{$pagination}
{/if}

{/if}
{include file="footer.tpl"}
