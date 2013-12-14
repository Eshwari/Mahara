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
define('MENUITEM', 'groups/courseoutcomes');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'group.php');
require_once('pieforms/pieform.php');
define('GROUP', param_integer('group'));
$groupid = param_integer('group');
printf('here');
require_once('courseoutcome.php');
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.courseoffering, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.id = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $groupid)
);

printf('here');
$courseoutname = "";
//start-Eshwari

printf($group->courseoffering);
$courseofferingdes = get_record('course_offering','id',$group->courseoffering);
printf($courseofferingdes->coursetmp_id);

$coursetempdes =get_record('course_template', 'id',$courseofferingdes->coursetmp_id);
		
		$sampledes =get_courseoutcomes($coursetempdes->id);
		
		  $outcomelists= @get_records_sql_array('SELECT  c.id FROM {courseoutcomes} c INNER JOIN {course_template} ct 
				  ON c.coursetemplate_id = ct.id
				WHERE c.coursetemplate_id =? ', array($coursetempdes->id)
				);
	  $course_lists = array();
	  $i=0;
	  foreach($outcomelists as $crstest){
		
		$course_lists[$i]=$crstest->id;
		$i++;
	} 
	$max =count($course_lists);
	for($i= 0; $i < $max; $i++){
	$courseoutcomedes = get_record('courseoutcomes','id',$course_lists[$i]);

	printf($course_lists[$i]);
	//}
	//end-Eshwari
if($group){
if($group->role != "member"){
$student = 0;
$assessments = @get_records_sql_array(
    'SELECT u.username,o.final_level,o.private_comments,o.public_comments
       FROM {view} v 
	   INNER JOIN {courseoutcome_results} o ON (o.view_id = v.id)
	   INNER JOIN {usr} u ON (u.id = v.owner)
	   WHERE v.submittedcourseoutcome = ?
	   AND o.rubric_no = 0
	   AND o.submitted = 2',
    //array($group->outcome)
	array($course_lists[$i])
);

}else {
	$student = 1;
	$assessments = get_record_sql(
		'SELECT o.final_level,o.public_comments
		   FROM {view} v 
		   INNER JOIN {courseoutcome_results} o ON (o.view_id = v.id)
		   WHERE v.owner = ? 
		   AND v.submittedcourseoutcome = ?
		   AND o.rubric_no = 0
		   AND o.submitted = 2',
		//array($USER->get('id'),$group->outcome)
		array($USER->get('id'),$course_lists[$i])
	);
}
printf('assesment');
printf($assessments->id);
//$outcomedes = get_record('outcomes','id',$group->outcome);
$courseoutcomedes = get_record('courseoutcomes','id',$course_lists[$i]);
if($courseoutcomedes){
	$courseoutname = $courseoutcomedes->courseoutcome_name. ' CourseOutcome';
}
}
}//closing for loop for courseoutcomes

define('TITLE',$courseoutname);
$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('assessments',$assessments);
$smarty->assign('student',$student);
$smarty->display('group/groupcourseoutcome.tpl');


?>
