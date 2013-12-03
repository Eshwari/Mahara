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

$courseoffering_id = param_integer('courseoffering',0);
$offset = param_integer('offset',0);

$activityid = param_integer('activity',0);

$act_offset = param_integer('act_offset',0);

if (!can_create_courseofferings()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseofferingdes = is_courseoffering_available($courseoffering_id);

if (!$courseofferingdes) {
    throw new AccessDeniedException("Courseoffering does not exist");
}

define('TITLE', $courseofferingdes->courseoffering_name);

$activity = is_activity_available($activityid);

if (!$activity) {
    throw new AccessDeniedException("Activity does not exist for the outcome");
}

$createactivity = array(

    'name'     => 'createactivity',
    'method'   => 'post',
    'elements' => array(),
);
        $createactivity['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'Activity Name',
			'defaultvalue' => $activity->activity_name,
        );
		$createactivity['elements']['description'] = array(
             'type'         => 'wysiwyg',
             'title'        => 'Activity Name',
			 'rows'         => 10,
             'cols'         => 55,
			 'defaultvalue' => $activity->description,
        );
        $createactivity['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );


$createactivity = pieform($createactivity);
$smarty = smarty();
$smarty->assign('createactivity', $createactivity);
$smarty->assign('main_courseoffering',$courseofferingdes->main_courseoffering);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('heading', 'Edit Activity');
$smarty->assign('COURSEOFFERINGNAV', courseoffering_get_menu_tabs($courseoffering_id));
$smarty->display('courseoffering/editactivity.tpl');

function createactivity_validate(Pieform $form, $values) {
 	$errorval = '';
	   if($values['name'] == ''){
		$errorval = "Activity Name is mandatory";
	   }

	if($errorval){
 	$form->set_error('name', $errorval);
	}
}

function createactivity_cancel_submit(Pieform $form, $values) {
global $activity, $act_offset;
    redirect(get_config('wwwroot').'/courseoffering/activities.php?courseoffering=' . $activity->courseoffering_id . '&offset=' . $act_offset);
}

function createactivity_submit(Pieform $form, $values) {
   global $USER;
   global $SESSION;
   global $activity, $offset, $act_offset;
   $str= $_POST['description'];
db_begin();

update_record('add_activities', array('activity_name' => $_POST['name'],'description' => $str), array('id' => $activity->id));

db_commit();

    $SESSION->add_ok_msg('Activity Updated');

    //redirect(get_config('wwwroot').'courseoffering/editactivity.php?courseoffering=' . $activity->courseoffering_id . '&activity=' . $activity->id . '&act_offset=' .$act_offset . '&offset=' . $offset);
     redirect(get_config('wwwroot').'courseoffering/activities.php?courseoffering=' . $activity->courseoffering_id . '&offset=' . $offset);
}











?>