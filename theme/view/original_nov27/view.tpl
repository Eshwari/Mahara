{include file="viewmicroheader.tpl"}
{if $backgroup}
	<a class="btn-reply" href="{$WWWROOT}group/view.php?id={$backgroup}">{str tag=back}</a>&nbsp;
{/if}
<h1>{if !$new}{if !$subgroup}<a href="{$WWWROOT}view/view.php?id={$viewid}">{else}<a href="{$WWWROOT}view/view.php?id={$viewid}&group={$subgroup}">{/if}{/if}{$viewtitle|escape}{if !$new}</a>{/if}</h1>

<p id="view-description">{$viewdescription}</p>

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent}
                <div class="cb">
                </div>
            </div>
        </div>
  <div class="viewfooter cb">
    {if $tags}<div class="tags">{str tag=tags}: {list_tags owner=$owner tags=$tags}</div>{/if}
    <div>{$releaseform}</div>
	
	<!-- Start - Anusha -->
	{if $selfevaluation}
		<br/>
		<table class="fullwidth table">
			<thead>
				<tr><th>
					{str tag="selfeval" section="view"}
				</th></tr>
			</thead>
			<tbody>
				{$selfevaluation}
			</tbody>
		</table>
		<table class="fullwidth table">
			<thead>
				<tr><th>
					{str tag="comeval" section="view"}
				</th></tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<br/>
		<a href="{$WWWROOT}view/listassessments.php?id={$viewid}&outcome={$submittedoutcome}&first={$submittedoutcome}">View Assessment</a>
		<br/>
		{if $admin && $display==1} 
			<a href="{$WWWROOT}view/showassessments.php?id={$viewid}&outcome={$submittedoutcome}&first={$submittedoutcome}">View Assessment by committee</a>		
			<br/>
			{if $finalAssessment}
				<a href="{$WWWROOT}view/finalassessment.php?id={$viewid}&outcome={$submittedoutcome}">Provide Final Assessment</a>
				<br/>
			{/if}
		{/if}
		<br/>
	{/if}
	<!-- End - Anusha -->	
    
		<!--  Start by Shashank for formative assessment change -->
		{if $describeformative}
		<br/>
		<table class="fullwidth table">
			<thead>
				<tr><th>
					{str tag="selfevalformative" section="view"}
				</th></tr>
			</thead>
			<tbody>
				{$selfevaluationformative}
			</tbody>
		</table>
		{/if}
		<!-- End by Shashank -->
    <table id="feedbacktable" class="fullwidth table">
      <thead><tr><th>{str tag="feedback" section="view"}</th></tr></thead>
      <tbody>
        {$feedback->tablerows}
      </tbody>
    </table>
    {$feedback->pagination}
	<div id="viewmenu">
        {include file="view/viewmenu.tpl"}
    </div>
    {if $addfeedbackform}<div>{$addfeedbackform}</div>{/if}
    {if $objectionform}<div>{$objectionform}</div>{/if}
  </div>
</div>

{include file="microfooter.tpl"}
