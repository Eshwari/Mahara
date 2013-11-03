{include file="header.tpl"}
{if $degrees}
{foreach from=$degrees item=degree}
<div class="{cycle values='r0,r1'} listing">
<h4><a href="{$WWWROOT}coursetemplate/coursetemplates.php?course={$degree->id}">{$degree->course_name|escape}</a></h4>&nbsp;
</div>
{/foreach}
{else}
{if $cancreate}
            <div class="rbuttons">
			{if $sample}
		   {if $coursetemplateid == 0}
		   
                <a href="{$WWWROOT}coursetemplate/create.phpoffset={$offset}" class="btn">Create Course Template</a>
			{/if}
		   {else}
                <a href="{$WWWROOT}coursetemplate/create.php?dept_course={$samplesdes}&offset={$offset}" class="btn">Create Course Templates</a>
		   {/if}
            </div>
{/if}

{if $coursetemplates}
{foreach from=$coursetemplates item=coursetemplate}

            <div class="{cycle values='r0,r1'} listing">
{if $cancreate}
			<div class="fr">
                     {include file="coursetemplate/updatecoursetemplate.tpl" coursetemplate=$coursetemplate returnto='find'}
			</div>
{/if}
			<div>
<h4	><a href="{$WWWROOT}coursetemplate/view.php?coursetemplate={$coursetemplate->id|escape}&offset={$offset}">{$coursetemplate->coursetemplate_name|escape}</a></h4>&nbsp;

			</div>
           </div>
{/foreach}
{$pagination}
{/if}

{/if}
{include file="footer.tpl"}
