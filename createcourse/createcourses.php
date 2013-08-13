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
require('createcourse.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'create courses');
$offset = param_integer('offset', 'all');

$createcourse_id = param_integer('createcourse',0);

$degree_id = param_integer('degree',0);

if(!$USER->get('staff') && !$USER->get('admin')){
	$usr_rec = get_record('usr','id',$USER->get('id'));
    if (!$usr_rec->course_id) {
    		throw new AccessDeniedException("Not a valid course");
	}
     $degree_id = $usr_rec->course_id;
}

if($createcourse_id == 0){
define('MENUITEM', 'createcourse/createcourse');
define('TITLE', 'Create Course ');

if($degree_id == 0){
	$listcourse = 0;
	}else{
	$course_rec = get_record('dept_course','id',$degree_id);
	if (!$course_rec) {
    		throw new AccessDeniedException("Not a valid course");
	}
	$listcourse = 1;
}
}else{

$listcourse = 1;
$createcoursedes = is_createcourse_available($createcourse_id);

if (!$createcoursedes) {
    throw new AccessDeniedException("Course does not exist");
}
define('MENUITEM', 'createcourse/subcourse');
define('TITLE', $createcoursedes->course_name);
}

$smarty = smarty();
if($listcourse){
$courseperpage = 20;
$offset = (int)($offset / $courseperpage) * $courseperpage;

$results = get_createcourse($createcourseperpage, $offset, $createcourse_id, (int)$degree_id);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'createcourse/createcourses.php?degree=' . $createcourse_id,
    'count' => $results['count'],
    'limit' => $courseperpage,
    'offset' => $offset,
    'resultcounttextsingular' => 'createcourse',
    'resultcounttextplural' => 'createcourses',
));
$smarty->assign('createcourses', $results['createcourses']);
$smarty->assign('cancreate', can_create_courses());
$smarty->assign('pagination', $pagination['html']);
}else{
$courses = @get_records_sql_array(
    'SELECT id, course_name
       FROM {dept_courses}'
);
$smarty->assign('courses', $courses);

}

$smarty->assign('createcourseid',$createcourse_id);
$smarty->assign('main_course',$createcoursedes->main_course);
$smarty->assign('offset',$offset);
if($createcourse_id != 0){
$smarty->assign('CREATECOURSENAV', createcourse_get_menu_tabs($createcourse_id));
$smarty->assign('PAGEHEADING', $createcoursedes->course_name);
}else{
$smarty->assign('CREATECOURSENAV','');
if($listcourse){
	$smarty->assign('PAGEHEADING', 'Create Course');
}else{
	$smarty->assign('PAGEHEADING', 'Graduate Degrees');
}
}
$smarty->display('createcourse/createcourses.tpl');
?>