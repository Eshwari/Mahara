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

$assesstype = array();
$assesstype['Level'] = 'Levels';
$assesstype['Meets/Does not meet'] = 'Meets/Does not meet';

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
        $createcoursetemplate['elements']['assesstype'] = array(
            'type'         => 'select',
            'title'        => 'Evaluation Type',
            'options'      => $assesstype,
            'defaultvalue' => $coursetemplatedes->eval_type,
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

if($coursetemplatedes->eval_type == 'Level'){
$addlevels = array(
    'name'     => 'addlevels',
    'method'   => 'post',
    'elements' => array(),
);

        $addlevels['elements']['header'] = array(
            'type'         => 'html',
		'value' => '<br/><table class="fullwidth table"><thead><tr><th></th></tr></thead></table><br/>',
        );

        $addlevels['elements']['headtitle'] = array(
            'type'         => 'html',
		'value' => '<h3 align="center">coursetemplate Levels</h3>',
        );

$limit = 4;
$coursetemplatelevels = get_coursetemplate_levels($offset, $limit, $coursetemplate_id, 0);
$countlvls = find_last_offset($limit, $coursetemplate_id, 0);

if($offset >= $countlvls['last_offset']){
	$adddisabled = 0;
}else{
	$adddisabled = 1;
}
if($coursetemplatelevels){
$i = 0;
$dellink = get_config('wwwroot') . 'coursetemplate/deletelvl.php';
foreach($coursetemplatelevels as $coursetemplatelevel){
        $addlevels['elements']['dellvl'.$i] = array(
            'type'         => 'html',
		'value' => '<a href="' . $dellink . '?coursetemplate=' . $coursetemplate_id . '&levelid=' . $coursetemplatelevel->id .'&main_offset=' . $main_offset . '&offset=' . $offset . '" class="btn-del">Delete</a>',
        );
        $addlevels['elements']['level'.$i] = array(
            'type'         => 'text',
            'title'        => 'Level',
		'defaultvalue' => $coursetemplatelevel->level_val,
        );
        $addlevels['elements']['oldlevel'.$i] = array(
            'type'         => 'hidden',
		'value' => $coursetemplatelevel->level_val,
        );
        $addlevels['elements']['description'.$i] = array(
            'type'         => 'textarea',
            'title'        => 'Description',
            'rows'         => 10,
            'cols'         => 55,
		'defaultvalue' => $coursetemplatelevel->level_desc,
        );
        $addlevels['elements']['olddesc'.$i] = array(
            'type'         => 'hidden',
		'value' => $coursetemplatelevel->level_desc,
        );
        $addlevels['elements']['levelid'.$i] = array(
            'type'         => 'hidden',
            'value' => $coursetemplatelevel->id,
        );
	$i = $i + 1;
}
if($i < 4){
for($j=$i; $j < $limit; $j++){

        $addlevels['elements']['level'.$j] = array(
            'type'         => 'text',
            'title'        => 'Level',
        );
        $addlevels['elements']['oldlevel'.$j] = array(
            'type'         => 'hidden',
		'value' => 'null',
        );
        $addlevels['elements']['description'.$j] = array(
            'type'         => 'textarea',
            'title'        => 'Description',
            'rows'         => 10,
            'cols'         => 55,
        );
        $addlevels['elements']['olddesc'.$j] = array(
            'type'         => 'hidden',
		'value' => 'null',
        );
        $addlevels['elements']['levelid'.$i] = array(
            'type'         => 'hidden',
            'value' => '',
        );
}
}

}else{
for($i=0; $i < $limit; $i++){

        $addlevels['elements']['level'.$i] = array(
            'type'         => 'text',
            'title'        => 'Level',
        );
        $addlevels['elements']['oldlevel'.$i] = array(
            'type'         => 'hidden',
		'value' => 'null',
        );
        $addlevels['elements']['description'.$i] = array(
            'type'         => 'wysiwyg',
            'title'        => 'Description',
            'rows'         => 10,
            'cols'         => 55,
        );
        $addlevels['elements']['olddesc'.$i] = array(
            'type'         => 'hidden',
		'value' => 'null',
        );
        $addlevels['elements']['levelid'.$i] = array(
            'type'         => 'hidden',
            'value' => '',
        );
}
}
        $addlevels['elements']['savelevels'] = array(
            'type'         => 'submit',
            'value' => 'Save',
        );
        if(!$adddisabled){
        $addlevels['elements']['addlevels'] = array(
            'type'         => 'submit',
            'value' => 'Save & Add more',
        );
	  }

$addlevels= pieform($addlevels);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'coursetemplate/edit.php?coursetemplate=' . $coursetemplate_id . '&main_offset=' .$main_offset,
    'count' => $countlvls['count'],
    'limit' => $limit,
    'offset' => (int)$offset,
    'resultcounttextsingular' => 'coursetemplate level',
    'resultcounttextplural' => 'coursetemplate levels',
));

}


$createcoursetemplate = pieform($createcoursetemplate);
$smarty = smarty();
$smarty->assign('createcoursetemplate', $createcoursetemplate);
$smarty->assign('addlevels',$addlevels);
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


function createcoursetemplate_validate(Pieform $form, $values) {
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

update_record('course_template', array('coursetemplate_name' => $_POST['name'],'course_description' => $str, 'degree_id' => $degree_id, 'special_access' => $primaryval, 'eval_type' => $_POST['assesstype']),array('id' => $_POST['coursetemplateid']));

db_commit();

    $SESSION->add_ok_msg('coursetemplate Updated');

    redirect(get_config('wwwroot').'coursetemplate/edit.php?coursetemplate=' . $_POST['coursetemplateid'] . '&offset=' .$offset . '&main_offset=' .$main_offset);

}

function addlevels_validate(Pieform $form, $values){
global $limit;
global $coursetemplate_id;

for($i = 0; $i<$limit; $i++){
	if($values['level'.$i] == '' && $values['description'.$i] != ''){
		$form->set_error('level'.$i,'Enter a level for the description');
	}
}
}

function addlevels_submit(Pieform $form, $values){
global $limit;
global $offset, $main_offset;
global $coursetemplate_id;
global $SESSION;

$cntnos = 0;
for($i = 0; $i<$limit; $i++){
$assess = '';
if($_POST['level'.$i] != ''){
$cntnos = $cntnos + 1;
if(!($_POST['oldlevel'.$i] == $_POST['level'.$i] && $_POST['olddesc'.$i] == $_POST['description'.$i])){

$assess = $_POST['levelid'.$i];

$str = $_POST['course_description'.$i];
/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/

db_begin();
if($assess){

	update_record('coursetemplate_levels', array('level_val'=>$_POST['level'.$i],'level_desc' => $str),array('id' => $assess));

}else{
	insert_record('coursetemplate_levels', (object)array('coursetemplate_id' => $coursetemplate_id,'rubric_no' => 0,'level_val'=>$_POST['level'.$i],'level_desc'=>$str),'id',true);
}
db_commit();
}

}
}
if($cntnos > 0){
 $SESSION->add_ok_msg('coursetemplate Levels Updated');
}

if(isset($_POST['savelevels'])) {
    redirect(get_config('wwwroot').'coursetemplate/edit.php?coursetemplate=' . $coursetemplate_id.'&offset=' .$offset . '&main_offset=' .$main_offset);
}else{
if($cntnos == 4){
	$offset = $offset+ 4;
}
    redirect(get_config('wwwroot').'coursetemplate/edit.php?coursetemplate=' . $coursetemplate_id.'&offset=' .$offset . '&main_offset=' .$main_offset);
}

}

?>
