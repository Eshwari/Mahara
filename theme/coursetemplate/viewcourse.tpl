{include file="header.tpl"}

{if $GROUP->description}
	<div class="groupdescription">{$GROUP->description}</div>
{/if}

<div class="group-info">
  <div class="fr">
  {include file="coursetemplate/groupuserstatus.tpl" group=$group returnto='viewcourse'}
  </div>
  {include file="coursetemplate/info.tpl"}
</div>

{if $group->public || $role}
    <h4>{str tag=latestforumposts section=interaction.forum}</h4>
    {if $foruminfo}
        <table class="fullwidth s" id="groupforumtable">
        {foreach from=$foruminfo item=postinfo}
            <tr class="{cycle values='r0,r1'}">
                <td><strong><a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic|escape}#post{$postinfo->id|escape}">{$postinfo->topicname|escape}</a></strong></td>
                <td>{$postinfo->body|str_shorten_html:100:true}</td>
                <td><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$postinfo->poster|escape}" alt="">
                    <a href="{$WWWROOT}user/courseview.php?id={$postinfo->poster|escape}">{$postinfo->poster|display_name|escape}</a>
                </td>
            </tr>
        {/foreach}
        </table>
    {else}
        <p>{str tag=noforumpostsyet section=interaction.forum}</p>
    {/if}
    <div class="right"><a href="{$WWWROOT}interaction/forum/?group={$group->id|escape}"><strong>{str tag=gotoforums section=interaction.forum} &raquo;</strong></a></div>
{/if}

{if $sharedviews}
    <h4>{str tag="viewssharedtogroupbyothers" section="viewcourse"}</h4>
    <p>
    <table class="fullwidth">
    {foreach from=$sharedviews item=view}
        <tr class="{cycle values='r0,r1'}">
            <td>
                <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a>
                {if $view.sharedby}
                    {str tag=by section=viewcourse}
                    {if $view.group}
                        <a href="{$WWWROOT}coursetemplate/viewcourse.php?id={$view.group}">{$view.sharedby}</a>
                    {elseif $view.owner}
                        <a href="{$WWWROOT}user/courseview.php?id={$view.owner}">{$view.sharedby}</a>
                    {else}
                        {$view.sharedby}
                    {/if}
                {/if}
                <div>{$view.shortdescription}</div>
                {if $view.tags}<div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
                {if $view.template}
                <div><a href="">{str tag=copythisview section=viewcourse}</a></div>
                {/if}
            </td>
        </tr>
    {/foreach}
    </table>
    {$pagination}
    </p>
{/if}

{if $formativeviews}
<h4>{str tag="viewssubmittedtoformative" section="viewcourse"}</h4>
    <p>
    <table class="fullwidth">
    {foreach from=$formativeviews item=view}
        <tr class="{cycle values='r0,r1'}">
            <td>		    
		    {if $subgroup}
	               <a href="{$WWWROOT}view/view.php?id={$view.id}&group={$group->id}">{$view.title|escape}</a>
		    {else}
                	   <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a>
		    {/if}

                {if $view.sharedby}
                    {str tag=by section=viewcourse}
                    {if $view.group}
                        <a href="{$WWWROOT}coursetemplate/viewcourse.php?id={$view.group}">{$view.sharedby}</a>{if $view.formativecount>1} - Resubmitted{/if}
                    {elseif $view.owner}
                        <a href="{$WWWROOT}user/courseview.php?id={$view.owner}">{$view.sharedby}</a>{if $view.formativecount>1} - Resubmitted{/if}
                    {else}
                        {$view.sharedby}
                    {/if}
                {/if}				
                <div>{$view.shortdescription}</div>
                {if $view.tags}<div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
            </td>
        </tr>
    {/foreach}
    </table>
    {$pagination}
    </p>
{/if}



{if $submittedviews}
    <h4>{str tag="viewssubmittedtogroup" section="viewcourse"}</h4>
    <p>
    <table class="fullwidth">
    {foreach from=$submittedviews item=view}
	{if $view.status<2 && $view.evaluated==0}
        <tr class="{cycle values='r0,r1'}">
            <td>
		    {if $subgroup}
	               <a href="{$WWWROOT}view/view.php?id={$view.id}&group={$group->id}">{$view.title|escape}{$view.id}</a>
		    {else}
                	   <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}{$view.assessCount}</a>
		    {/if}		

                {if $view.sharedby}
                    {str tag=by section=viewcourse}
                    {if $view.group}
                        <a href="{$WWWROOT}coursetemplate/viewcourse.php?id={$view.group}">{$view.sharedby}</a>
                    {elseif $view.owner}
                        <a href="{$WWWROOT}user/courseview.php?id={$view.owner}">{$view.sharedby}</a>
                    {else}
                        {$view.sharedby}
                    {/if}
                {/if}							
				{if $view.reassess==1}Please re-assess the view{/if}
                <div>{$view.shortdescription}</div>
                {if $view.tags}<div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
            </td>
        </tr>					
		{/if}
		
    {/foreach}
    </table>
    {$pagination}
    </p>
{/if}

{if $finalviews}
    <h4>{str tag="viewsfinal" section="viewcourse"}</h4>
    <p>
    <table class="fullwidth">
    {foreach from=$finalviews item=view}		
        <tr class="{cycle values='r0,r1'}">
            <td>
		    {if $subgroup}
	               <a href="{$WWWROOT}view/view.php?id={$view.id}&group={$group->id}">{$view.title|escape}</a>
		    {else}
                	   <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a>
		    {/if}		

                {if $view.sharedby}
                    {str tag=by section=viewcourse}
                    {if $view.group}
                        <a href="{$WWWROOT}coursetemplate/viewcourse.php?id={$view.group}">{$view.sharedby}</a>
                    {elseif $view.owner}
                        <a href="{$WWWROOT}user/courseview.php?id={$view.owner}">{$view.sharedby}</a>
                    {else}
                        {$view.sharedby}
                    {/if}
                {/if}				
				
                <div>{$view.shortdescription}</div>
                {if $view.tags}<div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
            </td>
        </tr>		
    {/foreach}	
    </table>
    {$pagination}
    </p>
{/if}


{if $finalviews2}
    <h4>{str tag="viewsfinal" section="viewcourse"}</h4>
    <p>
    <table class="fullwidth">
    {foreach from=$finalviews2 item=view}		
        <tr class="{cycle values='r0,r1'}">
            <td>
		    {if $subgroup}
	               <a href="{$WWWROOT}view/view.php?id={$view.id}&group={$group->id}">{$view.title|escape}</a>
		    {else}
                	   <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a>
		    {/if}		

                {if $view.sharedby}
                    {str tag=by section=viewcourse}
                    {if $view.group}
                        <a href="{$WWWROOT}coursetemplate/viewcourse.php?id={$view.group}">{$view.sharedby}</a>
                    {elseif $view.owner}
                        <a href="{$WWWROOT}user/courseview.php?id={$view.owner}">{$view.sharedby}</a>
                    {else}
                        {$view.sharedby}
                    {/if}
                {/if}				
				
                <div>{$view.shortdescription}</div>
                {if $view.tags}<div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
            </td>
        </tr>		
    {/foreach}	
    </table>
    {$pagination}
    </p>
{/if}

{include file="footer.tpl"}
