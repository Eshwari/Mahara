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

$tmpl_id = param_integer('coursetemplate',0);

$coursetemplatedes = get_coursetemplate_name($tmpl_id);
$sampledes =get_courseoutcomes($tmpl_id);
$outcomelists= @get_records_sql_array('SELECT  c.id FROM {courseoutcomes} c INNER JOIN {course_template} ct 
              ON c.coursetemplate_id = ct.id
            WHERE c.coursetemplate_id =? ', array($tmpl_id)
			);
  $course_lists = array();
  $i=0;
foreach($outcomelists as $crstest){
	
	$course_lists[$i]=$crstest->id;
	$i++;
} 
//printf($course_lists[3]);
// semester offering
$semestertype = array();
$semestertype['semestertype']= 'Select a Semester';
$semestertype['Spring-2014'] ='Spring-2014';
$semestertype['Fall-2014'] = 'Fall-2014';
$semestertype['Spring-2015'] ='Spring-2015';
$semestertype['Fall-2015'] ='Spring-2015';
$courselevel=array();
$courselevel['courselevel']= 'Select a level';
$courselevel['Undergraduate'] ='Undergraduate';
$courselevel['Graduate'] ='Graduate';
//pre-requisite
$prerequisitetype= array();
//$prerequisitetype['prerequisitetype'] ='Select one';
$prerequisitetype['Prereq'] ='Prereqs';
$prerequisitetype['NoPrereq'] = 'NoPrereq';
//printf($course_id);
$coursetmps = @get_records_sql_array(
	'SELECT id, coursetemplate_name
		FROM {course_template}',
	array()
);
$coursetemplates = array();
foreach($coursetmps as $coursetemplate){
	$coursetemplates[$coursetemplate->id] = $coursetemplate->coursetemplate_name;
}

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
		$createcourseoffering['elements']['Instructor2'] = array(
            'type'         => 'text',
            'title'        => 'Instructor 2',
        );
		$createcourseoffering['elements']['semestertype'] = array(
            'type'         => 'select',
            'title'        => 'Semester Term',
			'options'      =>  $semestertype,
			'defaultyvalue'=>  'Select a Semester',
        );
		$createcourseoffering['elements']['courselevel'] = array(
            'type'         => 'select',
            'title'        => 'Course Level',
			'options'      =>  $courselevel,
			'defaultyvalue'=>  'Select a Level',
        );
			 
if($courseoffering_id == 0){
	$disable = 0;
	//$default = '';
}else{
	$disable = 1;
	$default = $courseofferingdes->coursetmp_id;
}
$createcourseoffering['elements']['coursetemplate'] = array(
            'type'         => 'text',
            'title'        => 'Course Template ID',
 		    'options'      => $coursetemplates,
			
		    'defaultvalue' => $coursetemplatedes->id,
		  );
		  $createcourseoffering['elements']['coursetemplatename'] = array(
            'type'         => 'text',
            'title'        => 'Course Template Name',
 		    'options'      => $coursetemplates,
		    'defaultvalue' => $coursetemplatedes->coursetemplate_name,
		  );
 
	  $createcourseoffering['elements']['courseofferingid'] = array(
		'type' => 'hidden',
		'value' => $courseoffering_id,
	  );
	  
	  $createcourseoffering['elements']['courseoutcome1'] = array(
		'type' => 'hidden',
		'value' => $course_lists[0],
	  );
	  $createcourseoffering['elements']['courseoutcome2'] = array(
		'type' => 'hidden',
		'value' => $course_lists[1],
	  );
	  $createcourseoffering['elements']['courseoutcome3'] = array(
		'type' => 'hidden',
		'value' => $course_lists[2],
	  );
	  $createcourseoffering['elements']['courseoutcome4'] = array(
		'type' => 'hidden',
		'value' => $course_lists[3],
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
    //if (get_field('course_offering', 'id','courseoffering_name', $values['name'])) {
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
	$coursetemplatename = $_POST['coursetemplatename'];
}else{
	$coursetemplate = $courseofferingdes->coursetmp_id;
	$coursetemplatename = $courseofferingdes->coursetmp_name;
}
$str= $_POST['description'];

/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/
printf('before table entries');
db_begin();
$newcourseoffering = insert_record('course_offering', (object)array('courseoffering_name' => $_POST['name'],'Instructor1' =>$_POST[Instructor1],'Instructor2' =>$_POST[Instructor2],'coursetmp_id' => $coursetemplate,'coursetmp_name' => $coursetemplatename,'semester' =>$_POST['semestertype'],'courselevel' =>$_POST['courselevel'],'courseoutcome1' =>$_POST['courseoutcome1'],'courseoutcome2' =>$_POST['courseoutcome2'],'courseoutcome3' =>$_POST['courseoutcome3'],'courseoutcome4' =>$_POST['courseoutcome4'], 'main_courseoffering' => $_POST['courseofferingid'], 'deleted' => 0),'id',true);
db_commit();
    $SESSION->add_ok_msg('Course Offering Saved');
	//redirect(get_config('wwwroot').'courseoffering/edit.php?courseoffering=' . $newcourseoffering. '&main_offset=' . $offset);
	 redirect(get_config('wwwroot').'/courseoffering/courseofferings.php?courseoffering=' . $_POST['courseofferingid']. '&offset=' . $offset);
/*
   if($_POST['prerequisitetype'] == 'Prereq'){
    redirect(get_config('wwwroot').'courseoffering/edit.php?courseoffering=' . $newcourseoffering. '&main_offset=' . $offset);
	}else{
    redirect(get_config('wwwroot').'/courseoffering/courseofferings.php?courseoffering=' . $_POST['courseofferingid']. '&offset=' . $offset);
   }
   */
   

}
?>
