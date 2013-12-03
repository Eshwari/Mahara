{include file="header.tpl"}

<div class="group-info">

<ul>
{if $outrec->Instructor1}
	<ul><b>Instructor 1: </b>{$outrec->Instructor1}</ul>
{/if}
{if $outrec->Instructor2}
	<ul><b>Instructor 2: </b>{$outrec->Instructor2}</ul>
{/if}
{if $outrec->semester}
	<li><b>Semester offered: </b>{$outrec->semester}</li>
{/if}

{if $outrec->coursetmp_name}
	<li><b>Course Template Name: </b>{$outrec->coursetmp_name}</li>
{/if}



</ul>

</div>

{include file="footer.tpl"}
