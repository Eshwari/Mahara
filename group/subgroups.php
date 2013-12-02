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
define('MENUITEM', 'groups/subgroups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'file');

define('GROUP', param_integer('id'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}


define('TITLE', $group->name);
$group->ctime = strftime(get_string('strftimedate'), $group->ctime);

$smarty = smarty();
$smarty->assign('group', $group);
$smarty->assign('groupid', $group->id);
$smarty->assign('foruminfo', $foruminfo);
$smarty->assign('membercount', count_records('group_member', 'group', $group->id));
$smarty->assign('viewcount', count_records('view', 'group', $group->id));
$smarty->assign('filecount', $filecounts->files);
$smarty->assign('foldercount', $filecounts->folders);
$subgroup = 0;

$subgroupsperpage = 20;
$offset = (int)($offset / $subgroupserpage) * $subgroupsperpage;
$results = get_subgroups($subgroupsperpage, $offset, $group->id);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'group/subgroups.php?view=' . $groupid,
    'count' => $results['count'],
    'limit' => $subgroupsperpage,
    'offset' => $offset,
    'resultcounttextsingular' => 'subgroup',
    'resultcounttextplural' => 'subgroups',
));
$smarty->assign('subgroups', $results['subgroups']);
$smarty->assign('cancreate', can_create_subgroups());
$smarty->assign('groupid',$group->id);
$smarty->assign('outcome',$group->outcome);
$smarty->display('group/subgroups.tpl');

?>
