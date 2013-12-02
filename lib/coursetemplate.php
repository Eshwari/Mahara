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
 * Create , update or delete coursetemplate
*/

/**
 * Sets up groups for display in mygroups.php and find.php
 *
 * @param array $groups    Initial group data, including the current user's 
 *                         membership type in each group. See mygroups.php for
 *                         the query to build this information.
 * @param string $returnto Where forms generated for display should be told to return to
 */

function is_coursetemplate_available($coursetemplateid) {
$outrec = get_record('course_template','id',$coursetemplateid);
if(!$outrec || $outrec->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $outrec;
}

}
function is_prerequisite_available($prerequisiteid) {
$outrec = get_record('coursetemplate_prerequisite','id',$prerequisiteid);
if(!$outrec || $outrec->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $outrec;
}

}

//Eshwari -start
function get_courseoutcome_name($_id){
$outrec2 = get_record('dept_courses','id',$_id);
if(!$outrec2 || $outrec2->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $outrec2;
}
}
//eshwari -end



function can_create_coursetemplates() {
    global $USER;
    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        return true;
    }
    return false;
}


function get_coursetemplates($limit=20, $offset=0, $coursetemplateid=0, $course=0) {

    if(!$coursetemplateid){
	$coursetemplateid = 0;
    }
    if($course == 0){
    $sql = 'SELECT *
		    FROM {course_template}
		    WHERE main_coursetemplate = ?
		    AND deleted != 1
		    ORDER BY coursetemplate_name';
	$values = array($coursetemplateid);
    }else{
    $sql = 'SELECT *
		    FROM {course_template}
		    WHERE main_coursetemplate = ?
		    AND degree_id = ?
		    AND deleted != 1
		    ORDER BY coursetemplate_name';
	$values = array($coursetemplateid, $course);
    }
    
    $coursetemplates = get_records_sql_assoc($sql, $values, $offset, $limit);
    
 $count = count_records_sql('SELECT COUNT(*) FROM {course_template} WHERE main_coursetemplate = ? and deleted != 1', $values);

    if (!$coursetemplates) {
        $coursetemplates = array();
    }

    return array('coursetemplates' => $coursetemplates, 'count' => $count);

}

/*
function get_rubrics($limit=20, $offset=0, $coursetemplateid=0) {

    if(!$coursetemplateid){
	$coursetemplateid = 0;
    }
    
    $values = array($coursetemplateid);
    $rubrics =  @get_records_sql_array(
	'SELECT *
		FROM {course_rubrics}
		WHERE coursetemplate_id = ?
		AND deleted != 1
		LIMIT ?,?',
	array($coursetemplateid,$offset,$limit)
    );
    
 $count = count_records_sql('SELECT COUNT(*) FROM {course_rubrics} WHERE coursetemplate_id = ? AND deleted != 1', $values);

    if (!$coursetemplates) {
        $coursetemplates = array();
    }

    return array('rubrics' => $rubrics, 'count' => $count);

}
*/

function get_coursetemplate_prerequisites($offset=0, $limit=4, $coursetemplateid, $rubric_no) {

$sql = 'SELECT *
	  FROM {coursetemplate_prerequisites} 
        WHERE coursetemplate_id = ?
		AND rubric_no = ?
	    ORDER BY prerequisite_val';
    
    $values = array($coursetemplateid, $rubric_no);
    $coursetemplates = get_records_sql_assoc($sql, $values, $offset, $limit);

    if (!$coursetemplates) {
        $coursetemplates = array();
    }

    return $coursetemplates;
}

function find_last_offset($limit, $coursetemplateid, $rubric_no){
	$values = array($coursetemplateid, $rubric_no);
 	$countlvls = count_records_sql('SELECT COUNT(*) FROM {coursetemplate_prerequisites} WHERE coursetemplate_id = ? AND rubric_no = ?', $values);

	$pages = ceil($countlvls/$limit);
	$last_offset = ($pages-1)*$limit;
	return array('last_offset' => $last_offset, 'count' => $countlvls);
}
function coursetemplate_get_menu_tabs($coursetemplate_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'coursetemplates/info',
            'url' => 'coursetemplate/view.php?coursetemplate='.$coursetemplate_id,

            'title' => 'About',
            'weight' => 20
        ),
	);

	if($USER->get('admin') || $courseoutcomes){ 
    $menu['rubrics'] = array(
            'path' => 'courseoutcomes/courseoutcomes',
            'url' => 'coursetemplate/createcourseoutcome.php?coursetemplate='.$coursetemplate_id,
			'title' => 'Courseoutcomes',
            'weight' => 30
        );
}
	if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('coursetemplates/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}
/*
function coursetemplate_get_menu_tabs($coursetemplate_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'coursetemplates/info',
            'url' => 'coursetemplate/view.php?coursetemplate='.$coursetemplate_id,
            'title' => 'About',
            'weight' => 20
        ),
	);

if(!$USER->get('admin')){
$rubrics = @get_records_sql_array(
    'SELECT id
       FROM {course_rubrics}
       WHERE coursetemplate_id = ?
	 AND deleted = 0',
    array($coursetemplate_id)
);
$subcoursetemplates = @get_records_sql_array(
    'SELECT id
       FROM {course_template}
       WHERE main_coursetemplate = ?
	 AND deleted = 0',
    array($coursetemplate_id)
);
} 
if($USER->get('admin') || $rubrics){ 
    $menu['rubrics'] = array(
            'path' => 'coursetemplates/rubrics',
            'url' => 'coursetemplate/rubrics.php?coursetemplate='.$coursetemplate_id,
            'title' => 'Rubrics',
            'weight' => 30
        );
}

if($USER->get('admin') || $subcoursetemplates){
      $menu['subcoursetemplates'] = array(
            'path' => 'coursetemplates/subcoursetemplates',
            'url' => 'coursetemplate/coursetemplates.php?coursetemplate='.$coursetemplate_id,
            'title' => 'Sub coursetemplates',
            'weight' => 30
        );
}
if($USER->get('admin')){
      $menu['primary'] = array(
            'path' => 'coursetemplates/primary',
            'url' => 'coursetemplate/primary.php?coursetemplate='.$coursetemplate_id,
            'title' => 'Primary',
            'weight' => 30
        );
} 
//start-eshwari 
if($USER->get('admin') ){ 
    $menu['courseoutcomes'] = array(
            'path' => 'coursetemplates/courseoutcomes',
            'url' => 'coursetemplate/courseoutcomes.php?coursetemplate='.$coursetemplate_id,
            'title' => 'course outcomes',
            'weight' => 30
        );
}
//end-eshwari
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('coursetemplates/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}

function rubric_get_menu_tabs($coursetemplate_id=0, $rubric_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'coursetemplates/rubrics/info',
            'url' => 'coursetemplate/rubricview.php?coursetemplate='.$coursetemplate_id .'&rubric=' . $rubric_id,
            'title' => 'About',
            'weight' => 20
        ),
	);
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('coursetemplates/rubrics/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}
*/
//start -eshwari
function courseoutcomes_get_menu_tabs($coursetemplate_id=0, $rubric_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'coursetemplates/rubrics/info',
            'url' => 'coursetemplate/rubricview.php?coursetemplate='.$coursetemplate_id .'&rubric=' . $rubric_id,
            'title' => 'About',
            'weight' => 20
        ),
	);
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('coursetemplates/rubrics/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}
//end-eshawrai

?>
