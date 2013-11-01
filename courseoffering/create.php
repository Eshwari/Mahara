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



if (!can_create_courseofferings()) {
	
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$courseofferingdes = is_courseoffering_available($courseoffering_id);

if (!$courseofferingdes) {
    throw new AccessDeniedException("Course Offering does not exist");
}

if($courseoffering_id == 0){
	define('MENUITEM', 'courseofferings/courseofferings');
	define('TITLE', 'Create Course Offering');
}else{
	define('MENUITEM', 'courseofferings/subcourseofferings');
	define('TITLE', $courseofferingdes->courseoffering_name);
}
//start-Eshwari
$course_id = param_integer('coursetemplate',0);
printf($course_id);
$coursetemplatedes = get_coursetemplate_name($course_id);
//end-eshwari

printf($course_id);


//college offering type
$collegeofferingtype =array();
$collegeofferingtype ['Collegeofferingtype'] = 'Select a Collage';
$collegeofferingtype['Business, W. P. Carey School of Business'] ='Business, W. P. Carey School of Business';
$collegeofferingtype['Colleg of Technology and Innovation']= 'Colleg of Technology and Innovation'; 
$collegeofferingtype['Mary Lou Fulton'] = 'Mary Lou Fulton';
$collegeofferingtype['School of Letter and Science']='School of Letter and Science';
//department offering type
$deptofferingtype = array();
$deptofferingtype['Deptofferingtype'] = 'Select a Department';
$deptofferingtype['Engineering and computing'] ='Engineering and computing';
$deptofferingtype['Applied Science'] ='Applied Science';
$deptofferingtype['Industry Management']= 'Industry Management';
// semester offering
$semestertype = array();
$semestertype['semestertype']= 'Select a Semester';
$semestertype['Spring-2014'] ='Spring-2014';
$semestertype['Fall-2014'] = 'Fall-2014';
$semestertype['Spring-2015'] ='Spring-2015';
$semestertype['Fall-2015'] ='Spring-2015';

//pre-requisite
$prerequisitetype= array();
//$prerequisitetype['prerequisitetype'] ='Select one';
$prerequisitetype['Prereq'] ='Prereqs';
$prerequisitetype['NoPrereq'] = 'NoPrereq';
printf($course_id);
$coursetmps = @get_records_sql_array(
	'SELECT id, coursetemplate_name
		FROM {course_template}
		WHERE id = course_id',
	array()
);
/*
$coursetemplates = array();
//$coursetemplates['CourseTemplate'] = 'Select a Course Template';
foreach($coursetmps as $coursetmp){
	$coursetemplates[$coursetmp->id] = $coursetmp->coursetemplate_name;
}
*/
 
$createcourseoffering = array(
    'name'     => 'createcourseoffering',
    'method'   => 'post',
    'elements' => array(),
);
        $createcourseoffering['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'Course Offering Name',
        );
       
		$createcourseoffering['elements']['Instructor1'] = array(
            'type'         => 'text',
            'title'        => 'Instructor 1',
        );
		$createcourseoffering['elements']['Instructor1'] = array(
            'type'         => 'text',
            'title'        => 'Instructor 2',
        );
		$createcourseoffering['elements']['semestertype'] = array(
            'type'         => 'select',
            'title'        => 'Semester Term',
			'options'      =>  $semestertype,
			'defaultyvalue'=>  'Select a Semester',
        );
		/* $createcourseoffering['elements']['coursetemplate'] = array(
            'type'         => 'select',
            'title'        => 'Course Template',
            'options'      => $coursetemplates,
			'defaultvalue' => $coursetemplatedes->coursetemplate_name,
        );
		*/
		$createcourseoffering['elements']['coursetemplate'] = array(
            'type'         => 'text',
           'title'        => 'Course Template',
		   'options'      => $coursetemplates,
		   'defaultvalue' => $coursetemplatedes->coursetemplate_name,
        );
		 
		

if($courseoffering_id == 0){
	$disable = 0;
	$default = '';
}else{
	$disable = 1;
	$default = $courseofferingdes->coursetmp_id;
}


      
/*
$createcourseoffering['elements']['prerequisitetype'] = array(
            'type'         => 'select',
            'title'        => 'Indicate Pre-requisites exist for the Course',
            'options'      => $prerequisitetype,
           // 'defaultvalue' => 'prerequisitetype',
		   'defaultvalue' => 'Prereq',
        );*/

      
	  $createcourseoffering['elements']['courseofferingid'] = array(
		'type' => 'hidden',
		'value' => $courseoffering_id,
	  );
        $createcourseoffering['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );

$createcourseoffering = pieform($createcourseoffering);
$smarty = smarty();
$smarty->assign('createcourseoffering', $createcourseoffering);

$smarty->assign('main_courseoffering',$courseofferingdes->main_courseoffering);
if($courseoffering_id != 0){
$smarty->assign('COURSEOFFERINGNAV', courseoffering_get_menu_tabs($courseoffering_id));
$smarty->assign('PAGEHEADING', $courseofferingdes->courseoffering_name);
$smarty->assign('header', 'Create Sub Course Template');
}else{
$smarty->assign('COURSEOFFERINGNAV','');
$smarty->assign('PAGEHEADING', 'Create Course Offering');
}

$smarty->display('courseoffering/create.tpl');

function createcourseoffering_validate(Pieform $form, $values) {
global $courseoffering_id;
 	$errorval = '';
   if($values['name'] == ''){
	$errorval = "Course Offering Name is mandatory";
   }

    if (get_field('course_offering', 'id', 'coursetmp_id', $values['coursetemplate'],'courseoffering_name', $values['name'])) {
		$errorval = 'Course Template already exists for this course';
    }

	if($errorval){
 	$form->set_error('name', $errorval);
	}else {
   	if($courseoffering_id == 0 && $_POST['coursetemplate'] == "CourseTemplate"){
	$err = 'Please select a coursetemplate';
	$form->set_error('coursetemplate',$err);
  	}
	}
}

function createcourseoffering_cancel_submit(Pieform $form, $values) {
global $offset;
    redirect(get_config('wwwroot').'/courseoffering/courseofferings.php?courseoffering=' . $_POST['courseofferingid'] . '&offset=' . $offset);
}

function createcourseoffering_submit(Pieform $form, $values) {
   global $USER;
    global $SESSION;
global $offset,$courseoffering_id, $courseofferingdes;
if($courseoffering_id == 0){
	$coursetemplate = $_POST['coursetemplate'];
}else{
	$coursetemplate = $courseofferingdes->coursetmp_id;
}

//$str= $_POST['description'];

/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/

db_begin();
$newcourseoffering = insert_record('course_offering', (object)array('courseoffering_name' => $_POST['name'],'Instrcutor1' =>$_POST[Instrcutor1],'Instrcutor2' =>$_POST[Instrcutor2],'coursetmp_id' => $coursetemplate,'semester' =>$_POST['semestertype'], 'main_courseoffering' => $_POST['courseofferingid'], 'deleted' => 0),'id',true);
db_commit();


    $SESSION->add_ok_msg('Course Offering Saved');
	print("Lets start debugging");

   if($_POST['prerequisitetype'] == 'Prereq'){
    redirect(get_config('wwwroot').'courseoffering/edit.php?courseoffering=' . $newcourseoffering. '&main_offset=' . $offset);
	}else{
    redirect(get_config('wwwroot').'/courseoffering/courseofferings.php?courseoffering=' . $_POST['courseofferingid']. '&offset=' . $offset);
   }

}

?>
