{include file="header.tpl"}
{if $degrees}
{foreach from=$degrees item=degree}
<div class="{cycle values='r0,r1'} listing">
<h4><a href="{$WWWROOT}coursetemplate/courseoutcomes.php?course={$degree->id}">{$degree->degree_name|escape}</a></h4>&nbsp;
</div>
{/foreach}
{else}
{if $cancreate}
            <div class="rbuttons">
		   {if $courseoutcomeid == 0}
                <a href="{$WWWROOT}coursetemplate/create.php?offset={$offset}" class="btn">Create Course Outcome</a>
		   {else}
                <a href="{$WWWROOT}coursetemplate/create.php?courseoutcome={$courseoutcomeid}&offset={$offset}" class="btn">Create Sub CourseOutcome</a>
		   {/if}
            </div>
{/if}

{if $courseoutcomes}
{foreach from=$courseoutcomes item=courseoutcome}

            <div class="{cycle values='r0,r1'} listing">
{if $cancreate}
			<div class="fr">
                     {include file="coursetemplate/updatecourseoutcome.tpl" courseoutcome=$courseoutcome returnto='find'}
			</div>
{/if}
			<div>
<h4	><a href="{$WWWROOT}coursetemplate/view.php?courseoutcome={$courseoutcome->id|escape}&offset={$offset}">{$courseoutcome->courseoutcome_name|escape}</a></h4>&nbsp;

			</div>
           </div>
{/foreach}
{$pagination}
{/if}

{/if}
{include file="footer.tpl"}
