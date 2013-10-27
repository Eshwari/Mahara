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
 * Create , update or delete courseoutcome
*/

/**
 * Sets up groups for display in mygroups.php and find.php
 *
 * @param array $groups    Initial group data, including the current user's 
 *                         membership type in each group. See mygroups.php for
 *                         the query to build this information.
 * @param string $returnto Where forms generated for display should be told to return to
 */

function is_courseoutcome_available($courseoutcomeid) {
$outrec = get_record('courseoutcomes','id',$courseoutcomeid);
if(!$outrec || $outrec->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $outrec;
}

}

function is_rubric_available($rubricid) {
$rubrec = get_record('course_rubrics','id',$rubricid);
if(!$rubrec || $rubrec->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $rubrec;
}

}

function can_create_courseoutcomes() {
    global $USER;
    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        return true;
    }
    return false;
}


function get_courseoutcomes($limit=20, $offset=0, $courseoutcomeid=0, $course=0) {

    if(!$courseoutcomeid){
	$courseoutcomeid = 0;
    }
    if($course == 0){
    $sql = 'SELECT *
		    FROM {courseoutcomes}
		    WHERE main_courseoutcome = ?
		    AND deleted != 1
		    ORDER BY courseoutcome_name';
	$values = array($courseoutcomeid);
    }else{
    $sql = 'SELECT *
		    FROM {courseoutcomes}
		    WHERE main_courseoutcome = ?
		    AND degree_id = ?
		    AND deleted != 1
		    ORDER BY courseoutcome_name';
	$values = array($courseoutcomeid, $course);
    }
    
    $courseoutcomes = get_records_sql_assoc($sql, $values, $offset, $limit);
    
 $count = count_records_sql('SELECT COUNT(*) FROM {courseoutcomes} WHERE main_courseoutcome = ? and deleted != 1', $values);

    if (!$courseoutcomes) {
        $courseoutcomes = array();
    }

    return array('courseoutcomes' => $courseoutcomes, 'count' => $count);

}


function get_rubrics($limit=20, $offset=0, $courseoutcomeid=0) {

    if(!$courseoutcomeid){
	$courseoutcomeid = 0;
    }
    
    $values = array($courseoutcomeid);
    $rubrics =  @get_records_sql_array(
	'SELECT *
		FROM {course_rubrics}
		WHERE courseoutcome_id = ?
		AND deleted != 1
		LIMIT ?,?',
	array($courseoutcomeid,$offset,$limit)
    );
    
 $count = count_records_sql('SELECT COUNT(*) FROM {course_rubrics} WHERE courseoutcome_id = ? AND deleted != 1', $values);

    if (!$courseoutcomes) {
        $courseoutcomes = array();
    }

    return array('rubrics' => $rubrics, 'count' => $count);

}

function get_courseoutcome_levels($offset=0, $limit=4, $courseoutcomeid, $rubric_no) {

$sql = 'SELECT *
	  FROM {courseoutcome_levels} 
        WHERE courseoutcome_id = ?
	  AND rubric_no = ?
	  ORDER BY level_val';
    
    $values = array($courseoutcomeid, $rubric_no);
    $courseoutcomes = get_records_sql_assoc($sql, $values, $offset, $limit);

    if (!$courseoutcomes) {
        $courseoutcomes = array();
    }

    return $courseoutcomes;
}

function find_last_offset_courseoutcome($limit, $courseoutcomeid, $rubric_no){
	$values = array($courseoutcomeid, $rubric_no);
 	$countlvls = count_records_sql('SELECT COUNT(*) FROM {courseoutcome_levels} WHERE courseoutcome_id = ? AND rubric_no = ?', $values);

	$pages = ceil($countlvls/$limit);
	$last_offset = ($pages-1)*$limit;
	return array('last_offset' => $last_offset, 'count' => $countlvls);
}

function courseoutcome_get_menu_tabs($courseoutcome_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'courseoutcomes/info',
            'url' => 'courseoutcome/view.php?courseoutcome='.$courseoutcome_id,
            'title' => 'About',
            'weight' => 20
        ),
	);

if(!$USER->get('admin')){
$rubrics = @get_records_sql_array(
    'SELECT id
       FROM {course_rubrics}
       WHERE courseoutcome_id = ?
	 AND deleted = 0',
    array($courseoutcome_id)
);
$subcourseoutcomes = @get_records_sql_array(
    'SELECT id
       FROM {courseoutcomes}
       WHERE main_courseoutcome = ?
	 AND deleted = 0',
    array($courseoutcome_id)
);
}

if($USER->get('admin') || $rubrics){ 
    $menu['rubrics'] = array(
            'path' => 'courseoutcomes/rubrics',
            'url' => 'courseoutcome/rubrics.php?courseoutcome='.$courseoutcome_id,
            'title' => 'Rubrics',
            'weight' => 30
        );
}
/*
if($USER->get('admin') || $subcourseoutcomes){
      $menu['subcourseoutcomes'] = array(
            'path' => 'courseoutcomes/subcourseoutcomes',
            'url' => 'courseoutcome/courseoutcomes.php?courseoutcome='.$courseoutcome_id,
            'title' => 'Sub courseoutcomes',
            'weight' => 30
        );
}

if($USER->get('admin')){
      $menu['primary'] = array(
            'path' => 'courseoutcomes/primary',
            'url' => 'courseoutcome/primary.php?courseoutcome='.$courseoutcome_id,
            'title' => 'Primary',
            'weight' => 30
        );
}*/
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('courseoutcomes/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}

function rubric_get_menu_tabs($courseoutcome_id=0, $rubric_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'courseoutcomes/rubrics/info',
            'url' => 'courseoutcome/rubricview.php?courseoutcome='.$courseoutcome_id .'&rubric=' . $rubric_id,
            'title' => 'About',
            'weight' => 20
        ),
	);
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('courseoutcomes/rubrics/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}

?>
