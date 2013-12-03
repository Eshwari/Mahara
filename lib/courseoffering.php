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

defined('INTERNAL') || die();

//Outcome related functions
/*
 * Create , update or delete courseoffering
*/

/**
 * Sets up groups for display in mygroups.php and find.php
 *
 * @param array $groups    Initial group data, including the current user's 
 *                         membership type in each group. See mygroups.php for
 *                         the query to build this information.
 * @param string $returnto Where forms generated for display should be told to return to
 */

function is_courseoffering_available($courseofferingid) {
$outrec = get_record('course_offering','id',$courseofferingid);
if(!$outrec || $outrec->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $outrec;
}

}
//Eshwari -start
function get_coursetemplate_name($_id){
$outrec2 = get_record('course_template','id',$_id);
if(!$outrec2 || $outrec2->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $outrec2;
}
}
//eshwari -end
function get_courseoutcomes($_id){

$outrec1 = get_record('courseoutcomes','id',$_id);
if(!$outrec1 || $outrec1->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $outrec2;
	}
  }


function can_create_courseofferings() {
    global $USER;
    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        return true;
    }
    return false;
}


function get_courseofferings($limit=20, $offset=0, $courseofferingid=0 , $course=0) {

    if(!$courseofferingid){
	$courseofferingid = 0;
    }
    if($course == 0){
    $sql = 'SELECT *
		    FROM {course_offering}
		    WHERE main_courseoffering = ?
		    AND deleted != 1
		    ORDER BY courseoffering_name';
	$values = array($courseofferingid);
    }else{
    $sql = 'SELECT *
		    FROM {course_offering}
		    WHERE main_courseoffering = ?
		    AND coursetmp_id = ?
		    AND deleted != 1
		    ORDER BY courseoffering_name';
	$values = array($courseofferingid, $course);
    }
    
    $courseofferings = get_records_sql_assoc($sql, $values, $offset, $limit);
    
 $count = count_records_sql('SELECT COUNT(*) FROM {course_offering} WHERE main_courseoffering = ? and deleted != 1', $values);

    if (!$courseofferings) {
        $courseofferings = array();
    }

    return array('courseofferings' => $courseofferings, 'count' => $count);

}


function get_courseoffering_prerequisites($offset=0, $limit=4, $courseofferingid, $rubric_no) {

$sql = 'SELECT *
	  FROM {courseoffering_prerequisites} 
        WHERE courseoffering_id = ?
		AND rubric_no = ?
	    ORDER BY prerequisite_val';
    
    $values = array($courseofferingid, $rubric_no);
    $courseofferings = get_records_sql_assoc($sql, $values, $offset, $limit);

    if (!$courseofferings) {
        $courseofferings = array();
    }

    return $courseofferings;
}



function courseoffering_get_menu_tabs($courseoffering_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'courseofferings/info',
            'url' => 'courseoffering/view.php?courseoffering='.$courseoffering_id,

            'title' => 'About',
            'weight' => 20
        ),
	);
if(!$USER->get('admin')){
$activities = @get_records_sql_array(
    'SELECT id
       FROM {add_activities}
       WHERE courseoffering_id = ?
	 AND deleted = 0',
    array($courseoffering_id)
);

} 
	if($USER->get('admin') || $activities){ 
    $menu['activities'] = array(
            'path' => 'courseofferings/activities',
            'url' => 'courseoffering/activities.php?courseoffering='.$courseoffering_id,
			'title' => 'Add Activities',
            'weight' => 30
        );
}
	


	if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('courseofferings/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}
function addfaculty_get_menu_tabs($addfaculty_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'courseofferings/info',
            'url' => 'courseoffering/addfaculty.php?courseoffering='.$addfaculty_id,


            'title' => 'Abou',
            'weight' => 20
        ),
	);
	
	if($USER->get('admin') || $courseofferings){ 
    $menu['addstudents'] = array(
            'path' => 'courseofferings/addfaculty',
            'url' => 'courseoffering/addfaculty.php?courseoffering='.$addfaculty_id,


			'title' => 'Add Faculty',
            'weight' => 30
        );
}
	if($USER->get('admin') || $courseofferings){ 
    $menu['addfaculty'] = array(
            'path' => 'courseofferings/addfaculty',
            'url' => 'courseoffering/addfaculty.php?courseoffering='.$courseoffering_id,


			'title' => 'Add Faculty',
            'weight' => 30
        );
}


	if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('courseofferings/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}

function get_activities($limit=20, $offset=0, $courseofferingid=0) {

    if(!$courseofferingid){
	$courseofferingid = 0;
    }
    
    $values = array($courseofferingid);
    $activities =  @get_records_sql_array(
	'SELECT *
		FROM {add_activities}
		WHERE courseoffering_id = ?
		AND deleted != 1
		LIMIT ?,?',
	array($courseofferingid,$offset,$limit)
    );
    
 $count = count_records_sql('SELECT COUNT(*) FROM {add_activities} WHERE courseoffering_id = ? AND deleted != 1', $values);

    if (!$courseoffering) {
        $courseoffering = array();
    }

    return array('activities' => $activities, 'count' => $count);

}
function can_create_activities() {
    global $USER;
    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        return true;
    }
    return false;
}

function is_activity_available($activityid) {
$activityrec = get_record('add_activities','id',$activityid);
if(!$activityrec || $activityrec->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $activityrec;
}

}

function activity_get_menu_tabs($courseoffering_id=0, $activity_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'courseofferings/activities/info',
            'url' => 'courseoffering/viewactivity.php?courseoffering='.$courseoffering_id .'&activity=' . $activity_id,
            'title' => 'About',
            'weight' => 20
        ),
	);
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('courseofferings/activities/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}


?>
