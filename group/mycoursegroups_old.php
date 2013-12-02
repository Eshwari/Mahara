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
define('MENUITEM', 'coursegroups/mycoursegroups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('mycoursegroups'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'coursegroup');
define('SECTION_PAGE', 'mycoursegroups');
require('coursegroup.php');
$filter = param_alpha('filter', 'all');
$offset = param_integer('offset', 'all');

$coursegroupsperpage = 20;
$offset = (int)($offset / $coursegroupsperpage) * $coursegroupsperpage;

$results = coursegroup_get_associated_coursegroups($USER->get('id'), $filter, $coursegroupsperpage, $offset);

$form = pieform(array(
    'name'   => 'filter',
    'method' => 'post',
    'renderer' => 'oneline',
    'elements' => array(
        'options' => array(
            'type' => 'select',
            'options' => array(
                'all'     => get_string('allmycoursegroups', 'coursegroup'),
                'admin'   => get_string('coursegroupsiown', 'coursegroup'),
                'member'  => get_string('coursegroupsimin', 'coursegroup'),
                'invite'  => get_string('coursegroupsiminvitedto', 'coursegroup'),
                'request' => get_string('coursegroupsiwanttojoin', 'coursegroup')
            ),
            'defaultvalue' => $filter
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('filter')
        )
    ),
));

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'coursegroup/mycoursegroups.php?filter=' . $filter,
    'count' => $results['count'],
    'limit' => $coursegroupsperpage,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('coursegroup', 'coursegroup'),
    'resultcounttextplural' => get_string('coursegroups', 'coursegroup'),
));

coursegroup_prepare_usercoursegroups_for_display($results['coursegroups'], 'mycoursegroups');

$smarty = smarty();
$smarty->assign('coursegroups', $results['coursegroups']);
$smarty->assign('cancreate', coursegroup_can_create_coursegroups());
$smarty->assign('form', $form);
$smarty->assign('filter', $filter);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('searchingforcoursegroups', array('<a href="' . get_config('wwwroot') . 'coursegroup/find.php">', '</a>'));
$smarty->assign('PAGEHEADING', hsc(get_string('mycoursegroups')));
$smarty->display('coursegroup/mycoursegroups.tpl');

function filter_submit(Pieform $form, $values) {
    redirect('/coursegroup/mycoursegroups.php?filter=' . $values['options']);
}

?>
