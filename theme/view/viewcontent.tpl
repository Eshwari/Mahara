<h2>{$viewtitle}{if $ownername} {str tag=by section=view} {$ownername}{/if}</h2>

<p class="view-description">{$viewdescription}</p>

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent}
                <div class="cb">
                </div>
            </div>
        </div>
{if $tags}
  <div class="viewfooter cb">
    <div class="tags">{str tag=tags}: {list_tags owner=0 tags=$tags}</div>
  </div>
{/if}
</div>
