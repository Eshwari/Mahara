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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('view.php');
require_once('activity.php');
$viewid = param_integer('id');
$groupid = param_integer('group');

$view = get_record('view', 'id', $viewid, 'owner', $USER->get('id'));
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.id = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $groupid)
);

if (!$view || !$group || $view->submittedgroup || $view->submittedhost) {
    throw new AccessDeniedException(get_string('cantsubmitviewtogroup', 'view'));
}

define('TITLE', get_string('submitviewtogroupformative', 'view', $view->title, $group->name));

// Start -Anusha

if($view->submittedoutcome){

$options = array();
$outcomedes = get_record('outcomes','id',$view->submittedoutcome);
if($outcomedes->eval_type == 'Level'){
	$outcomelevels = @get_records_sql_array(
	'SELECT *
	  FROM {outcome_levels} 
        WHERE outcome_id = ?
	  AND rubric_no = 0
	  ORDER BY level_val',
	array($view->submittedoutcome)
	);
	$options['Level'] = "Select a level";
	$var = "Level";
	foreach($outcomelevels as $outcomelevel){
		$options[$var.$outcomelevel->level_val] = $var.$outcomelevel->level_val;
	}
}else{
				$options['Level'] = "Select";
				$subst = $outcomedes->eval_type;
				while($subst){					
					$ind = strpos($subst,'/');
					if($ind === false){
						$options[$subst] = $subst;
						$subst = "";
					}else{	
						$str = substr($subst,0,$ind);
						$tempstr = $subst;
						$subst = substr($tempstr,$ind+1);
						$options[$str] = $str;							}
				}
}

$form = pieform(array(
    'name' => 'submitview',
    'autofocus' => false,
    'method' => 'post',
	'elements' => array(
	// Display the self assessment part
        'options' => array(
			'type' => 'select',
			'title' => 'Set a level againt the outcome',
            'collapseifoneoption' => false,
            'options' => $options,
        ),
		'break' => array(
			'type' => 'html',
			'value' => '<br/>',
        ),
        'explain' => array(
            'type' => 'wysiwyg',
			'title' => 'Explain',
            'rows'         => 10,
            'cols'         => 70,
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'view/'
        ),
		'isOutcome' => array(
            'type' => 'hidden',
			'value' => 1,
        ),
    ),
));

}else{
// End- Anusha

$form = pieform(array(
    'name' => 'submitview',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'elements' => array(
		//Start - Anusha
		'isOutcome' => array(
            'type' => 'hidden',
			'value' => 0,
        ),
		//End - Anusha
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'view/'
        )
    ),
));

// Start- Anusha
}
// End- Anusha

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));
//$smarty->assign('message', get_string('submitviewconfirm', 'view', $view->title, $group->name));
$smarty->assign('form', $form);
$smarty->display('view/submit.tpl');

//Start- Anusha
function submitview_validate(Pieform $form, $values) {

	if($values['isOutcome']){
		if($values['options'] == 'Level'){
			$form->set_error('options', get_string('selflevelneeded', 'view'));
		}
		if($values['explain'] == ""){
			$form->set_error('explain', get_string('descriptionneeded', 'view'));
		}
	}
}
//End-Anusha

function submitview_submit(Pieform $form, $values) {
    global $SESSION, $USER, $viewid, $groupid, $group;
    db_begin();
	
	//Start-Anusha
	//Store the self assessment in the db
	
	//update_record('view', array('submittedgroup' => $groupid), array('id' => $viewid));
	
	if($_POST['isOutcome']){
		update_record('view', array(/*'submittedgroup' => $groupid,*/'self_level_formative' => $_POST['options'],'self_describe_formative' => $_POST['explain']), array('id' => $viewid));
	}else{
		update_record('view', array(/*'submittedgroup' => $groupid,*/'self_level_formative' => null,'self_level_formative' => null), array('id' => $viewid));
	}
    //End-Anusha
	
    activity_occurred('groupmessage', array(
        'subject'       => get_string('viewsubmittedformative', 'view'), // will be overwritten
        'message'       => get_string('viewsubmittedformative', 'view'), // will be overwritten
        'submittedview' => $viewid,
        'viewowner'     => $USER->get('id'),
        'group'         => $groupid,
        'roles'         => get_column('grouptype_roles', 'role', 'grouptype', $group->grouptype, 'see_submitted_views', 1),
    ));
    db_commit();
    $SESSION->add_ok_msg(get_string('viewsubmittedformative', 'view'));
    redirect('/view/');
}
?>
