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
require('coursetemplate.php');
$coursetemplate_id = param_integer('coursetemplate');
$prereqid = param_integer('prereqid');
$offset = param_integer('offset');
$main_offset = param_integer('main_offset');

if (!can_create_coursetemplates()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$coursetemplatedes = is_coursetemplate_available($coursetemplate_id);

if (!$coursetemplatedes) {
    throw new AccessDeniedException("Coursetemplate does not exist");
}

if ($coursetemplatedes->main_coursetemplate != 0){
$main_coursetemplate = is_coursetemplate_available($coursetemplatedes->main_coursetemplate);

if (!$main_coursetemplate) {
    throw new AccessDeniedException("coursetemplate does not exist");
}
	define('MENUITEM', 'coursetemplates/subcoursetemplates');

}else{
	define('MENUITEM', 'coursetemplates/coursetemplates');
}

$coursetemplate_prereq = get_record('course_prerequisites','id',$prereqid);

if(!$coursetemplate_prereq){
	throw new AccessDeniedException('coursetemplate pre-req does not exist');
}

define('TITLE', $coursetemplatedes->coursetemplate_name);

$form = pieform(array(
    'name' => 'deletegroup',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'coursetemplate/edit.php?coursetemplate=' . $coursetemplate_id . '&main_offset=' . $main_offset . '&offset=' . $offset
        )
    ),
));

$smarty = smarty();
$smarty->assign('message', 'Do you want to delete pre-req ' . $coursetemplate_prereq->prerequisite_val . ' for the coursetemplate?' );
$smarty->assign('form', $form);

$smarty->assign('main_coursetemplate',$coursetemplatedes->main_coursetemplate);

if($coursetemplatedes->main_coursetemplate != 0){
$smarty->assign('COURSETEMPLATENAV', coursetemplate_get_menu_tabs($main_coursetemplate->id));
$smarty->assign('PAGEHEADING', $main_coursetemplate->coursetemplate_name);
$smarty->assign('header', 'Delete Sub coursetemplate Level');
}else{
$smarty->assign('COURSETEMPLATENAV','');
$smarty->assign('PAGEHEADING', 'Delete coursetemplate Pre-req');
$smarty->assign('header', '');

}
$smarty->display('coursetemplate/deletepre.tpl');

function deletegroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $coursetemplate_id, $prereqid, $offset, $coursetemplate_prereq;

     db_begin();
     delete_records('course_prerequisites','id',$prereqid);
     db_commit();
    $SESSION->add_ok_msg('prereq ' . $coursetemplate_prereq->prerequisite_val . ' for the coursetemplate deleted');
    redirect(get_config('wwwroot') . 'coursetemplate/edit.php?coursetemplate=' . $coursetemplate_id . '&main_offset=' . $main_offset . '&offset=' . $offset);
}
?>
