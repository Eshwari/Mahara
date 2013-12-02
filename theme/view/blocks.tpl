{include file="header.tpl"}
{if $columns}
    {str tag="editblockspagedescription" section="view"}

    <form action="{$formurl}" method="post">
        <input type="submit" name="{$action_name}" id="action-dummy" class="hidden">
        <input type="hidden" id="viewid" name="id" value="{$view}">
        <input type="hidden" name="change" value="1">
        <input type="hidden" id="category" name="c" value="{$category}">
        {if $new}<input type="hidden" name="new" value="1">{/if}
        <div id="page">
            <div id="top-pane">
                <div id="category-list">
                    {$category_list}
                </div>
                <div id="blocktype-list">
                    {$blocktype_list}
                </div>
                <div class="cb"></div>
            </div>

            <a id="btn-displaymyview" class="fr" href="view.php?id={$view}&amp;new={$new}">{str tag=displaymyview section=view} &raquo;</a>
            
            <a id="layout-link" href="columns.php?id={$view}&amp;c={$category}&amp;new={$new}"{if !$can_change_layout} class="disabled"{/if}>{str tag='changeviewlayout' section='view'}</a> {contextualhelp plugintype="core" pluginname="view" section="changeviewlayout"}
                <div class="cb"></div>

            <div id="bottom-pane">
                <div id="column-container">
                	<div id="blocksinstruction" class="center">
                	    {str tag='blocksintructionnoajax' section='view'}
                	</div>
                	    {$columns}
                    <div class="cb"></div>
                </div>
            </div>
            <script type="text/javascript">
            {literal}
            insertSiblingNodesAfter('bottom-pane', DIV({'id': 'views-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading')));
            {/literal}
            </script>
        </div>
    </form>

    <div id="view-wizard-controls" class="center">
    {if $new}
        <form action="" method="POST">
            <input type="hidden" name="id" value="{$view}">
            <input type="hidden" name="new" value="1">
            <input type="submit" name="cancel" class="submit" value="{str tag='cancel'}" onclick="return confirm('{str tag='confirmcancelcreatingview' section='view'}');">
        </form>
        <form action="{$WWWROOT}view/edit.php" method="GET">
            <input type="hidden" name="id" value="{$view}">
            <input type="hidden" name="new" value="1">
            <input type="submit" class="submit" value="{str tag='next'}">
        </form>
    {elseif $profile}
        <form action="{$WWWROOT}user/view.php" method="GET">
            <input class="submit" type="submit" value="{str tag='done'}">
        </form>
    {else}
        <form action="{$WWWROOT}view/{if $groupid}groupviews.php{elseif $institution}institutionviews.php{/if}" method="GET">
        {if $groupid}
            <input type="hidden" name="group" value="{$groupid}">
        {elseif $institution}
            <input type="hidden" name="institution" value="{$institution}">
        {/if}
            <input class="submit" type="submit" value="{str tag='done'}">
        </form>
    {/if}
    </div>

{elseif $block}
    <div class="blockconfig-background">
        <div class="blockconfig-container">
            {$block.html}
        </div>
    </div>
{/if}
{include file="footer.tpl"}
