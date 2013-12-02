{include file="viewmicroheader.tpl"}
{if !$subgroup}<a class="btn-reply" href="{$WWWROOT}view/view.php?id={$viewid}">
{else}<a class="btn-reply" href="{$WWWROOT}view/view.php?id={$viewid}&group={$subgroup}">{/if}
{str tag=back}</a>&nbsp;
<h2 align="center"> {$outcomename} Outcome </h2>
{if $comevaluation}
{$comevaluation}
{/if}

<br/>
{include file="microfooter.tpl"}
