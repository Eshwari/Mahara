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
require('courseoffering.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'course offerings');
$offset = param_integer('offset', 'all');

$courseoffering_id = param_integer('courseoffering',0);

$course_id = param_integer('coursetemplate',0);


if(!$USER->get('staff') && !$USER->get('admin')){
	$usr_rec = get_record('usr','id',$USER->get('id'));
	if (!$usr_rec->coursetmp_id) {
    		throw new AccessDeniedException("Not a valid degree course offering");
	}
	$course_id = $usr_rec->coursetmp_id;
}

if($courseoffering_id == 0){
define('MENUITEM', 'courseofferings/courseofferings');
define('TITLE', 'Course Offerings');

if($course_id == 0){
	$listcourseofferings = 0;
}else{
	$course_rec = get_record('course_template','id',$course_id);
	if (!$course_rec) {
    		throw new AccessDeniedException("Not a valid degree course");
	}
	$listcourseofferings = 1;
	
}
}else{

$listcourseofferings = 1;
$courseofferingdes = is_courseoffering_available($courseoffering_id);


if (!$courseofferingdes) {
    throw new AccessDeniedException("Course offering does not exist");
}

define('MENUITEM', 'courseofferings/subcourseofferings');
define('TITLE', $courseofferingdes->courseoffering_name);
}

$smarty = smarty();

if($listcourseofferings){
$courseofferingsperpage = 20;
$offset = (int)($offset / $courseofferingsperpage) * $courseofferingsperpage;

$results = get_courseofferings($courseofferingsperpage, $offset, $courseoffering_id, (int)$course_id);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'courseoffering/courseofferings.php?coursetemplate=' . $courseoffering_id,
    'count' => $results['count'],
    'limit' => $courseofferingsperpage,
    'offset' => $offset,
    'resultcounttextsingular' => 'courseoffering',
    'resultcounttextplural' => 'courseofferings',
));


printf($course_id);


//start -eshwari


//	$outcomedes = get_record('outcomes','id',$group->outcome);
	$samplesdes = get_record('course_template', 'id', $course_id);

	   
//var samples =$samplesdes->id;
printf((int)$samplesdes->id);


$smarty->assign('samplesdes',(int)$samplesdes->id);

$smarty->assign('courseofferings', $results['courseofferings']);

$smarty->assign('cancreate', can_create_courseofferings());
$smarty->assign('pagination', $pagination['html']);

}else{
$degrees = @get_records_sql_array(
    'SELECT id, coursetemplate_name
       FROM {course_template}'
	   
);


$smarty->assign('degrees', $degrees);

}




$smarty->assign('courseofferingid',$courseoffering_id);
$smarty->assign('main_courseoffering',$courseofferingdes->main_courseoffering);
$smarty->assign('offset',$offset);
if($courseoffering_id != 0){
$smarty->assign('COURSEOFFERINGNAV', courseoffering_get_menu_tabs($courseoffering_id));
$smarty->assign('PAGEHEADING', $courseofferingdes->courseoffering_name);
}else{
$smarty->assign('COURSEOFFERINGNAV','');
if($listcourseofferings){
	$smarty->assign('PAGEHEADING', 'Course Offerings');
}else{
	$smarty->assign('PAGEHEADING', 'Course Templates');
}
}
$smarty->display('courseoffering/courseofferings.tpl');



?>
