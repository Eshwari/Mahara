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
require_once('courseoutcome.php');

$courseoutcome_id = param_integer('courseoutcome',0);
$offset = param_integer('offset',0);

if (!can_create_courseoutcomes()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseoutcomedes = is_courseoutcome_available($courseoutcome_id);

if (!$courseoutcomedes) {
    throw new AccessDeniedException("Course Outcome does not exist");
}

if($courseoutcome_id == 0){
	define('MENUITEM', 'courseoutcomes/courseoutcomes');
	define('TITLE', 'Create Course Outcome');
}else{
	define('MENUITEM', 'courseoutcomes/subcourseoutcomes');
	define('TITLE', $courseoutcomedes->courseoutcome_name);
}

$assesstype = array();
$assesstype['Level'] = 'Levels';
$assesstype['Meets/Does not meet'] = 'Meets/Does not meet';

$degcourses = @get_records_sql_array(
	'SELECT id, degree_name
		FROM {degree_courses}',
	array()
);

$courses = array();
$courses['Course'] = 'Select a course';
foreach($degcourses as $course){
	$course[$course->id] = $course->degree_name;
}

$createcourseoutcome = array(
    'name'     => 'createcourseoutcome',
    'method'   => 'post',
    'elements' => array(),
);
        $createcourseoutcome['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'Course Outcome Name',
        );
        $createcourseoutcome['elements']['description'] = array(
            'type'         => 'wysiwyg',
            'title'        => 'Course Outcome Description',
            'rows'         => 10,
            'cols'         => 55,
        );

if($courseoutcome_id == 0){
	$disable = 0;
	$default = '';
}else{
	$disable = 1;
	$default = $courseoutcomedes->degree_id;
}
       $createcourseoutcome['elements']['degree'] = array(
            'type'         => 'select',
            'title'        => 'Degree Program',
            'options'      => $courses,
		'disabled' 	   => $disable,
		'defaultvalue' => $default,
        );

        $createcourseoutcome['elements']['assesstype'] = array(
            'type'         => 'select',
            'title'        => 'Evaluation Type',
            'options'      => $assesstype,
            'defaultvalue' => 'Level',
        );
	  $createcourseoutcome['elements']['courseoutcomeid'] = array(
		'type' => 'hidden',
		'value' => $courseoutcome_id,
	  );
        $createcourseoutcome['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );

$createcourseoutcome = pieform($createcourseoutcome);
$smarty = smarty();
$smarty->assign('createcourseoutcome', $createcourseoutcome);

$smarty->assign('main_courseoutcome',$courseoutcomedes->main_courseoutcome);
if($courseoutcome_id != 0){
$smarty->assign('OUTCOMENAV', courseoutcome_get_menu_tabs($courseoutcome_id));
$smarty->assign('PAGEHEADING', $courseoutcomedes->courseoutcome_name);
$smarty->assign('header', 'Create Sub Course Outcome');
}else{
$smarty->assign('OUTCOMENAV','');
$smarty->assign('PAGEHEADING', 'Create Course Outcome');
}

$smarty->display('courseoutcome/courseoutcomecreate.tpl');

function createcourseoutcome_validate(Pieform $form, $values) {
global $courseoutcome_id;
 	$errorval = '';
   if($values['name'] == ''){
	$errorval = "Course Outcome Name is mandatory";
   }

    if (get_field('courseoutcomes', 'id', 'degree_id', $values['degree'],'courseoutcome_name', $values['name'])) {
		$errorval = 'Course Outcome already exists for this course';
    }

	if($errorval){
 	$form->set_error('name', $errorval);
	}else {
   	if($courseoutcome_id == 0 && $_POST['degree'] == "Program"){
	$err = 'Please select a course';
	$form->set_error('degree',$err);
  	}
	}
}

function createcourseoutcome_cancel_submit(Pieform $form, $values) {
global $offset;
    redirect(get_config('wwwroot').'/courseoutcome/courseoutcomes.php?courseoutcome=' . $_POST['courseoutcomeid'] . '&offset=' . $offset);
}

function createcourseoutcome_submit(Pieform $form, $values) {
   global $USER;
    global $SESSION;
global $offset,$courseoutcome_id, $courseoutcomedes;
if($courseoutcome_id == 0){
	$degree = $_POST['degree'];
}else{
	$degree = $courseoutcomedes->degree_id;
}

$str= $_POST['description'];
/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/

db_begin();
$newcourseoutcome = insert_record('courseoutcomes', (object)array('courseoutcome_name' => $_POST['name'],'description' => $str, 'degree_id' => $degree, 'eval_type' => $_POST['assesstype'], 'main_courseoutcome' => $_POST['courseoutcomeid'], 'deleted' => 0),'id',true);
db_commit();


    $SESSION->add_ok_msg('Course Outcome Saved');

   if($_POST['assesstype'] == 'Level'){
    redirect(get_config('wwwroot').'courseoutcome/edit.php?courseoutcome=' . $newcourseoutcome. '&main_offset=' . $offset);
   }else{
    redirect(get_config('wwwroot').'/courseoutcome/courseoutcomes.php?courseoutcome=' . $_POST['courseoutcomeid']. '&offset=' . $offset);
   }

}

?>
