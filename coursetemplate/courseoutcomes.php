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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require('courseoutcome.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'course outcomes');
$offset = param_integer('offset', 'all');

$courseoutcome_id = param_integer('courseoutcome',0);

$course_id = param_integer('course',0);

if(!$USER->get('staff') && !$USER->get('admin')){
	$usr_rec = get_record('usr','id',$USER->get('id'));
	if (!$usr_rec->degree_id) {
    		throw new AccessDeniedException("Not a valid degree course");
	}
	$course_id = $usr_rec->degree_id;
}

if($courseoutcome_id == 0){
define('MENUITEM', 'courseoutcomes/courseoutcomes');
define('TITLE', 'Course Outcomes ');

if($course_id == 0){
	$listcourseoutcomes = 0;
}else{
	$course_rec = get_record('degree_courses','id',$course_id);
	if (!$course_rec) {
    		throw new AccessDeniedException("Not a valid degree course");
	}
	$listcourseoutcomes = 1;
}
}else{

$listcourseoutcomes = 1;
$courseoutcomedes = is_courseoutcome_available($courseoutcome_id);

if (!$courseoutcomedes) {
    throw new AccessDeniedException("Course Outcomes does not exist");
}

define('MENUITEM', 'courseoutcomes/subcourseoutcomes');
define('TITLE', $courseoutcomedes->courseoutcome_name);
}

$smarty = smarty();
if($listcourseoutcomes){
$courseoutcomesperpage = 20;
$offset = (int)($offset / $courseoutcomesperpage) * $courseoutcomesperpage;

$results = get_courseoutcomes($courseoutcomesperpage, $offset, $courseoutcome_id, (int)$course_id);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'courseoutcome/courseoutcomes.php?courseoutcome=' . $courseoutcome_id,
    'count' => $results['count'],
    'limit' => $courseoutcomesperpage,
    'offset' => $offset,
    'resultcounttextsingular' => 'courseoutcome',
    'resultcounttextplural' => 'courseoutcomes',
));

$smarty->assign('courseoutcomes', $results['courseoutcomes']);
$smarty->assign('cancreate', can_create_courseoutcomes());
$smarty->assign('pagination', $pagination['html']);
}else{
$degrees = @get_records_sql_array(
    'SELECT id, degree_name
       FROM {degree_courses}'
);
$smarty->assign('degrees', $degrees);

}

$smarty->assign('courseoutcomeid',$courseoutcome_id);
$smarty->assign('main_courseoutcome',$courseoutcomedes->main_courseoutcome);
$smarty->assign('offset',$offset);
if($courseoutcome_id != 0){
$smarty->assign('COURSEOUTCOMENAV', courseoutcome_get_menu_tabs($courseoutcome_id));
$smarty->assign('PAGEHEADING', $courseoutcomedes->courseoutcome_name);
}else{
$smarty->assign('COURSEOUTCOMENAV','');
if($listcourseoutcomes){
	$smarty->assign('PAGEHEADING', 'Course Outcomes');
}else{
	$smarty->assign('PAGEHEADING', 'Degree Courses');
}
}
$smarty->display('courseoutcome/courseoutcomes.tpl');



?>
