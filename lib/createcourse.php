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
 
 function is_createcourse_available($createcourseid) {
$courserec = get_record('course_template','id',$createcourseid);
if(!$courserec || $courserec->deleted == 1){
	$notfound = array();
	return $notfound;
}else{
	return $courserec;
}

}
 //get_createcourse function
 function get_createcourse($limit=20, $offset=0, $createcourseid=0, $course=0) {

    if(!$createcourseid){
	$createcourseid = 0;
    }
    if($course == 0){
    $sql = 'SELECT *
		    FROM {course_template}
		    WHERE 
		    deleted != 1
		    ORDER BY course_name';
	$values = array($createcourseid);
    }else{
    $sql = 'SELECT *
		    FROM {course_template}
		    AND deleted != 1
		    ORDER BY course_name';
	$values = array($createcourseid, $course);
    }
    
    $createcourses = get_records_sql_assoc($sql, $values, $offset, $limit);
    
 $count = count_records_sql('SELECT COUNT(*) FROM {course_template} WHERE deleted != 1', $values);

    if (!$createcourses) {
        $createcourses = array();
    }

    return array('createcourses' => $createcourses, 'count' => $count);

}
 function can_create_courses() {
    global $USER;
    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        return true;
    }
    return false;
}
 function createcourse_get_menu_tabs($createcourse_id=0) {
static $menu;
global $USER;
    $menu = array(
        'info' => array(
            'path' => 'createcourse/info',
            'url' => 'createcourse/view.php?course='.$createcourse_id,
            'title' => 'About',
            'weight' => 20
        ),
	);

    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('createcourse/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }
return $menu;
}
 
 ?>