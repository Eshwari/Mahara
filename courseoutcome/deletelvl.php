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
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require('courseoutcome.php');
$courseoutcome_id = param_integer('courseoutcome');
$levelid = param_integer('levelid');
$offset = param_integer('offset');
$main_offset = param_integer('main_offset');

if (!can_create_courseoutcomes()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseoutcomedes = is_courseoutcome_available($courseoutcome_id);

if (!$courseoutcomedes) {
    throw new AccessDeniedException("Courseoutcome does not exist");
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

$courseoutcome_lvl = get_record('courseoutcome_levels','id',$levelid);

if(!$courseoutcome_lvl){
	throw new AccessDeniedException('courseoutcome level does not exist');
}

define('TITLE', $courseoutcomedes->courseoutcome_name);

$form = pieform(array(
    'name' => 'deletegroup',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'courseoutcome/edit.php?courseoutcome=' . $courseoutcome_id . '&main_offset=' . $main_offset . '&offset=' . $offset
        )
    ),
));

$smarty = smarty();
$smarty->assign('message', 'Do you want to delete level ' . $courseoutcome_lvl->level_val . ' for the courseoutcome?' );
$smarty->assign('form', $form);

$smarty->assign('main_courseoutcome',$courseoutcomedes->main_courseoutcome);

if($courseoutcomedes->main_courseoutcome != 0){
$smarty->assign('COURSEOUTCOMENAV', courseoutcome_get_menu_tabs($main_courseoutcome->id));
$smarty->assign('PAGEHEADING', $main_courseoutcome->courseoutcome_name);
$smarty->assign('header', 'Delete Sub courseoutcome Level');
}else{
$smarty->assign('COURSEOUTCOMENAV','');
$smarty->assign('PAGEHEADING', 'Delete courseoutcome Level');
$smarty->assign('header', '');

}
$smarty->display('courseoutcome/deletelvl.tpl');

function deletegroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $courseoutcome_id, $levelid, $offset, $courseoutcome_lvl;

     db_begin();
     delete_records('courseoutcome_levels','id',$levelid);
     db_commit();
    $SESSION->add_ok_msg('Level ' . $courseoutcome_lvl->level_val . ' for the courseoutcome deleted');
    redirect(get_config('wwwroot') . 'courseoutcome/edit.php?courseoutcome=' . $courseoutcome_id . '&main_offset=' . $main_offset . '&offset=' . $offset);
}
?>
