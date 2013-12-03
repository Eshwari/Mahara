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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require('courseoffering.php');
$courseoffering_id = param_integer('courseoffering');
$offset = param_integer('offset');


if (!can_create_courseofferings()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseoffering_rec = is_courseoffering_available($courseoffering_id);

if($courseoffering_id == 0 || !$courseoffering_rec){
	throw new AccessDeniedException('Course Offering does not exist');
}

if($courseoffering_rec->main_courseoffering == 0){
	define('MENUITEM', 'courseofferings/courseofferings');
}else{
$main_courseoffering = is_courseoffering_available($courseoffering_rec->main_courseoffering);

if (!$main_courseoffering) {
    throw new AccessDeniedException("Course Offering does not exist");
}
	define('MENUITEM', 'courseofferings/subcourseofferings');
}

define('TITLE', $courseoffering_rec->courseoffering_name);

$form = pieform(array(
    'name' => 'deletecourseoffering',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'courseoffering/courseofferings.php?courseoffering=' . $courseoffering_rec->main_courseoffering . '&offset=' . $offset
        )
    ),
));

$smarty = smarty();
$smarty->assign('subheading', hsc(TITLE));
$smarty->assign('message', 'Do you want to delete the Course Offering - ' . $courseoffering_rec->courseoffering_name);
$smarty->assign('form', $form);
$smarty->assign('main_outcome',$main_courseoffering->main_courseoffering);
if($courseoffering_rec->main_courseoffering != 0){
$smarty->assign('COURSEOFFERINGNAV', courseoffering_get_menu_tabs($courseoffering_rec->main_courseoffering));
$smarty->assign('PAGEHEADING', $main_courseoffering->courseoffering_name);
$smarty->assign('header', 'Delete Sub courseoffering');
}else{
$smarty->assign('COURSEOFFERINGNAV','');
$smarty->assign('PAGEHEADING', 'Delete Course Offering');
$smarty->assign('header', '');
}

$smarty->display('courseoffering/delete.tpl');

function deletecourseoffering_submit(Pieform $form, $values) {
    global $SESSION, $USER, $courseoffering_id, $levelid, $offset, $courseoffering_rec;

$subcourseofferings = @get_records_sql_array(
    'SELECT id
       FROM {course_offering}
       WHERE main_courseoffering = ?
	 AND deleted = 0',
    array($courseoffering_rec->id)
);
     db_begin();
     update_record('course_offering',array('deleted' => 1), array('id' =>$courseoffering_rec->id));

foreach($subcourseofferings as $subcourseoffering){
     update_record('course_offering',array('deleted' => 1), array('id' =>$subcourseoffering->id));
}
     db_commit();
    $SESSION->add_ok_msg('Course Offering Deleted');
    redirect(get_config('wwwroot') . 'courseoffering/courseofferings.php?courseoffering=' . $courseoffering_rec->main_courseoffering. '&offset=' . $offset);
}
?>
