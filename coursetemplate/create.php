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
require_once('coursetemplate.php');


$coursetemplate_id = param_integer('coursetemplate',0);
$offset = param_integer('offset',0);

if (!can_create_coursetemplates()) {
	
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$coursetemplatedes = is_coursetemplate_available($coursetemplate_id);

if (!$coursetemplatedes) {
    throw new AccessDeniedException("Course Template does not exist");
}

if($coursetemplate_id == 0){
	define('MENUITEM', 'coursetemplates/coursetemplates');
	define('TITLE', 'Create Course Template');
}else{
	define('MENUITEM', 'coursetemplates/subcoursetemplates');
	define('TITLE', $coursetemplatedes->coursetemplate_name);
}

$assesstype = array();
$assesstype['Level'] = 'Levels';
$assesstype['Meets/Does not meet'] = 'Meets/Does not meet';
//offering type
$offeringtype =array();
$offeringtype['Spring-14'] = 'Spring-14';
$offeringtype['Fall-14'] = 'Fall-14';
$offeringtype['Spring-15'] = 'Spring-15';
$offeringtype['Fall-15'] = 'Fall-15';
//college offering type
$collegeofferingtype =array();
$collegeofferingtype['Business, W. P. Carey School of Business'] ='Business, W. P. Carey School of Business';
$collegeofferingtype['Colleg of Technology and Innovation']= 'Colleg of Technology and Innovation'; 
$collegeofferingtype['Mary Lou Fulton'] = 'Mary Lou Fulton';
$collegeofferingtype['School of Letter and Science']='School of Letter and Science';

$degcourses = @get_records_sql_array(
	'SELECT id, course_name
		FROM {dept_courses}',
	array()
);

$courses = array();
$courses['Course'] = 'Select a course';
foreach($degcourses as $course){
	$courses[$course->id] = $course->course_name;
}
//course outcomes-Eshwari

$course_outs = @get_records_sql_array(
	'SELECT id, courseoutcome_name
		FROM {courseoutcomes}',
	array()
);

$course_outcomes =array();
$course_outcomes['courseoutcomes'] = 'Select course outcomes';
foreach($course_outs as $courseoutcomes){
	$course_outcomes[$courseoutcomes->id] = $courseoutcomes->courseoutcome_name;
}
$createcoursetemplate = array(
    'name'     => 'createcoursetemplate',
    'method'   => 'post',
    'elements' => array(),
);
        $createcoursetemplate['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'Course Template Name',
        );
        $createcoursetemplate['elements']['course_description'] = array(
            'type'         => 'wysiwyg',
            'title'        => 'Course Template Description',
            'rows'         => 10,
            'cols'         => 55,
        );
		$createcoursetemplate['elements']['collegeofferingtype'] = array(
            'type'         => 'select',
            'title'        => 'College Offering',
			'options'      =>  $collegeofferingtype,
			'defaultyvalue'=>  'Colleg of Technology and Innovation',
        );
		$createcoursetemplate['elements']['prerequisite'] = array(
            'type'         => 'wysiwyg',
            'title'        => 'Course Pre-requisites',
            'rows'         => 10,
            'cols'         => 55,
        );
		$createcoursetemplate['elements']['offeringtype'] = array(
            'type'         => 'select',
            'title'        => 'Offering Semester',
			'options'      =>  $offeringtype,
			'defaultyvalue'=>  'Spring-14',
        );

if($coursetemplate_id == 0){
	$disable = 0;
	$default = '';
}else{
	$disable = 1;
	$default = $coursetemplatedes->course_id;
}


       $createcoursetemplate['elements']['degree'] = array(
            'type'         => 'select',
            'title'        => 'Degree Course',
            'options'      => $courses,
		'disabled' 	   => $disable,
		'defaultvalue' => $default,
        );
		$createcoursetemplate['elements']['courseoutcome'] = array(
            'type'         => 'select',
            'title'        => 'Course outcomes',
            'options'      => $courseoutcomes,
			'disabled' 	   => $disable,
			'defaultvalue' => $default,
        );

        $createcoursetemplate['elements']['assesstype'] = array(
            'type'         => 'select',
            'title'        => 'Evaluation Type',
            'options'      => $assesstype,
            'defaultvalue' => 'Level',
        );
	  $createcoursetemplate['elements']['coursetemplateid'] = array(
		'type' => 'hidden',
		'value' => $coursetemplate_id,
	  );
        $createcoursetemplate['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );

$createcoursetemplate = pieform($createcoursetemplate);
$smarty = smarty();
$smarty->assign('createcoursetemplate', $createcoursetemplate);

$smarty->assign('main_coursetemplate',$coursetemplatedes->main_coursetemplate);
if($coursetemplate_id != 0){
$smarty->assign('COURSETEMPLATENAV', coursetemplate_get_menu_tabs($coursetemplate_id));
$smarty->assign('PAGEHEADING', $coursetemplatedes->coursetemplate_name);
$smarty->assign('header', 'Create Sub Course Template');
}else{
$smarty->assign('COURSETEMPLATENAV','');
$smarty->assign('PAGEHEADING', 'Create Course Template');
}

$smarty->display('coursetemplate/create.tpl');

function createcoursetemplate_validate(Pieform $form, $values) {
global $coursetemplate_id;
 	$errorval = '';
   if($values['name'] == ''){
	$errorval = "Course Template Name is mandatory";
   }

    if (get_field('course_template', 'id', 'degree_id', $values['degree'],'coursetemplate_name', $values['name'])) {
		$errorval = 'Course Template already exists for this course';
    }

	if($errorval){
 	$form->set_error('name', $errorval);
	}else {
   	if($coursetemplate_id == 0 && $_POST['degree'] == "Course"){
	$err = 'Please select a course';
	$form->set_error('degree',$err);
  	}
	}
}

function createcoursetemplate_cancel_submit(Pieform $form, $values) {
global $offset;
    redirect(get_config('wwwroot').'/coursetemplate/coursetemplates.php?coursetemplate=' . $_POST['coursetemplateid'] . '&offset=' . $offset);
}

function createcoursetemplate_submit(Pieform $form, $values) {
   global $USER;
    global $SESSION;
global $offset,$coursetemplate_id, $coursetemplatedes;
if($coursetemplate_id == 0){
	$degree = $_POST['degree'];
}else{
	$degree = $coursetemplatedes->degree_id;
}

$str= $_POST['course_description'];
$str2 =$_POST['prerequisite'];
/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/

db_begin();
$newcoursetemplate = insert_record('course_template', (object)array('coursetemplate_name' => $_POST['name'],'course_description' => $str,'prerequisite	' => $str2, 'degree_id' => $degree,'college_offering' =>$_POST['collegeofferingtype'], 'offering' => $_POST['offeringtype'],'eval_type' => $_POST['assesstype'], 'main_coursetemplate' => $_POST['coursetemplateid'], 'deleted' => 0),'id',true);
db_commit();


    $SESSION->add_ok_msg('Course Template Saved');

   if($_POST['assesstype'] == 'Level'){
    redirect(get_config('wwwroot').'coursetemplate/edit.php?coursetemplate=' . $newcoursetemplate. '&main_offset=' . $offset);
   }else{
    redirect(get_config('wwwroot').'/coursetemplate/coursetemplates.php?coursetemplate=' . $_POST['coursetemplateid']. '&offset=' . $offset);
   }

}

?>
