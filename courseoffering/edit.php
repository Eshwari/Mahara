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
require_once('courseoffering.php');

$courseoffering_id = param_integer('courseoffering',0);

$offset = param_integer('offset',0);

$main_offset = param_integer('main_offset',0);

if (!can_create_courseofferings()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseofferingdes = is_courseoffering_available($courseoffering_id);

if (!$courseofferingdes) {
    throw new AccessDeniedException("courseoffering does not exist");
}

if ($courseofferingdes->main_courseoffering != 0){
$main_courseoffering = is_courseoffering_available($courseofferingdes->main_courseoffering);

if (!$main_courseoffering) {
    throw new AccessDeniedException("courseoffering does not exist");
}
	define('MENUITEM', 'courseofferings/subcourseofferings');

}else{
	define('MENUITEM', 'courseofferings/courseofferings');
}

define('TITLE', $courseofferingdes->courseoffering_name);

// semester offering
$semestertype = array();
$semestertype['semestertype']= 'Select a Semester';
$semestertype['Spring-2014'] ='Spring-2014';
$semestertype['Fall-2014'] = 'Fall-2014';
$semestertype['Spring-2015'] ='Spring-2015';
$semestertype['Fall-2015'] ='Spring-2015';


$degcourses = @get_records_sql_array(
	'SELECT id, course_name
		FROM {dept_courses}',
	array()
);

$courses = array();
$courses['Course'] = 'Select a Course';
foreach($degcourses as $course){
	$courses[$course->id] = $course->course_name;
}


$createcourseoffering = array(
    'name'     => 'createcourseoffering',
    'method'   => 'post',
    'elements' => array(),
);
        $createcourseoffering['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'courseoffering Name',
		'defaultvalue' => $courseofferingdes->courseoffering_name,
        );
		$createcourseoffering['elements']['Instructor1'] = array(
            'type'         => 'text',
            'title'        => 'Instructor 1',
			'defaultvalue' => $courseofferingdes->Instructor1,
        );
		$createcourseoffering['elements']['Instructor2'] = array(
            'type'         => 'text',
            'title'        => 'Instructor 2',
			'defaultvalue' => $courseofferingdes->Instructor2,
        );
		$createcourseoffering['elements']['semestertype'] = array(
            'type'         => 'select',
            'title'        => 'Semester Term',
			'options'      =>  $semestertype,
			'defaultvalue'=>  $courseofferingdes->semester,
        );
		
			
		
		
if($courseofferingdes->main_courseoffering == 0){
	$disable = 0;
}else{
	$disable = 1;
}
       $createcourseoffering['elements']['coursetemplate'] = array(
            'type'         => 'text',
            'title'        => 'Course Template',
 		   // 'options'      => $coursetemplates,
		    'defaultvalue' => $coursetemplatedes->coursetmp_id,
			);
	
		
        
	  $createcourseoffering['elements']['courseofferingid'] = array(
		'type' => 'hidden',
		'value' => $courseoffering_id,
	  );
	  $createcourseoffering['elements']['maincourseoffering'] = array(
		'type' => 'hidden',
		'value' => $courseofferingdes->main_courseoffering,
	  );
        $createcourseoffering['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );

$createcourseoffering = pieform($createcourseoffering);
$smarty = smarty();
$smarty->assign('createcourseoffering', $createcourseoffering);


$smarty->assign('main_courseoffering',$main_courseoffering->main_courseoffering);

if($courseofferingdes->main_courseoffering != 0){
$smarty->assign('COURSEOFFERINGNAV', courseoffering_get_menu_tabs($courseofferingdes->main_courseoffering));
$smarty->assign('PAGEHEADING', $main_courseoffering->courseoffering_name);
$smarty->assign('header', 'Edit Sub courseoffering');
}else{
$smarty->assign('COURSEOFFERINGNAV','');
$smarty->assign('PAGEHEADING', 'Edit courseoffering');
$smarty->assign('header', '');

}
$smarty->display('courseoffering/edit.tpl');

function createcourseoffering_validate(Pieform $form, $values) {
global $courseoffering_id,$courseofferingdes;

 	$errorval = '';
   if($values['name'] == ''){
	$errorval = "courseoffering Name is mandatory";
   }

if($courseofferingdes->main_courseoffering){

	$degree_id = $courseofferingdes->degree_id;
}else{
	$degree_id = $_POST['degree'];
}

 	$dupid = get_record('course_template', 'degree_id', $degree_id,'courseoffering_name', $_POST['name']);
    if ($dupid != '' && $dupid->id != $courseoffering_id) {
		$errorval = 'courseoffering already exists for this course';
    }

	if($errorval){
 	$form->set_error('name', $errorval);
	}else if(!$courseofferingdes->main_courseoffering) {
   	if($_POST['degree'] == "Course"){
	$err = 'Please select a course';
	$form->set_error('degree',$err);
  	}
	/*
	if($_POST['degree'] == "Course"){
	$err = 'Please select a course';
	$form->set_error('degree',$err);
  	}
*/
    }
 }

function createcourseoffering_cancel_submit(Pieform $form, $values) {
global $main_offset;
    redirect(get_config('wwwroot').'/courseoffering/courseofferings.php?courseoffering=' . $_POST['maincourseoffering'] . '&offset=' . $main_offset);
}

function createcourseoffering_submit(Pieform $form, $values) {
    global $USER;
    global $SESSION;
global $courseofferingdes, $main_offset, $offset;
db_begin();

if($_POST['degree'] != $courseofferingdes->degree_id){
	$primaryval = NULL;
}else{
	$primaryval = $courseofferingdes->special_access;
}

if($courseofferingdes->main_courseoffering){
	$degree_id = $courseofferingdes->degree_id;
}else{
	$degree_id = $_POST['degree'];
}
$str= $_POST['description'];
/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/
update_record('course_template', array('courseoffering_name' => $_POST['name'],'description' => $str, 'degree_id' => $degree_id,'college_offering' =>$_POST['collegeofferingtype'],'dept_offering' =>$_POST['deptofferingtype'], 'special_access' => $primaryval,'prerequisite_type' => $_POST['prerequisitetype']),array('id' => $_POST['courseofferingid']));
db_commit();

    $SESSION->add_ok_msg('Course Template Updated');

    redirect(get_config('wwwroot').'courseoffering/edit.php?courseoffering=' . $_POST['courseofferingid'] . '&offset=' .$offset . '&main_offset=' .$main_offset);

}






?>
