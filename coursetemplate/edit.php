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

$main_offset = param_integer('main_offset',0);

if (!can_create_coursetemplates()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$coursetemplatedes = is_coursetemplate_available($coursetemplate_id);

if (!$coursetemplatedes) {
    throw new AccessDeniedException("coursetemplate does not exist");
}

if ($coursetemplatedes->main_coursetemplate != 0){
$main_coursetemplate = is_coursetemplate_available($coursetemplatedes->main_coursetemplate);

if (!$main_coursetemplate) {
    throw new AccessDeniedException("coursetemplate does not exist");
}
	define('MENUITEM', 'coursetemplates/subcoursetemplates');

}else{
	define('MENUITEM', 'coursetemplates/coursetemplates');
}

define('TITLE', $coursetemplatedes->coursetemplate_name);

/*$assesstype = array();
$assesstype['Level'] = 'Levels';
$assesstype['Meets/Does not meet'] = 'Meets/Does not meet';
*/
//pre-requisite
$prerequisite= array();
$prerequisite['prerequisite'] ='Select one';
$prerequisite['Yes'] ='Yes';
$prerequisite['No'] = 'No';

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

$createcoursetemplate = array(
    'name'     => 'createcoursetemplate',
    'method'   => 'post',
    'elements' => array(),
);
        $createcoursetemplate['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'coursetemplate Name',
		'defaultvalue' => $coursetemplatedes->coursetemplate_name,
        );
		
		$createcoursetemplate['elements']['collegeofferingtype'] = array(
            'type'         => 'select',
            'title'        => 'College Offering',
			'options'      =>  $collegeofferingtype,
			'defaultvalue'=>  $coursetemplatedes->college_offering,
        );
		$createcoursetemplate['elements']['deptofferingtype'] = array(
            'type'         => 'select',
            'title'        => 'Department Offering',
			'options'      =>  $deptofferingtype,
			'defaultvalue'=>  $coursetemplatedes->dept_offering,
        );
		
        $createcoursetemplate['elements']['course_description'] = array(
            'type'         => 'wysiwyg',
            'title'        => 'coursetemplate Description',
            'rows'         => 10,
            'cols'         => 55,
		'defaultvalue' => $coursetemplatedes->course_description,
        );
		
if($coursetemplatedes->main_coursetemplate == 0){
	$disable = 0;
}else{
	$disable = 1;
}
       $createcoursetemplate['elements']['degree'] = array(
            'type'         => 'select',
            'title'        => 'Degree Course',
            'options'      => $courses,
		'disabled' 	   => $disable,
		'defaultvalue' => $coursetemplatedes->degree_id,
        );
		$createcoursetemplate['elements']['prerequisite'] = array(
            'type'         => 'select',
            'title'        => 'Indicate Pre-requisites exist for the Course',
            'options'      => $prerequisite,
            'defaultvalue' => $coursetemplatedes->prerequisite_type,
        );
        
	  $createcoursetemplate['elements']['coursetemplateid'] = array(
		'type' => 'hidden',
		'value' => $coursetemplate_id,
	  );
	  $createcoursetemplate['elements']['maincoursetemplate'] = array(
		'type' => 'hidden',
		'value' => $coursetemplatedes->main_coursetemplate,
	  );
        $createcoursetemplate['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );

if($coursetemplatedes->prerequisite_type == 'Yes'){
$add_prerequisites = array(
    'name'     => 'add_prerequisites',
    'method'   => 'post',
    'elements' => array(),
);

        $add_prerequisites['elements']['header'] = array(
            'type'         => 'html',
		'value' => '<br/><table class="fullwidth table"><thead><tr><th></th></tr></thead></table><br/>',
        );

        $add_prerequisites['elements']['headtitle'] = array(
            'type'         => 'html',
		'value' => '<h3 align="center">Course Pre-Requisites</h3>',
        );

$limit = 4;
$coursetemplate_prerequisites = get_coursetemplate_prerequisites($offset, $limit, $coursetemplate_id, 0);
$countpres = find_last_offset($limit, $coursetemplate_id, 0);

if($offset >= $countpres['last_offset']){
	$adddisabled = 0;
}else{
	$adddisabled = 1;
}
if($coursetemplate_prerequisites){
$i = 0;
$dellink = get_config('wwwroot') . 'coursetemplate/deletepre.php';
foreach($coursetemplate_prerequisites as $coursetemplate_prerequisite){
        $add_prerequisites['elements']['delpre'.$i] = array(
            'type'         => 'html',
		'value' => '<a href="' . $dellink . '?coursetemplate=' . $coursetemplate_id . '&prereqid=' . $coursetemplate_prerequisite->id .'&main_offset=' . $main_offset . '&offset=' . $offset . '" class="btn-del">Delete</a>',
        );
        $add_prerequisites['elements']['prereq'.$i] = array(
            'type'         => 'text',
            'title'        => 'Pre-Requisite No.',
		'defaultvalue' => $coursetemplatelevel->prerequisite_val,
        );
        $add_prerequisites['elements']['oldprereq'.$i] = array(
            'type'         => 'hidden',
		'value' => $coursetemplatelevel->prerequisite_val,
        );
        $add_prerequisites['elements']['description'.$i] = array(
            'type'         => 'text',
            'title'        => 'Description',
            'defaultvalue' => $coursetemplatelevel->prerequisite_desc,
        );
        $add_prerequisites['elements']['olddesc'.$i] = array(
            'type'         => 'hidden',
		'value' => $coursetemplatelevel->prerequisite_desc,
        );
        $add_prerequisites['elements']['prereqid'.$i] = array(
            'type'         => 'hidden',
            'value' => $coursetemplatelevel->id,
        );
	$i = $i + 1;
}
if($i < 4){
for($j=$i; $j < $limit; $j++){

        $add_prerequisites['elements']['prereq'.$j] = array(
            'type'         => 'text',
            'title'        => 'Pre-Requisite No.',
        );
        $add_prerequisites['elements']['oldprereq'.$j] = array(
            'type'         => 'hidden',
		'value' => 'null',
        );
        $add_prerequisites['elements']['description'.$j] = array(
            'type'         => 'text',
            'title'        => 'Description',
        );
        $add_prerequisites['elements']['olddesc'.$j] = array(
            'type'         => 'hidden',
		'value' => 'null',
        );
        $add_prerequisites['elements']['prereqid'.$i] = array(
            'type'         => 'hidden',
            'value' => '',
        );
}
}

}else{
for($i=0; $i < $limit; $i++){

        $add_prerequisites['elements']['prereq'.$i] = array(
            'type'         => 'text',

            'title'        => 'Pre-Requisite No.',
        );
        $add_prerequisites['elements']['oldprereq'.$i] = array(
            'type'         => 'hidden',
		'value' => 'null',
        );
        $add_prerequisites['elements']['description'.$i] = array(
            'type'         => 'text',
            'title'        => 'Description',
        );
        $add_prerequisites['elements']['olddesc'.$i] = array(
            'type'         => 'hidden',
		'value' => 'null',
        );
        $add_prerequisites['elements']['prereqid'.$i] = array(
            'type'         => 'hidden',
            'value' => '',
        );
}
}
        $add_prerequisites['elements']['saveprereqs'] = array(
            'type'         => 'submit',
            'value' => 'Save',
        );
        if(!$adddisabled){
        $add_prerequisites['elements']['add_prerequisites'] = array(
            'type'         => 'submit',
            'value' => 'Save & Add more',
        );
	  }

$add_prerequisites= pieform($add_prerequisites);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'coursetemplate/edit.php?coursetemplate=' . $coursetemplate_id . '&main_offset=' .$main_offset,
    'count' => $countpres['count'],
    'limit' => $limit,
    'offset' => (int)$offset,
    'resultcounttextsingular' => 'coursetemplate prereq',
    'resultcounttextplural' => 'coursetemplate prereqs',
));

}


$createcoursetemplate = pieform($createcoursetemplate);
$smarty = smarty();
$smarty->assign('createcoursetemplate', $createcoursetemplate);
$smarty->assign('add_prerequisites',$add_prerequisites);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('main_coursetemplate',$main_coursetemplate->main_coursetemplate);

if($coursetemplatedes->main_coursetemplate != 0){
$smarty->assign('COURSETEMPLATENAV', coursetemplate_get_menu_tabs($coursetemplatedes->main_coursetemplate));
$smarty->assign('PAGEHEADING', $main_coursetemplate->coursetemplate_name);
$smarty->assign('header', 'Edit Sub coursetemplate');
}else{
$smarty->assign('COURSETEMPLATENAV','');
$smarty->assign('PAGEHEADING', 'Edit coursetemplate');
$smarty->assign('header', '');

}
$smarty->display('coursetemplate/edit.tpl');

function coursetemplate_validate(Pieform $form, $values) {
global $coursetemplate_id,$coursetemplatedes;

 	$errorval = '';
   if($values['name'] == ''){
	$errorval = "coursetemplate Name is mandatory";
   }

if($coursetemplatedes->main_coursetemplate){

	$degree_id = $coursetemplatedes->degree_id;
}else{
	$degree_id = $_POST['degree'];
}

 	$dupid = get_record('course_template', 'degree_id', $degree_id,'coursetemplate_name', $_POST['name']);
    if ($dupid != '' && $dupid->id != $coursetemplate_id) {
		$errorval = 'coursetemplate already exists for this course';
    }

	if($errorval){
 	$form->set_error('name', $errorval);
	}else if(!$coursetemplatedes->main_coursetemplate) {
   	if($_POST['degree'] == "Course"){
	$err = 'Please select a course';
	$form->set_error('degree',$err);
  	}
	if($_POST['degree'] == "Course"){
	$err = 'Please select a course';
	$form->set_error('degree',$err);
  	}
    }
 }

 function createcoursetemplate_cancel_submit(Pieform $form, $values) {
global $main_offset;
    redirect(get_config('wwwroot').'/coursetemplate/coursetemplates.php?coursetemplate=' . $_POST['maincoursetemplate'] . '&offset=' . $main_offset);
}

function createcoursetemplate_submit(Pieform $form, $values) {
   global $USER;
    global $SESSION;
global $coursetemplatedes, $main_offset, $offset;
db_begin();

if($_POST['degree'] != $coursetemplatedes->degree_id){
	$primaryval = NULL;
}else{
	$primaryval = $coursetemplatedes->special_access;
}

if($coursetemplatedes->main_coursetemplate){
	$degree_id = $coursetemplatedes->degree_id;
}else{
	$degree_id = $_POST['degree'];
}
$str= $_POST['course_description'];
/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/

update_record('course_template', array('coursetemplate_name' => $_POST['name'],'course_description' => $str, 'prerequisite_type	' => $_POST['prerequisite'],'degree_id' => $degree_id,'college_offering' =>$_POST['collegeofferingtype'],'dept_offering' =>$_POST['deptofferingtype'], 'special_access' => $primaryval, array('id' => $_POST['coursetemplateid'])));

db_commit();

    $SESSION->add_ok_msg('coursetemplate Updated');

    redirect(get_config('wwwroot').'coursetemplate/edit.php?coursetemplate=' . $_POST['coursetemplateid'] . '&offset=' .$offset . '&main_offset=' .$main_offset);

}

function add_prerequisites_validate(Pieform $form, $values){
global $limit;
global $coursetemplate_id;

for($i = 0; $i<$limit; $i++){
	if($values['prereq'.$i] == '' && $values['description'.$i] != ''){
		$form->set_error('prereq'.$i,'Enter a prereq for the description');
	}
}
}

function add_prerequisites_submit(Pieform $form, $values){
global $limit;
global $offset, $main_offset;
global $coursetemplate_id;
global $SESSION;

$cntnos = 0;
for($i = 0; $i<$limit; $i++){
$assess = '';
if($_POST['prereq'.$i] != ''){
$cntnos = $cntnos + 1;
if(!($_POST['oldprereq'.$i] == $_POST['prereq'.$i] && $_POST['olddesc'.$i] == $_POST['description'.$i])){

$assess = $_POST['prereqid'.$i];
//may have to change to text
$str = $_POST['description'.$i];
/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/

db_begin();
if($assess){

	update_record('coursetemplate_prerequisites', array('prerequisite_val'=>$_POST['level'.$i],'prerequisite_desc' => $_POST['level'.$i]),array('id' => $assess));

}else{
	insert_record('coursetemplate_prerequisites', (object)array('coursetemplate_id' => $coursetemplate_id,'rubric_no' => 0,'prerequisite_val'=>$_POST['level'.$i],'prerequisite_desc'=>$_POST['level'.$i]),'id',true);
}
db_commit();
}

}
}
if($cntnos > 0){
 $SESSION->add_ok_msg('Coursetemplate Pre-Reqs Updated');
}

if(isset($_POST['saveprereqs'])) {
    redirect(get_config('wwwroot').'coursetemplate/edit.php?coursetemplate=' . $coursetemplate_id.'&offset=' .$offset . '&main_offset=' .$main_offset);
}else{
if($cntnos == 4){
	$offset = $offset+ 4;
}
    redirect(get_config('wwwroot').'coursetemplate/edit.php?coursetemplate=' . $coursetemplate_id.'&offset=' .$offset . '&main_offset=' .$main_offset);
}

}

?>
