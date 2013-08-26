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
require_once('createcourse.php');


$createcourse_id = param_integer('courseout',0);
$offset = param_integer('offset',0);

if (!can_create_courses()) {
	
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$createcoursedes = is_createcourse_available($createcourse_id);

if (!$createcoursedes) {
    throw new AccessDeniedException("Course does not exist");
}

if($createcourse_id == 0){
	define('MENUITEM', 'createcourse/createcourse');
	define('TITLE', 'Create Courses');
}else{
	define('MENUITEM', 'createcourse/createcourse');
	define('TITLE', $createcoursedes->createcourse_name);
}

$assesstype = array();
$assesstype['Level'] = 'Levels';
$assesstype['Meets/Does not meet'] = 'Meets/Does not meet';

$degcourses = @get_records_sql_array(
	'SELECT id, course_name
		FROM {dept_courses}',
	array()
);

$degrees = array();
$degrees['Degree'] = 'Select a degree';
foreach($degcourses as $degree){
	$degrees[$degree->id] = $degree->course_name;
}

$createcourse = array(
    'name'     => 'createcourse',
    'method'   => 'post',
    'elements' => array(),
);
        $createcourse['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'Course Name',
        );
        $createcourse['elements']['description'] = array(
            'type'         => 'wysiwyg',
            'title'        => 'Course Description',
            'rows'         => 10,
            'cols'         => 55,
        );

if($createcourse_id == 0){
	$disable = 0;
	$default = '';
}else{
	$disable = 1;
	$default = $createcoursedes->course_id;
}
       $createcourse['elements']['course'] = array(
            'type'         => 'select',
            'title'        => 'Graduate Degree ',
            'options'      => $degrees,
		'disabled' 	   => $disable,
		'defaultvalue' => $default,
        );

        $createcourse['elements']['assesstype'] = array(
            'type'         => 'select',
            'title'        => 'Evaluation Type',
            'options'      => $assesstype,
            'defaultvalue' => 'Level',
        );
	  $createcourse['elements']['createcourseid'] = array(
		'type' => 'hidden',
		'value' => $createcourse_id,
	  );
        $createcourse['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );

$createcourse = pieform($createcourse);
$smarty = smarty();
$smarty->assign('createcourse', $createcourse);

$smarty->assign('main_course',$createcoursedes->main_course);
if($createcourse_id != 0){
$smarty->assign('CREATECOURSENAV', createcourse_get_menu_tabs($createcourse_id));
$smarty->assign('PAGEHEADING', $createcoursedes->createcourse_name);
$smarty->assign('header', 'Create Sub Course');
}else{
$smarty->assign('COURSEOUTCOMENAV','');
$smarty->assign('PAGEHEADING', 'Create Course');
}

$smarty->display('createcourse/create.tpl');

function createcourse_validate(Pieform $form, $values) {
global $createcourse_id;
 	$errorval = '';
   if($values['name'] == ''){
	$errorval = "Course Name is mandatory";
   }

    if (get_field('createcourse', 'id', 'course_id', $values['course'],'course_name', $values['name'])) {
		$errorval = 'Course already exists ';
    }

	if($errorval){
 	$form->set_error('name', $errorval);
	}else {
   	if($createcourse_id == 0 && $_POST['course'] == "Degree"){
	$err = 'Please select a degree';
	$form->set_error('course',$err);
  	}
	}
}

function createcourse_cancel_submit(Pieform $form, $values) {
global $offset;
    redirect(get_config('wwwroot').'/createcourse/createcourses.php?createcourse=' . $_POST['createcourseid'] . '&offset=' . $offset);
}

function createcourse_submit(Pieform $form, $values) {
   global $USER;
    global $SESSION;
global $offset,$createcourse_id, $createcoursedes;
if($createcourse_id == 0){
	$course = $_POST['course'];
}else{
	$course = $createcoursedes->course_id;
}

$str= $_POST['description'];
/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/

db_begin();
$newcourse = insert_record('createcourses', (object)array('course_name' => $_POST['name'],'description' => $str, 'course_id' => $degree, 'eval_type' => $_POST['assesstype'], 'main_course' => $_POST['createcourseid'], 'deleted' => 0),'id',true);
db_commit();


    $SESSION->add_ok_msg('Course Saved');

   if($_POST['assesstype'] == 'Level'){
    redirect(get_config('wwwroot').'createcourse/edit.php?createcourse=' . $newcourse. '&main_offset=' . $offset);
   }else{
    redirect(get_config('wwwroot').'/createcourse/createcourses.php?createcourse=' . $_POST['createcourseid']. '&offset=' . $offset);
   }

}

?>
