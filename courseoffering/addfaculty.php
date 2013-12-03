
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
/*
if($courseoffering_id == 0){
	define('MENUITEM', 'courseofferings/courseofferings');
	define('TITLE', 'Create Course Offering');
}else{
	define('MENUITEM', 'courseofferings/subcourseofferings');
	define('TITLE', $courseofferingdes->courseoffering_name);
}*/
//start-Eshwari
$tmpl_id = param_integer('coursetemplate',0);




//echo (is_int($course_id));
$coursetemplatedes = get_coursetemplate_name($tmpl_id);
printf('here');
 /*
$facultydatas =@get_records_sql_array(
	'SELECT id, firstname, lastname, email, asu_id
		FROM usr u',
	   
 array()
 );


$faculnames =array();
foreach($facultydatas as $faculname) {
		$faculnames[$faculname->id] =$faculname->firstname;
 }
 */
 $datas = "SELECT id, firstname, lastname".
		" FROM {usr} u ".
		" WHERE staff ='1'" ;
		 
		
		//echo($x);
		$peas=@get_records_sql_array($datas);
print_r($peas);

$facultynames =array();
$facultynames['facultyname'] ='Select from list';
foreach($peas as $facultyname){
		$facultynames[$facultyname->id] = $facultyname->firstname;

}

$addfaculty = array(
    'name'     => 'addfaculty',
    'method'   => 'post',
    'elements' => array(),
);
/*
 $addfaculty['elements']['faculname'] = array(
            'type'         => 'select',
            'title'        => 'Faculty Name',
			'options'      =>  $faculnames,
			'defaultyvalue'=>  'Select a Student Name',
        );*/
		$addfaculty['elements']['facultyname'] = array(
            'type'         => 'select',
            'title'        => 'Assign Faculty',
			'options'      =>  $facultynames,
			'defaultyvalue'=>  'Select a Faculty Name',
        );
 
	  $addfaculty['elements']['addfacultyid'] = array(
		'type' => 'hidden',
		'value' => $addfaculty_id,
	  );
	  $addfaculty['elements']['addofferingid'] = array(
		'type' => 'hidden',
		'value' => $courseoffering_id,
	  );
	   $addfaculty['elements']['addsemester'] = array(
		'type' => 'hidden',
		'value' => $courseofferingdes->semester,
	  );
	  
	  
        $addfaculty['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );
$addfaculty = pieform($addfaculty);
$smarty = smarty();
$smarty->assign('addfaculty', $addfaculty);
$smarty->assign('main_faculty',$courseofferingdes->main_faculty);
if($addfaculty_id != 0){
$smarty->assign('ADDFACULTYNAV', addfaculty_get_menu_tabs($addfaculty_id));
$smarty->assign('PAGEHEADING', $courseofferingdes->addfaculty_name);

}else{
$smarty->assign('ADDFACULTYNAV','');
//$smarty->assign('PAGEHEADING', 'Create Course Template');
}
$smarty->display('courseoffering/addfaculty.tpl');

function addfaculty_validate(Pieform $form, $values) {
global $addfaculty_id;
 	$errorval = '';
   

    if (get_field('faculty_course', 'id', 'faculty_id', $values['addfacultyid'],'offering_semester', $values['addsemester'])) {
		$errorval = 'Faculty already exists for this course';
    }

	if($errorval){
 	$form->set_error('name', $errorval);
	}else {
   	if($addfaculty_id == 0 && $_POST['facultynames'] == "Faculty"){
	$err = 'Please select a Faculty';
	$form->set_error('facultynames',$err);
  	}
	}


}

function addfaculty_cancel_submit(Pieform $form, $values) {
global $offset;
    redirect(get_config('wwwroot').'/courseoffering/view.php?courseoffering=' . $_POST['courseofferingid'] . '&offset=' . $offset);
}

function addfaculty_submit(Pieform $form, $values) {
   global $USER;
    global $SESSION;
global $offset,$addfaculty_id, $addfacultysdes;
/*
if($addfaculty_id == 0){
	$degree = $_POST['degree'];
		
}else{
	$degree = $coursetemplatedes->degree_id;
	$degreename = $coursetemplatedes->degree_name;
}
*/

db_begin();
$newaddfaculty = insert_record('faculty_course', (object)array('faculty_id' => $_POST['facultyname'],'offering_id' => $_POST['addofferingid'],'offering_semester' => $_POST['addsemester']),'id',true);
db_commit();
 $SESSION->add_ok_msg('Faculty Saved');

}

?>