{include file="header.tpl"}
  
<div class="message">
{if $header}
<h3 align="center">{$header}</h3>
{/if} 
<p>{$message}</p>
{$form}
</div>
{include file="footer.tpl"}
