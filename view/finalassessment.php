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
require_once('view.php');
require('outcome.php');
$viewid = param_integer('id');
$outcome_id = param_integer('outcome');

$view = get_record('view', 'id', $viewid);
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.outcome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.outcome = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $outcome_id)
);

$outcomedes = is_outcome_available($outcome_id);

if(!$outcomedes){
    throw new AccessDeniedException('Outcome does not exist');
}
if(!group && $outcomedes->main_outcome){
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.outcome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.outcome = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $outcomedes->main_outcome)
);
}

if (!$view || !$group || $group->role != "chair") {
    throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
}

$assessment = get_record_sql(
    'SELECT *
       FROM {outcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND outcome = ?
	 AND rubric_no = 0',
    array($USER->get('id'), $viewid, $outcome_id)
);
if(!$assessment || !$assessment->submitted){
	throw new AccessDeniedException(get_string('cantviewassessments', 'view'));	
}


if($assessment->submitted == 2){
		$comevaluation = pieform(array(
			'name'     => 'comevaluation',
			'method'   => 'get',
			'autofocus' => false,
			'plugintype' => 'core',
			'pluginname' => 'view',
			'elements' => array(
				'finallevel' => array(
					'type' => 'html',
					'title' => 'Assign a Level',
					'value' => $assessment->final_level,
				),
				'priline' => array(
					'type' => 'html',
					'value' => '<table class="fullwidth table"><thead><tr><th></th></tr></thead></table><br/>',
				),
				'pricomments' => array(
					'type' => 'html',
					'title' => 'Comments to Committee',
					'value' => $assessment->private_comments,
					'rows'         => 10,
					'cols'         => 70,
				),
				'publine' => array(
					'type' => 'html',
					'value' => '<table class="fullwidth table"><thead><tr><th></th></tr></thead></table><br/>',
				),
				'pubcomments' => array(
					'type' => 'html',
					'title' => 'Comments to the student',
					'value' => $assessment->public_comments,
					'rows'         => 10,
					'cols'         => 70,
				),
			),
		));			
}else{

$options = array();
if($outcomedes->eval_type == 'Level'){
$outcomelevels = @get_records_sql_array(
	'SELECT *
	  FROM {outcome_levels} 
        WHERE outcome_id = ?
	  AND rubric_no = 0
	  ORDER BY level_val',
	array($outcome_id)
);
	$options['Level'] = "Select a level";
	$var = "Level ";
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

		$comevaluation = pieform(array(
			'name'     => 'comevaluation',
			'method'   => 'post',
			'autofocus' => false,
			'plugintype' => 'core',
			'pluginname' => 'view',
			'elements' => array(
				'finallevel' => array(
					'type' => 'select',
					'title' => 'Assign a Level',
					'defaultvalue' => $assessment->final_level,
					'collapseifoneoption' => false,
					'options' => $options,
				),
				'pricomments' => array(
					'type' => 'wysiwyg',
					'title' => 'Comments to Committee',
					'defaultvalue' => $assessment->private_comments,
					'rows'         => 10,
					'cols'         => 70,
				),
				'pubcomments' => array(
					'type' => 'wysiwyg',
					'title' => 'Comments to the student',
					'defaultvalue' => $assessment->public_comments,
					'rows'         => 10,
					'cols'         => 70,
				),	
				'outid' => array(
					'type' => 'hidden',
					'value' => $assessment->id,
				),
				'outcomeid' => array (
					'type' => 'hidden',
					'value' => $outcome_id,
				),
				'saveeval' => array(
					'type'  => 'submit',
					'value' => get_string('save'),
				),
				'subeval' => array(
					'type'  => 'submit',
					'value' => get_string('submit'),
				),
			),
		));		
}
	
define('TITLE', get_string('assessmenttitle', 'view'));
$smarty = smarty();

$owner = get_record('usr','id',$view->owner);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('outcomename',$outcomedes->outcome_name);
$smarty->assign('viewid',$viewid);
$smarty->assign('outcome_id',$outcome_id);
if($outcome_id == $view->submittedoutcome){
	$smarty->assign('subgroup',0);
}else{
	$smarty->assign('subgroup',$group->id);
}
$smarty->assign('comevaluation',$comevaluation);
$smarty->assign('viewtitle',$view->title);
$smarty->assign('ownername',$owner->username);
$smarty->assign('ownerlink', 'user/view.php?id=' . $view->owner);
$smarty->display('view/finalassessment.tpl');

function comevaluation_validate(Pieform $comevaluation, $values) {

	if(isset($values['subeval'])) {
		if($values['finallevel'] == 'Level'){
			$comevaluation->set_error('finallevel', get_string('levelneeded', 'view'));
		}
	}
}

function comevaluation_submit(Pieform $comevaluation, $values) {
	global $USER, $viewid; 
	db_begin();
	if(isset($_POST['subeval'])) {
		$submitted = 2;
	} else {
		$submitted = 1;
	}
	update_record('outcome_results', array('final_level' => $_POST['finallevel'],'private_comments' => $_POST['pricomments'],'public_comments' => $_POST['pubcomments'],'submitted' => $submitted),array('id' => $_POST['outid']));
	db_commit();
	redirect(get_config('wwwroot') . 'view/finalassessment.php?id='.$viewid . '&outcome=' .$_POST['outcomeid']);
}

?>
