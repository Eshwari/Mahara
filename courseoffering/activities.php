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
define('MENUITEM', 'courseofferings/activities');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('courseoffering.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'activities');
$offset = param_integer('offset', 'all');


$courseoffering_id = param_integer('courseoffering',0);
$offset = param_integer('offset',0);



if (!can_create_courseofferings()) {
	
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$courseofferingdes = is_courseoffering_available($courseoffering_id);

if (!$courseofferingdes) {
    throw new AccessDeniedException("Course Offering does not exist");
}
define('TITLE', $courseofferingdes->courseoffering_name);

$activityperpage = 20;
$offset = (int)($offset / $activityperpage) * $activityperpage;

$results = get_activities($activityperpage, $offset, $courseoffering_id);


$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'courseoffering/activities.php?courseoffering=' . $courseoffering_id ,
    'count' => $results['count'],
    'limit' => $activityperpage,
    'offset' => $offset,
    'resultcounttextsingular' => 'activity',
    'resultcounttextplural' => 'activities',
));

$smarty = smarty();
$smarty->assign('activities', $results['activities']);
$smarty->assign('cancreate', can_create_activities());
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('PAGEHEADING', $courseofferingdes->courseoffering_name);
$smarty->assign('courseofferingid',$courseoffering_id);
$smarty->assign('main_courseoffering',$courseofferingdes->main_courseoffering);
$smarty->assign('offset',$offset);
$smarty->assign('COURSEOFFERINGNAV', courseoffering_get_menu_tabs($courseoffering_id));

$smarty->display('courseoffering/activities.tpl');



































?>