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

$outcome_id = param_integer('outcome',0);
$offset = param_integer('offset',0);

if (!can_create_outcomes()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$outcomedes = is_outcome_available($outcome_id);

if (!$outcomedes) {
    throw new AccessDeniedException("Outcome does not exist");
}

if($outcome_id == 0){
	define('MENUITEM', 'courseoutcomes/courseoutcomes');
	define('TITLE', 'Create Outcome');
}else{
	define('MENUITEM', 'courseoutcomes/suboutcomes');
	define('TITLE', $outcomedes->outcome_name);
}

$assesstype = array();
$assesstype['Level'] = 'Levels';
$assesstype['Meets/Does not meet'] = 'Meets/Does not meet';

$degprograms = @get_records_sql_array(
	'SELECT id, degree_name
		FROM {degree_courses}',
	array()
);

$courses = array();
$courses['Course'] = 'Select a course';
foreach($degcourses as $course){
	$courses[$course->id] = $course->degree_name;
}

$createoutcome = array(
    'name'     => 'createoutcome',
    'method'   => 'post',
    'elements' => array(),
);

	 $createoutcome['elements']['name'] = array(
            'type'         => 'text',
            'title'        => 'Outcome Name',
        );
        $createoutcome['elements']['description'] = array(
            'type'         => 'wysiwyg',
            'title'        => 'Outcome Description',
            'rows'         => 10,
            'cols'         => 55,
        );
		
if($outcome_id == 0){
	$disable = 0;
	$default = '';
}else{
	$disable = 1;
	$default = $outcomedes->degree_id;
}
       $createoutcome['elements']['degree'] = array(
            'type'         => 'select',
            'title'        => 'Degree Course',
            'options'      => $courses,
		'disabled' 	   => $disable,
		'defaultvalue' => $default,
        );
		
		 $createoutcome['elements']['assesstype'] = array(
            'type'         => 'select',
            'title'        => 'Evaluation Type',
            'options'      => $assesstype,
            'defaultvalue' => 'Level',
        );
		 $createoutcome['elements']['outcomeid'] = array(
		'type' => 'hidden',
		'value' => $outcome_id,
	  );
        $createoutcome['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );
		
db_begin();
$newoutcome = insert_record('courseoutcomes', (object)array('outcome_name' => $_POST['name'],'description' => $str, 'degree_id' => $degree, 'eval_type' => $_POST['assesstype'], 'main_outcome' => $_POST['outcomeid'], 'deleted' => 0),'id',true);
db_commit();


    $SESSION->add_ok_msg('Outcome Saved');

   if($_POST['assesstype'] == 'Level'){
    redirect(get_config('wwwroot').'courseoutcome/edit.php?outcome=' . $newoutcome. '&main_offset=' . $offset);
   }else{
    redirect(get_config('wwwroot').'/courseoutcome/courseoutcomes.php?outcome=' . $_POST['outcomeid']. '&offset=' . $offset);
   }

}

?>