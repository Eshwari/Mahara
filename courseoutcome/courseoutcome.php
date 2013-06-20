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
define('PUBLIC', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require('courseoutcome.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'courseoutcomes');
$offset = param_integer('offset', 'all');

$outcome_id = param_integer('courseoutcome',0);

$course_id = param_integer('course',0);

if(!$USER->get('staff') && !$USER->get('admin')){
	$usr_rec = get_record('usr','id',$USER->get('id'));
	if (!$usr_rec->degree_id) {
    		throw new AccessDeniedException("Not a valid course");
	}
	$course_id = $usr_rec->degree_id;
}

if($outcome_id == 0){
define('MENUITEM', 'course_outcomes/courseoutcomes');
define('TITLE', 'CourseOutcomes');

if($course_id == 0){
	$listoutcomes = 0;
}else{
	$course_rec = get_record('degree_programs','id',$course_id);
	if (!$course_rec) {
    		throw new AccessDeniedException("Not a valid degree program");
	}
	$listoutcomes = 1;
}
}else{

$listoutcomes = 1;
$outcomedes = is_outcome_available($outcome_id);

if (!$outcomedes) {
    throw new AccessDeniedException("Outcome does not exist");
}

define('MENUITEM', 'courseoutcomes/suboutcomes');
define('TITLE', $outcomedes->outcome_name);
}


?>
