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
require('coursetemplate.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'course templates');
$offset = param_integer('offset', 'all');

$coursetemplate_id = param_integer('coursetemplate',0);

$course_id = param_integer('course',0);

if(!$USER->get('staff') && !$USER->get('admin')){
	$usr_rec = get_record('usr','id',$USER->get('id'));
	if (!$usr_rec->degree_id) {
    		throw new AccessDeniedException("Not a valid degree course");
	}
	$course_id = $usr_rec->degree_id;
}

if($coursetemplate_id == 0){
define('MENUITEM', 'coursetemplates/coursetemplates');
define('TITLE', 'Course Templates ');

if($course_id == 0){
	$listcoursetemplates = 0;
}else{
	$course_rec = get_record('dept_courses','id',$course_id);
	if (!$course_rec) {
    		throw new AccessDeniedException("Not a valid degree course");
	}
	$listcoursetemplates = 1;
}
}else{

$listcoursetemplates = 1;
$coursetemplatedes = is_coursetemplate_available($coursetemplate_id);

if (!$coursetemplatedes) {
    throw new AccessDeniedException("Course Templates does not exist");
}

define('MENUITEM', 'coursetemplates/subcoursetemplates');
define('TITLE', $coursetemplatedes->coursetemplate_name);
}

$smarty = smarty();
if($listcoursetemplates){
$coursetemplatesperpage = 20;
$offset = (int)($offset / $coursetemplatesperpage) * $coursetemplatesperpage;

$results = get_coursetemplates($coursetemplatesperpage, $offset, $coursetemplate_id, (int)$course_id);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'coursetemplate/coursetemplates.php?coursetemplate=' . $coursetemplate_id,
    'count' => $results['count'],
    'limit' => $coursetemplatesperpage,
    'offset' => $offset,
    'resultcounttextsingular' => 'coursetemplate',
    'resultcounttextplural' => 'coursetemplates',
));

$smarty->assign('coursetemplates', $results['coursetemplates']);
$smarty->assign('cancreate', can_create_coursetemplates());
$smarty->assign('pagination', $pagination['html']);
}else{
$degrees = @get_records_sql_array(
    'SELECT id, course_name
       FROM {dept_courses}'
);
$smarty->assign('degrees', $degrees);

}

$smarty->assign('coursetemplateid',$coursetemplate_id);
$smarty->assign('main_coursetemplate',$coursetemplatedes->main_coursetemplate);
$smarty->assign('offset',$offset);
if($coursetemplate_id != 0){
$smarty->assign('COURSETEMPLATENAV', coursetemplate_get_menu_tabs($coursetemplate_id));
$smarty->assign('PAGEHEADING', $coursetemplatedes->coursetemplate_name);
}else{
$smarty->assign('COURSETEMPLATENAV','');
if($listcoursetemplates){
	$smarty->assign('PAGEHEADING', 'Course Templates');
}else{
	$smarty->assign('PAGEHEADING', 'Graduate Program');
}
}
$smarty->display('coursetemplate/coursetemplates.tpl');



?>
