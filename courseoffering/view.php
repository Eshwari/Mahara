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
define('MENUITEM', 'courseofferings/info');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('courseoffering.php');
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'file');

if (!$USER->is_logged_in()) {
    throw new AccessDeniedException();
}

$courseoffering_id = param_integer('courseoffering');
$offset= param_integer('offset',0);

$outrec = is_courseoffering_available($courseoffering_id);

if (!$outrec) {
    throw new AccessDeniedException("courseoffering does not exist");
}

if($outrec->main_courseoffering){

}
/*
$outprereqsdes  = '';
if($outrec->prerequisite_type == 'Prereq'){
$courseofferingprerequisites = @get_records_sql_array(
	'SELECT *
	  FROM {courseoffering_prerequisites} 
        WHERE courseoffering_id = ?
	  AND rubric_no = 0
	  ORDER BY prerequisite_val',
	array($courseoffering_id)
);
foreach($courseofferingprerequisites as $courseofferingprerequisite){
	$outprereqsdes = $outprereqsdes .'<br/><b>Pre Reqs' . $courseofferingprerequisite->prerequisite_val . '</b>:'. $courseofferingprerequisite->prerequisite_desc;
}
}else{

//$SESSION->add_ok_msg('No Pre requisites for the course');
//$outprereqsdes = '<b>courseoffering Evaluation</b> ' . $outrec->eval_type;
}

*/

define('TITLE', $outrec->courseoffering_name);


$smarty = smarty();
$smarty->assign('outrec',$outrec);
$smarty->assign('PAGEHEADING', $outrec->courseoffering_name);
$smarty->assign('main_courseoffering',$outrec->main_courseoffering);
$smarty->assign('COURSEOFFERING', courseoffering_get_menu_tabs($courseoffering_id));
$smarty->display('courseoffering/view.tpl');

?>
