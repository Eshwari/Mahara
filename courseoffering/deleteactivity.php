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
define('MENUITEM', 'courseofferings/activities');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('courseoffering.php');

$courseoffering_id = param_integer('courseoffering');
$offset = param_integer('offset');
$activityid = param_integer('activity');
 
if (!can_create_courseofferings()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseoffering_rec = get_record('course_offering','id',$courseoffering_id);
 
if (!$courseoffering_rec) {
    throw new AccessDeniedException("Courseoffering does not exist");
}
 
$activity = is_activity_available($activityid);

if (!$activity) {
    throw new AccessDeniedException("Activity does not exist for the outcome");
}
 
define('TITLE', $courseoffering_rec->courseoffering_name);

$form = pieform(array(
    'name' => 'deletegroup',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'courseoffering/activities.php?courseoffering=' . $courseoffering_id. '&offset=' . $offset
        )
    ),
));

$smarty = smarty();
$smarty->assign('message', 'Do you want to delete activity - ' . $activity->activity_name);
$smarty->assign('form', $form);
$smarty->assign('main_courseoffering',$courseoffering_rec->main_courseoffering);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('header', 'Delete Activity');
$smarty->assign('COURSEOFFERINGNAV', courseoffering_get_menu_tabs($courseoffering_id));
$smarty->display('courseoffering/delete.tpl');
function deletegroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $courseoffering_id, $offset, $activityid;

     db_begin();
     update_record('add_activities',array('deleted' => 1), array('id' =>$activityid));
     db_commit();
    $SESSION->add_ok_msg('Activity deleted');
    redirect(get_config('wwwroot') . 'courseoffering/activities.php?courseoffering=' . $courseoffering_id . '&offset=' . $offset);
}
 
 
 
 
 
 
 
 
 ?>