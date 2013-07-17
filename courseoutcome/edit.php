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

$main_offset = param_integer('main_offset',0);

if (!can_create_courseoutcomes()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseoutcomedes = is_courseoutcome_available($courseoutcome_id);

if (!$courseoutcomedes) {
    throw new AccessDeniedException("courseoutcome does not exist");
}

if ($courseoutcomedes->main_courseoutcome != 0){
$main_courseoutcome = is_courseoutcome_available($courseoutcomedes->main_courseoutcome);

if (!$main_courseoutcome) {
    throw new AccessDeniedException("courseoutcome does not exist");
}
	define('MENUITEM', 'courseoutcomes/subcourseoutcomes');

}else{
	define('MENUITEM', 'courseoutcomes/courseoutcomes');
}

define('TITLE', $courseoutcomedes->courseoutcome_name);

$assesstype = array();
$assesstype['Level'] = 'Levels';
$assesstype['Meets/Does not meet'] = 'Meets/Does not meet';

$degcourses = @get_records_sql_array(
	'SELECT id, degree_name
		FROM {degree_courses}',
	array()
);

$courses = array();
$courses['Course'] = 'Select a Course';
foreach($degcourses as $course){
	$courses[$course->id] = $course->degree_name;
}

$createcourseoutcome = array(
    'name'     => 'createcourseoutcome',
    'method'   => 'post',
    'elements' => array(),
);
        $createcourseoutcome['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'Courseoutcome Name',
		'defaultvalue' => $courseoutcomedes->courseoutcome_name,
        );
        $createcourseoutcome['elements']['description'] = array(
            'type'         => 'wysiwyg',
            'title'        => 'Courseoutcome Description',
            'rows'         => 10,
            'cols'         => 55,
		'defaultvalue' => $courseoutcomedes->description,
        );
if($courseoutcomedes->main_courseoutcome == 0){
	$disable = 0;
}else{
	$disable = 1;
}
       $createcourseoutcome['elements']['degree'] = array(
            'type'         => 'select',
            'title'        => 'Degree Course',
            'options'      => $courses,
		'disabled' 	   => $disable,
		'defaultvalue' => $courseoutcomedes->degree_id,
        );
        $createcourseoutcome['elements']['assesstype'] = array(
            'type'         => 'select',
            'title'        => 'Evaluation Type',
            'options'      => $assesstype,
            'defaultvalue' => $courseoutcomedes->eval_type,
        );
	  $createcourseoutcome['elements']['courseoutcomeid'] = array(
		'type' => 'hidden',
		'value' => $courseoutcome_id,
	  );
	  $createcourseoutcome['elements']['maincourseoutcome'] = array(
		'type' => 'hidden',
		'value' => $courseoutcomedes->main_courseoutcome,
	  );
        $createcourseoutcome['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );

if($courseoutcomedes->eval_type == 'Level'){
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
		'value' => '<h3 align="center">Courseoutcome Levels</h3>',
        );

$limit = 4;
$courseoutcomelevels = get_courseoutcome_levels($offset, $limit, $courseoutcome_id, 0);
$countlvls = find_last_offset($limit, $courseoutcome_id, 0);

if($offset >= $countlvls['last_offset']){
	$adddisabled = 0;
}else{
	$adddisabled = 1;
}
if($courseoutcomelevels){
$i = 0;
$dellink = get_config('wwwroot') . 'courseoutcome/deletelvl.php';
foreach($courseoutcomelevels as $courseoutcomelevel){
        $addlevels['elements']['dellvl'.$i] = array(
            'type'         => 'html',
		'value' => '<a href="' . $dellink . '?courseoutcome=' . $courseoutcome_id . '&levelid=' . $courseoutcomelevel->id .'&main_offset=' . $main_offset . '&offset=' . $offset . '" class="btn-del">Delete</a>',
        );
        $addlevels['elements']['level'.$i] = array(
            'type'         => 'text',
            'title'        => 'Level',
		'defaultvalue' => $courseoutcomelevel->level_val,
        );
        $addlevels['elements']['oldlevel'.$i] = array(
            'type'         => 'hidden',
		'value' => $courseoutcomelevel->level_val,
        );
        $addlevels['elements']['description'.$i] = array(
            'type'         => 'textarea',
            'title'        => 'Description',
            'rows'         => 10,
            'cols'         => 55,
		'defaultvalue' => $courseoutcomelevel->level_desc,
        );
        $addlevels['elements']['olddesc'.$i] = array(
            'type'         => 'hidden',
		'value' => $courseoutcomelevel->level_desc,
        );
        $addlevels['elements']['levelid'.$i] = array(
            'type'         => 'hidden',
            'value' => $courseoutcomelevel->id,
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
    'url' => get_config('wwwroot') . 'courseoutcome/edit.php?courseoutcome=' . $courseoutcome_id . '&main_offset=' .$main_offset,
    'count' => $countlvls['count'],
    'limit' => $limit,
    'offset' => (int)$offset,
    'resultcounttextsingular' => 'courseoutcome level',
    'resultcounttextplural' => 'courseoutcome levels',
));

}


$createcourseoutcome = pieform($createcourseoutcome);
$smarty = smarty();
$smarty->assign('createcourseoutcome', $createcourseoutcome);
$smarty->assign('addlevels',$addlevels);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('main_courseoutcome',$main_courseoutcome->main_courseoutcome);

if($courseoutcomedes->main_courseoutcome != 0){
$smarty->assign('COURSEOUTCOMENAV', courseoutcome_get_menu_tabs($courseoutcomedes->main_courseoutcome));
$smarty->assign('PAGEHEADING', $main_courseoutcome->courseoutcome_name);
$smarty->assign('header', 'Edit Sub courseoutcome');
}else{
$smarty->assign('COURSEOUTCOMENAV','');
$smarty->assign('PAGEHEADING', 'Edit Courseoutcome');
$smarty->assign('header', '');

}
$smarty->display('courseoutcome/edit.tpl');


function createcourseoutcome_validate(Pieform $form, $values) {
global $courseoutcome_id,$courseoutcomedes;
 	$errorval = '';
   if($values['name'] == ''){
	$errorval = "Courseoutcome Name is mandatory";
   }

if($courseoutcomedes->main_courseoutcome){
	$degree_id = $courseoutcomedes->degree_id;
}else{
	$degree_id = $_POST['degree'];
}

 	$dupid = get_record('courseoutcomes', 'degree_id', $degree_id,'courseoutcome_name', $_POST['name']);
    if ($dupid != '' && $dupid->id != $courseoutcome_id) {
		$errorval = 'courseoutcome already exists for this course';
    }

	if($errorval){
 	$form->set_error('name', $errorval);
	}else if(!$courseoutcomedes->main_courseoutcome) {
   	if($_POST['degree'] == "Course"){
	$err = 'Please select a course';
	$form->set_error('degree',$err);
  	}
	}

}

function createcourseoutcome_cancel_submit(Pieform $form, $values) {
global $main_offset;
    redirect(get_config('wwwroot').'/courseoutcome/courseoutcomes.php?courseoutcome=' . $_POST['maincourseoutcome'] . '&offset=' . $main_offset);
}

function createcourseoutcome_submit(Pieform $form, $values) {
   global $USER;
    global $SESSION;
global $courseoutcomedes, $main_offset, $offset;
db_begin();

if($_POST['degree'] != $courseoutcomedes->degree_id){
	$primaryval = NULL;
}else{
	$primaryval = $courseoutcomedes->special_access;
}

if($courseoutcomedes->main_courseoutcome){
	$degree_id = $courseoutcomedes->degree_id;
}else{
	$degree_id = $_POST['degree'];
}
$str= $_POST['description'];
/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/

update_record('courseoutcomes', array('courseoutcome_name' => $_POST['name'],'description' => $str, 'degree_id' => $degree_id, 'special_access' => $primaryval, 'eval_type' => $_POST['assesstype']),array('id' => $_POST['courseoutcomeid']));

db_commit();

    $SESSION->add_ok_msg('courseoutcome Updated');

    redirect(get_config('wwwroot').'courseoutcome/edit.php?courseoutcome=' . $_POST['courseoutcomeid'] . '&offset=' .$offset . '&main_offset=' .$main_offset);

}

function addlevels_validate(Pieform $form, $values){
global $limit;
global $courseoutcome_id;

for($i = 0; $i<$limit; $i++){
	if($values['level'.$i] == '' && $values['description'.$i] != ''){
		$form->set_error('level'.$i,'Enter a level for the description');
	}
}
}

function addlevels_submit(Pieform $form, $values){
global $limit;
global $offset, $main_offset;
global $courseoutcome_id;
global $SESSION;

$cntnos = 0;
for($i = 0; $i<$limit; $i++){
$assess = '';
if($_POST['level'.$i] != ''){
$cntnos = $cntnos + 1;
if(!($_POST['oldlevel'.$i] == $_POST['level'.$i] && $_POST['olddesc'.$i] == $_POST['description'.$i])){

$assess = $_POST['levelid'.$i];

$str = $_POST['description'.$i];
/*$desclen = strlen($desc);
$str = substr($desc,3,$desclen-7);*/

db_begin();
if($assess){

	update_record('courseoutcome_levels', array('level_val'=>$_POST['level'.$i],'level_desc' => $str),array('id' => $assess));

}else{
	insert_record('courseoutcome_levels', (object)array('courseoutcome_id' => $courseoutcome_id,'rubric_no' => 0,'level_val'=>$_POST['level'.$i],'level_desc'=>$str),'id',true);
}
db_commit();
}

}
}
if($cntnos > 0){
 $SESSION->add_ok_msg('Courseoutcome Levels Updated');
}

if(isset($_POST['savelevels'])) {
    redirect(get_config('wwwroot').'courseoutcome/edit.php?courseoutcome=' . $courseoutcome_id.'&offset=' .$offset . '&main_offset=' .$main_offset);
}else{
if($cntnos == 4){
	$offset = $offset+ 4;
}
    redirect(get_config('wwwroot').'courseoutcome/edit.php?courseoutcome=' . $courseoutcome_id.'&offset=' .$offset . '&main_offset=' .$main_offset);
}

}

?>
