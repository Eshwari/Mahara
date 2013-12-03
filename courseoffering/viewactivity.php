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
define('MENUITEM', 'courseofferings/activities/info');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('courseoffering.php');
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'file');
if (!$USER->is_logged_in()) {
    throw new AccessDeniedException();
}

$courseoffering_id = param_integer('courseoffering');
$activityid = param_integer('activity');
$offset = param_integer('offset',0);

$courseofferingrec = is_courseoffering_available($courseoffering_id);
$activity = is_activity_available($activityid);

if (!$activity) {
    throw new AccessDeniedException("Activity does not exist for the courseoffering");
}

if (!$courseofferingrec) {
    throw new AccessDeniedException("Courseoffering does not exist");
}


define('TITLE', $courseofferingrec->courseoffering_name);

$smarty = smarty();
$smarty->assign('courseoffering_id',$courseoffering_id);
$smarty->assign('PAGEHEADING', $courseofferingrec->courseoffering_name);
$smarty->assign('act_name',$activity->activity_name);
$smarty->assign('act_desc',$activity->description);
//$smarty->assign('outlevelsdes',$outlevelsdes);
$smarty->assign('COURSEOFFERINGNAV', activity_get_menu_tabs($courseoffering_id, $activityid));
$smarty->display('courseoffering/viewactivity.tpl');

?>