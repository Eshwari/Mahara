<ul>
{foreach from=$categories key=name item=category}

    <li class="{$category.class}">
{if $category.title|escape!="Outcome Type (1)" || ($category.title|escape=="Outcome Type (1)" && $outcome)}
{if $category.title|escape!="CourseOutcome Type (1)" || ($category.title|escape=="Course Outcome Type (1)" && $courseoutcome)}
<a href="{$WWWROOT}view/blocks.php?id={$viewid}&amp;c={$category.name|escape}&amp;new={$new}">{$category.title|escape}</a>
{/if}

</li>
{/foreach}
</ul>
