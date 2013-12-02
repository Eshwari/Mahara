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
define('MENUITEM', 'myportfolio/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('myviews', 'view'));

$limit = param_integer('limit', 5);
$offset = param_integer('offset', 0);

$data = View::get_myviews_data($limit, $offset);

$userid = $USER->get('id');

/* Get a list of groups that the user belongs to which views can
   be sumitted. */

if (!$tutorgroupdata = @get_records_sql_array('SELECT g.id, g.name
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} t ON t.name = g.grouptype
       WHERE u.member = ?
       AND t.submittableto = 1
       ORDER BY g.name', array($userid))) {
    $tutorgroupdata = array();
}
else {
	$options = array();
	foreach ($tutorgroupdata as $group) {
	    $options[$group->id] = $group->name;
	}
    $i = 0;
    foreach ($data->data as &$view) {
        if (empty($view['submittedto'])) {
            // This form sucks from a language string point of view. It should 
            // use pieforms' form template feature
            $tempview = array(
                'name' => 'submitto' . $i++,
                'method' => 'post',
                'renderer' => 'oneline',
                'autofocus' => false,
                'successcallback' => 'submitto_submit',
                'elements' => array(
                    'text1' => array(
                        'type' => 'html',
                        'value' => get_string('submitthisviewto', 'view') . ' ',
                    ),
                ),
            );					
			
			//Start-Anusha
			if(!$view['submittedoutcome']){
				$tempview['elements']['options'] = array(
                        'type' => 'select',
                        'collapseifoneoption' => false,
                        'options' => $options,
                    );
			}else{
			   $grouprec = get_record('group','outcome',$view['submittedoutcome'],'deleted',0,'id',$view['group']);
				
				$tempview['elements']['subout'] = array (
						'type' => 'hidden',
						'value' => $grouprec->id,
				);
		        $tempview['elements']['outcometext'] = array(
                        'type' => 'html',
                        'value' => "<b>".$grouprec->name."</b> ",
                );
			}
			//End-Anusha
            $tempview['elements']['text2'] = array(
                        'type' => 'html',
                        'value' => get_string('forassessment', 'view')."<br/>",
                    );
            $tempview['elements']['submit'] = array(
                        'type' => 'submit',
                        'value' => get_string('submit')
                    );
            $tempview['elements']['view'] = array(
                        'type' => 'hidden',
                        'value' => $view['id']
                    );
			$view['submitto'] = pieform($tempview);
        }
    }
}

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'view/',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural' => get_string('views', 'view')
));

function submitto_submit(Pieform $form, $values) {
	if(!$values['subout']){
		redirect('/view/submit.php?id=' . $values['view'] . '&group=' . $values['options']);
	}else{
		redirect('/view/submit.php?id=' . $values['view'] . '&group=' . $values['subout']);
	}
	
}

$createviewform = pieform(create_view_form());

$smarty = smarty();
$smarty->assign('views', $data->data);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('PAGEHEADING', hsc(get_string('myviews')));
$smarty->assign('createviewform', $createviewform);
$smarty->display('view/index.tpl');

?>
