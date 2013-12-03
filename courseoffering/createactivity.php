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
define('SECTION_PAGE', 'activities');
require_once('courseoffering.php');
$courseoffering_id = param_integer('courseoffering',0);
$offset = param_integer('offset',0);

if (!can_create_courseofferings()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseofferingdes = is_courseoffering_available($courseoffering_id);

if (!$courseofferingdes) {
    throw new AccessDeniedException("Course Offering does not exist");
}
define('TITLE', $courseofferingdes->courseoffering_name);

$createactivity = array(
    'name'     => 'createactivity',
    'method'   => 'post',
    'elements' => array(),
);
        $createactivity['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'Activity Name',
        );
		$createactivity['elements']['description'] = array(
             'type'         => 'wysiwyg',
            'title'        => 'Activity Name',
			 'rows'         => 10,
            'cols'         => 55,
        );
        $createactivity['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );

$createactivity = pieform($createactivity);
$smarty = smarty();
$smarty->assign('createactivity', $createactivity);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('header', 'Create Activity');
$smarty->assign('main_courseoffering',$courseofferingdes->main_courseoffering);
$smarty->assign('COURSEOFFERINGNAV', courseoffering_get_menu_tabs($courseoffering_id));
$smarty->display('courseoffering/createactivity.tpl');

function createactivity_validate(Pieform $form, $values) {

 	$errorval = '';
   if($values['name'] == ''){
	$errorval = "Activity Name is mandatory";
   }
   
   if (get_field('add_activities', 'id', 'activity_name', $values['name'])) {
		$errorval = 'Activtiy already exists for this Course offering';
    }
	
	if($errorval){
 	$form->set_error('name', $errorval);
	}
}

function createactivity_cancel_submit(Pieform $form, $values) {
global $offset, $courseoffering_id;
    redirect(get_config('wwwroot').'/courseoffering/activities.php?courseoffering=' . $courseoffering_id . '&offset=' . $offset);
}

function createactivity_submit(Pieform $form, $values) {
   global $USER;
    global $SESSION;
	global $offset,$courseoffering_id, $courseofferingdes;

    $activities =  @get_records_sql_array(
	'SELECT activity_no 
		FROM {add_activities}
		WHERE courseoffering_id = ?
		AND activity_no != 0',
	array($courseoffering_id)
    );
$max = 0;
foreach($activities as $activity){
	if($activity->activity_no > $max){
		$max = $activity->activity_no;
	}
}
$new_activity_no = $max + 1;
$str= $_POST['description'];
db_begin();
$newactivity = insert_record('add_activities', (object)array('courseoffering_id' => $courseoffering_id, 'activity_name' => $_POST['name'],'description' => $str, 'activity_no' => $new_activity_no, 'deleted' => 0),'id',true);
db_commit();

 $SESSION->add_ok_msg('Activity Saved' . $new_activity_no);
 redirect(get_config('wwwroot').'courseoffering/activities.php?courseoffering=' . $courseoffering_id . '&offset=' . $offset);

}























?>