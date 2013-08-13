{include file="header.tpl"}
{if $courses}
{foreach from=$courses item=course}
<div class="{cycle values='r0,r1'} listing">
<h4><a href="{$WWWROOT}createcourse/createcourses.php?degree={$course->id}">{$course->course_name|escape}</a></h4>&nbsp;
</div>
{/foreach}
{else}
{if $cancreate}
            <div class="rbuttons">
		   {if $createcourseid == 0}
                <a href="{$WWWROOT}createcourse/create.php?offset={$offset}" class="btn">Create Course</a>
		   {else}
                <a href="{$WWWROOT}createcourse/create.php?createcourse={$createcourseid}&offset={$offset}" class="btn">Create Sub Course</a>
		   {/if}
            </div>
{/if}

{if $createcourses}
{foreach from=$createcourses item=createcourse}

            <div class="{cycle values='r0,r1'} listing">
{if $cancreate}
			<div class="fr">
                     {include file="createcourse/updatecreatecourse.tpl" createcourse=$createcourse returnto='find'}
			</div>
{/if}
			<div>
<h4	><a href="{$WWWROOT}createcourse/view.php?course={$createcourse->id|escape}&offset={$offset}">{$createcourse->course_name|escape}</a></h4>&nbsp;

			</div>
           </div>
{/foreach}
{$pagination}
{/if}

{/if}
{include file="footer.tpl"}
