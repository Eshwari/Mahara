<h3><a href="{$WWWROOT}coursetemplate/view.php?coursetemplate={$coursetemplate->id|escape}&offset={$offset}">{$coursetemplate->coursetemplate_name|escape}</a></h3>
<h6> <a href="{$WWWROOT}coursetemplate/coursetemplates.php?coursetemplate={$coursetemplate->id}&main_offset={$offset}" class="btn">Sub coursetemplates</a></h6>
<h6> <a href="{$WWWROOT}coursetemplate/rubrics.php?coursetemplate={$coursetemplate->id}" class="btn">Rubrics</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href="{$WWWROOT}coursetemplate/primary.php?coursetemplate={$coursetemplate->id}&main_offset={$offset}" class="btn">Set/Remove Primary focus area</a> <h6> 