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
		   {if $coursetemplateid == 0}
                <a href="{$WWWROOT}coursetemplate/create.php?offset={$offset}" class="btn">Create Course Template</a>
		   {else}
                <a href="{$WWWROOT}outcome/create.php?coursetemplate={$coursetemplateid}&offset={$offset}" class="btn">Create Sub coursetemplate</a>
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
