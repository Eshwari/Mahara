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
 * Create , update or delete outcome
*/

/**
 * Sets up groups for display in mygroups.php and find.php
 *
 * @param array $groups    Initial group data, including the current user's 
 *                         membership type in each group. See mygroups.php for
 *                         the query to build this information.
 * @param string $returnto Where forms generated for display should be told to return to
 */

function is_outcome_available($outcomeid) {
$outrec = get_record('outcomes','id',$outcomeid);
if(!$outrec || $outrec->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $outrec;
}

}

function is_rubric_available($rubricid) {
$rubrec = get_record('rubrics','id',$rubricid);
if(!$rubrec || $rubrec->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $rubrec;
}

}

function can_create_outcomes() {
    global $USER;
    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        return true;
    }
    return false;
}


function get_outcomes($limit=20, $offset=0, $outcomeid=0, $program=0) {

    if(!$outcomeid){
	$outcomeid = 0;
    }
    if($program == 0){
    $sql = 'SELECT *
		    FROM {outcomes}
		    WHERE main_outcome = ?
		    AND deleted != 1
		    ORDER BY outcome_name';
	$values = array($outcomeid);
    }else{
    $sql = 'SELECT *
		    FROM {outcomes}
		    WHERE main_outcome = ?
		    AND degree_id = ?
		    AND deleted != 1
		    ORDER BY outcome_name';
	$values = array($outcomeid, $program);
    }
    
    $outcomes = get_records_sql_assoc($sql, $values, $offset, $limit);
    
 $count = count_records_sql('SELECT COUNT(*) FROM {outcomes} WHERE main_outcome = ? and deleted != 1', $values);

    if (!$outcomes) {
        $outcomes = array();
    }

    return array('outcomes' => $outcomes, 'count' => $count);

}


function get_rubrics($limit=20, $offset=0, $outcomeid=0) {

    if(!$outcomeid){
	$outcomeid = 0;
    }
    
    $values = array($outcomeid);
    $rubrics =  @get_records_sql_array(
	'SELECT *
		FROM {rubrics}
		WHERE outcome_id = ?
		AND deleted != 1
		LIMIT ?,?',
	array($outcomeid,$offset,$limit)
    );
    
 $count = count_records_sql('SELECT COUNT(*) FROM {rubrics} WHERE outcome_id = ? AND deleted != 1', $values);

    if (!$outcomes) {
        $outcomes = array();
    }

    return array('rubrics' => $rubrics, 'count' => $count);

}

function get_outcome_levels($offset=0, $limit=4, $outcomeid, $rubric_no) {

$sql = 'SELECT *
	  FROM {outcome_levels} 
        WHERE outcome_id = ?
	  AND rubric_no = ?
	  ORDER BY level_val';
    
    $values = array($outcomeid, $rubric_no);
    $outcomes = get_records_sql_assoc($sql, $values, $offset, $limit);

    if (!$outcomes) {
        $outcomes = array();
    }

    return $outcomes;
}

function find_last_offset($limit, $outcomeid, $rubric_no){
	$values = array($outcomeid, $rubric_no);
 	$countlvls = count_records_sql('SELECT COUNT(*) FROM {outcome_levels} WHERE outcome_id = ? AND rubric_no = ?', $values);

	$pages = ceil($countlvls/$limit);
	$last_offset = ($pages-1)*$limit;
	return array('last_offset' => $last_offset, 'count' => $countlvls);
}

function outcome_get_menu_tabs($outcome_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'outcomes/info',
            'url' => 'outcome/view.php?outcome='.$outcome_id,
            'title' => 'About',
            'weight' => 20
        ),
	);
if(!$USER->get('admin')){
$rubrics = @get_records_sql_array(
    'SELECT id
       FROM {rubrics}
       WHERE outcome_id = ?
	 AND deleted = 0',
    array($outcome_id)
);
$suboutcomes = @get_records_sql_array(
    'SELECT id
       FROM {outcomes}
       WHERE main_outcome = ?
	 AND deleted = 0',
    array($outcome_id)
);
} 
if($USER->get('admin') || $rubrics){ 
    $menu['rubrics'] = array(
            'path' => 'outcomes/rubrics',
            'url' => 'outcome/rubrics.php?outcome='.$outcome_id,
            'title' => 'Rubrics',
            'weight' => 30
        );
}
if($USER->get('admin') || $suboutcomes){
      $menu['suboutcomes'] = array(
            'path' => 'outcomes/suboutcomes',
            'url' => 'outcome/outcomes.php?outcome='.$outcome_id,
            'title' => 'Sub outcomes',
            'weight' => 30
        );
}
if($USER->get('admin')){
      $menu['primary'] = array(
            'path' => 'outcomes/primary',
            'url' => 'outcome/primary.php?outcome='.$outcome_id,
            'title' => 'Primary',
            'weight' => 30
        );
}
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('outcomes/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}

function rubric_get_menu_tabs($outcome_id=0, $rubric_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'outcomes/rubrics/info',
            'url' => 'outcome/rubricview.php?outcome='.$outcome_id .'&rubric=' . $rubric_id,
            'title' => 'About',
            'weight' => 20
        ),
	);
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('outcomes/rubrics/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}

?>
