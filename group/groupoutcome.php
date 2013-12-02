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
define('MENUITEM', 'groups/outcomes');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'group.php');
require_once('pieforms/pieform.php');
define('GROUP', param_integer('group'));
$groupid = param_integer('group');

$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.outcome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.id = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $groupid)
);

$outname = "";

if($group){
if($group->role != "member"){
$student = 0;
$assessments = @get_records_sql_array(
    'SELECT u.username,o.final_level,o.private_comments,o.public_comments
       FROM {view} v 
	   INNER JOIN {outcome_results} o ON (o.view_id = v.id)
	   INNER JOIN {usr} u ON (u.id = v.owner)
	   WHERE v.submittedoutcome = ?
	   AND o.rubric_no = 0
	   AND o.submitted = 2',
    array($group->outcome)
);

}else {
	$student = 1;
	$assessments = get_record_sql(
		'SELECT o.final_level,o.public_comments
		   FROM {view} v 
		   INNER JOIN {outcome_results} o ON (o.view_id = v.id)
		   WHERE v.owner = ? 
		   AND v.submittedoutcome = ?
		   AND o.rubric_no = 0
		   AND o.submitted = 2',
		array($USER->get('id'),$group->outcome)
	);
}
$outcomedes = get_record('outcomes','id',$group->outcome);
if($outcomedes){
	$outname = $outcomedes->outcome_name. ' Outcome';
}
}


define('TITLE',$outname);
$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('assessments',$assessments);
$smarty->assign('student',$student);
$smarty->display('group/groupoutcome.tpl');


?>
