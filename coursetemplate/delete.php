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
require('courseoutcome.php');
$courseoutcome_id = param_integer('courseoutcome');
$offset = param_integer('offset');


if (!can_create_courseoutcomes()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseoutcome_rec = is_courseoutcome_available($courseoutcome_id);

if($courseoutcome_id == 0 || !$courseoutcome_rec){
	throw new AccessDeniedException('courseoutcome does not exist');
}

if($courseoutcome_rec->main_courseoutcome == 0){
	define('MENUITEM', 'courseoutcomes/courseoutcomes');
}else{
$main_courseoutcome = is_courseoutcome_available($courseoutcome_rec->main_courseoutcome);

if (!$main_courseoutcome) {
    throw new AccessDeniedException("Courseoutcome does not exist");
}
	define('MENUITEM', 'courseoutcomes/subcourseoutcomes');
}

define('TITLE', $courseoutcome_rec->courseoutcome_name);

$form = pieform(array(
    'name' => 'deletecourseoutcome',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'courseoutcome/courseoutcomes.php?courseoutcome=' . $courseoutcome_rec->main_courseoutcome . '&offset=' . $offset
        )
    ),
));

$smarty = smarty();
$smarty->assign('subheading', hsc(TITLE));
$smarty->assign('message', 'Do you want to delete the courseoutcome - ' . $courseoutcome_rec->courseoutcome_name);
$smarty->assign('form', $form);
$smarty->assign('main_outcome',$main_courseoutcome->main_courseoutcome);
if($courseoutcome_rec->main_courseoutcome != 0){
$smarty->assign('COURSEOUTCOMENAV', courseoutcome_get_menu_tabs($courseoutcome_rec->main_courseoutcome));
$smarty->assign('PAGEHEADING', $main_courseoutcome->courseoutcome_name);
$smarty->assign('header', 'Delete Sub courseoutcome');
}else{
$smarty->assign('COURSEOUTCOMENAV','');
$smarty->assign('PAGEHEADING', 'Delete Courseoutcome');
$smarty->assign('header', '');
}

$smarty->display('courseoutcome/delete.tpl');

function deletecourseoutcome_submit(Pieform $form, $values) {
    global $SESSION, $USER, $courseoutcome_id, $levelid, $offset, $courseoutcome_rec;

$subcourseoutcomes = @get_records_sql_array(
    'SELECT id
       FROM {courseoutcomes}
       WHERE main_courseoutcome = ?
	 AND deleted = 0',
    array($courseoutcome_rec->id)
);
     db_begin();
     update_record('courseoutcomes',array('deleted' => 1), array('id' =>$courseoutcome_rec->id));

foreach($subcourseoutcomes as $subcourseoutcome){
     update_record('courseoutcomes',array('deleted' => 1), array('id' =>$subcourseoutcome->id));
}
     db_commit();
    $SESSION->add_ok_msg('courseoutcome deleted');
    redirect(get_config('wwwroot') . 'courseoutcome/courseoutcomes.php?courseoutcome=' . $courseoutcome_rec->main_courseoutcome. '&offset=' . $offset);
}
?>
