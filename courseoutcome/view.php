<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'courseoutcomes/info');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('courseoutcome.php');
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'file');

if (!$USER->is_logged_in()) {
    throw new AccessDeniedException();
}

$courseoutcome_id = param_integer('courseoutcome');
$offset= param_integer('offset',0);

$outrec = is_courseoutcome_available($courseoutcome_id);

if (!$outrec) {
    throw new AccessDeniedException("Courseoutcome does not exist");
}

if($outrec->main_courseoutcome){

}
$outlevelsdes  = '';
if($outrec->eval_type == 'Level'){
$courseoutcomelevels = @get_records_sql_array(
	'SELECT *
	  FROM {courseoutcome_levels} 
        WHERE courseoutcome_id = ?
	  AND rubric_no = 0
	  ORDER BY level_val',
	array($courseoutcome_id)
);
foreach($courseoutcomelevels as $courseoutcomelevel){
	$outlevelsdes = $outlevelsdes .'<br/><b>Level' . $courseoutcomelevel->level_val . '</b>:'. $courseoutcomelevel->level_desc;
}
}else{
$outlevelsdes = '<b>Courseoutcome Evaluation</b> ' . $outrec->eval_type;
}

if($outrec->special_access){
	$primary_focus = get_record('primary_focus','id',$outrec->special_access);
$primary_name = $primary_focus->primary_focus_name;
}else{
$primary_name = "";
}
define('TITLE', $outrec->courseoutcome_name);


$smarty = smarty();
$smarty->assign('outrec',$outrec);
$smarty->assign('PAGEHEADING', $outrec->courseoutcome_name);
$smarty->assign('main_courseoutcome',$outrec->main_courseoutcome);
$smarty->assign('primary_name',$primary_name);
$smarty->assign('outlevelsdes',$outlevelsdes);
$smarty->assign('COURSEOUTCOMENAV', courseoutcome_get_menu_tabs($courseoutcome_id));
$smarty->display('courseoutcome/view.tpl');

?>
