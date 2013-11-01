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

/*
function get_rubrics($limit=20, $offset=0, $courseofferingid=0) {

    if(!$courseofferingid){
	$courseofferingid = 0;
    }
    
    $values = array($courseofferingid);
    $rubrics =  @get_records_sql_array(
	'SELECT *
		FROM {course_rubrics}
		WHERE courseoffering_id = ?
		AND deleted != 1
		LIMIT ?,?',
	array($courseofferingid,$offset,$limit)
    );
    
 $count = count_records_sql('SELECT COUNT(*) FROM {course_rubrics} WHERE courseoffering_id = ? AND deleted != 1', $values);

    if (!$courseofferings) {
        $courseofferings = array();
    }

    return array('rubrics' => $rubrics, 'count' => $count);

}
*/

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

function find_last_offset($limit, $courseofferingid, $rubric_no){
	$values = array($courseofferingid, $rubric_no);
 	$countlvls = count_records_sql('SELECT COUNT(*) FROM {courseoffering_prerequisites} WHERE courseoffering_id = ? AND rubric_no = ?', $values);

	$pages = ceil($countlvls/$limit);
	$last_offset = ($pages-1)*$limit;
	return array('last_offset' => $last_offset, 'count' => $countlvls);
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
	if($USER->get('admin') || $courseofferings){ 
    $menu['rubrics'] = array(
            'path' => 'courseofferings/courseofferings',
            'url' => 'courseoffering/createcourseofferings.php?courseoffering='.$courseoffering_id,
			'title' => 'courseofferings',
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
/*
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
$rubrics = @get_records_sql_array(
    'SELECT id
       FROM {course_rubrics}
       WHERE courseoffering_id = ?
	 AND deleted = 0',
    array($courseoffering_id)
);
$subcourseofferings = @get_records_sql_array(
    'SELECT id
       FROM {courseoffering}
       WHERE main_courseoffering = ?
	 AND deleted = 0',
    array($courseoffering_id)
);
} 
if($USER->get('admin') || $rubrics){ 
    $menu['rubrics'] = array(
            'path' => 'courseofferings/rubrics',
            'url' => 'courseoffering/rubrics.php?courseoffering='.$courseoffering_id,
            'title' => 'Rubrics',
            'weight' => 30
        );
}

if($USER->get('admin') || $subcourseofferings){
      $menu['subcourseofferings'] = array(
            'path' => 'courseofferings/subcourseofferings',
            'url' => 'courseoffering/courseofferings.php?courseoffering='.$courseoffering_id,
            'title' => 'Sub courseofferings',
            'weight' => 30
        );
}
if($USER->get('admin')){
      $menu['primary'] = array(
            'path' => 'courseofferings/primary',
            'url' => 'courseoffering/primary.php?courseoffering='.$courseoffering_id,
            'title' => 'Primary',
            'weight' => 30
        );
} 
//start-eshwari 
if($USER->get('admin') ){ 
    $menu['courseoutcomes'] = array(
            'path' => 'courseofferings/courseoutcomes',
            'url' => 'courseoffering/courseoutcomes.php?courseoffering='.$courseoffering_id,
            'title' => 'course outcomes',
            'weight' => 30
        );
}
//end-eshwari
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('courseofferings/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}

function rubric_get_menu_tabs($courseoffering_id=0, $rubric_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'courseofferings/rubrics/info',
            'url' => 'courseoffering/rubricview.php?courseoffering='.$courseoffering_id .'&rubric=' . $rubric_id,
            'title' => 'About',
            'weight' => 20
        ),
	);
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('courseofferings/rubrics/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}
*/
//start -eshwari
function courseoutcomes_get_menu_tabs($courseoffering_id=0, $rubric_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'courseofferings/rubrics/info',
            'url' => 'courseoffering/rubricview.php?courseoffering='.$courseoffering_id .'&rubric=' . $rubric_id,
            'title' => 'About',
            'weight' => 20
        ),
	);
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('courseofferings/rubrics/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}
//end-eshawrai

?>
