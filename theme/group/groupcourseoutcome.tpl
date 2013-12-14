{include file="header.tpl"}
	{if $assessments}
		{if $student}
			<div class="group-info">
				<ul>
					<li><label>Final Level against course outcome:</label> {$assessments->final_level} </li>
					<li><label>Comments:</label> {$assessments->public_comments} </li>
				</ul>
			</div>
		{else}
			{foreach from=$assessments item=assessment}
				<div class="group-info">
					<ul>
						<li><label>Student Id:</label> {$assessment->username}</li>
						<li><label>Final Level against course outcome:</label> {$assessment->final_level} </li>
						<li><label>Comments to Student:</label> {$assessment->public_comments} </li>
						<li><label>Private Comments to the Committee:</label> {$assessment->private_comments} </li>
					</ul>
				</div>
			{/foreach}
		{/if}
	{else}
		<div class="group-info">
			<ul>
				<li><h4 align="center">Not Available</h4></li>
			</ul>
		</div>
	{/if}
{include file="footer.tpl"}
