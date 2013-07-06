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
require_once('courseoutcome.php');

$courseoutcome_id = param_integer('courseoutcome',0);

if (!can_create_courseoutcomes()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$courseoutcomedes = is_courseoutcome_available($courseoutcome_id);

define('TITLE', $courseoutcomedes->courseoutcome_name);

if (!$courseoutcomedes) {
    throw new AccessDeniedException("Outcome does not exist");
}


$primaryfocus = @get_records_sql_array(
	'SELECT *
		FROM {primary_focus}
		WHERE degree_id = ?',
	array($courseoutcomedes->degree_id)
);

$programs = array();
$programs['primary'] = 'Select a primary focus area';
foreach($primaryfocus as $program){
	$programs[$program->id] = $program->primary_focus_name;
}

$createcourseoutcome = array(
    'name'     => 'createcourseoutcome',
    'method'   => 'post',
    'elements' => array(),
);

       $createcourseoutcome['elements']['degree'] = array(
            'type'         => 'select',
            'title'        => 'Associate courseoutcome to a primary focus area',
            'options'      => $programs,
		'defaultvalue' => $courseoutcomedes->special_access,
        );
        $createcourseoutcome['elements']['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array('Save', 'Cancel'),
        );

$createcourseoutcome = pieform($createcourseoutcome);
$smarty = smarty();
$smarty->assign('createcourseoutcome', $createcourseoutcome);
$smarty->assign('PAGEHEADING', $courseoutcomedes->courseoutcome_name);
$smarty->assign('main_courseoutcome',$courseoutcomedes->main_courseoutcome);
$smarty->assign('OUTCOMENAV', courseoutcome_get_menu_tabs($courseoutcome_id));
$smarty->display('courseoutcome/create.tpl');


function createcourseoutcome_cancel_submit(Pieform $form, $values) {
global $courseoutcome_id;
    redirect(get_config('wwwroot').'/courseoutcome/view.php?courseoutcome=' . $courseoutcome_id);
}

function createcourseoutcome_submit(Pieform $form, $values) {
   global $USER;
    global $SESSION;
global $courseoutcome_id;

if($_POST['degree'] == 'primary'){
	$primary = NULL;
}else{
	$primary = $_POST['degree'];
}
db_begin();
update_record('courseoutcomes', array('special_access' => $primary),array('id'=> $courseoutcome_id));
db_commit();

    $SESSION->add_ok_msg('Outcome associated to a primary focus area');

    redirect(get_config('wwwroot').'/courseoutcome/view.php?courseoutcome=' . $courseoutcome_id);
}

?>
