<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-outcome
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/courseoutcomes');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'courseoutcome');

$userid = param_integer('user', 0);

if ($userid && $userid != $USER->get('id')) {
    $user = new User;
    $user->find_by_id($userid);
    if (!$USER->is_admin_for_user($user)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
}
else {
    $user = $USER;
}

$outcomedata = ArtefactTypeViewoutcome::user_outcome_data($user->get('id'));
if ($outcomedata) {
    foreach ($outcomedata as &$o) {
        $o->sum = 0;
        foreach ($o->grades as $g) {
            $o->sum += $g->grade;
        }
        $o->views = count($o->grades);
        $o->grade = $o->scale[round($o->sum/$o->views)];
    }
}

define('TITLE', get_string('importedcourseoutcomesfor', 'artefact.courseoutcome', display_name($user)));

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('courseoutcomes', $courseoutcomedata);
$smarty->assign('user', $user);
$smarty->display('artefact:courseoutcome:index.tpl');

?>
