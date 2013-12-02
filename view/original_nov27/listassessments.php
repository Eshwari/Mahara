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

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require('group.php');
require('outcome.php');

$viewid = param_integer('id');
$outcome_id = param_integer('outcome');
$first = param_integer('first',0);
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

$accessview = 0;
if($outcomedes->id == $view->submittedoutcome){
	$accessview = 1;
}
$main_outcome = 0;
if($outcomedes->main_outcome){
	$mainoutcomedes = is_outcome_available($outcomedes->main_outcome
);
if(!$mainoutcomedes){
    throw new AccessDeniedException('Outcome does not exist');
}
	$mainoutcomename = $mainoutcomedes->outcome_name;

if(!$mainoutcomedes->eval_required){
	$main_outcome = $mainoutcomedes->id;
}
$mainoutcome = $mainoutcomedes;

if(!$accessview && $view->submittedoutcome == $outcomedes->main_outcome){
	$accessview = 1;
}
while((!$group || !$accessview) && $mainoutcome){

if(!$group && !$mainoutcome->eval_required){
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.outcome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.outcome = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $mainoutcome->id)
);
}
	$tempout = $mainoutcome->main_outcome;
	$mainoutcome = "";
	if($tempout){
		if($tempout == $view->submittedoutcome){
			$accessview = 1;
		}
		$mainoutcome = is_outcome_available($tempout);
		if(!$mainoutcome){
    			throw new AccessDeniedException('Outcome does not exist');
		}
	}

}
}else{
	$mainoutcomename = "";
}

if (!$view || !$group || !$accessview) {
    throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
}

$ownerrec = get_record('usr','id',$view->owner);
if($outcomedes->special_access){
	if($outcomedes->special_access != $ownerrec->primary_focus){
    		throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
	}
}

$memval = 'member';
$adminval = 'admin';

$allsuboutcomes = @get_records_sql_array(
	'SELECT id, outcome_name, eval_required, special_access
	    FROM {outcomes}
	    WHERE main_outcome = ?
	    AND deleted = 0',
	array($outcome_id)
);

$suboutcomes = array();
$i = -1;
foreach($allsuboutcomes as $allsuboutcome){
	if($allsuboutcome->special_access){
		if($allsuboutcome->special_access == $ownerrec->primary_focus){
			$i = $i + 1;
			$suboutcomes[$i] = array();
			$suboutcomes[$i]['id'] = $allsuboutcome->id;
			$suboutcomes[$i]['outcome_name'] = $allsuboutcome->outcome_name;
			$suboutcomes[$i]['eval_required'] = $allsuboutcome->eval_required;

					$grouprec = get_record('group',outcome, $allsuboutcome->id);
			$groupmems = @get_records_sql_array(
	'SELECT member
	    FROM {group_member} gm
	    WHERE gm.group = ?
	    AND gm.role != ?
	    AND gm.role != ?',
	array($grouprec->id, $memval, $adminval)
);
		$suboutcomes[$i]['groupmemscnt'] = count($groupmems);

		}
	}else{
			$i = $i + 1;
			$suboutcomes[$i] = array();
			$suboutcomes[$i]['id'] = $allsuboutcome->id;
			$suboutcomes[$i]['outcome_name'] = $allsuboutcome->outcome_name;
			$suboutcomes[$i]['eval_required'] = $allsuboutcome->eval_required;
			
			$grouprec = get_record('group',outcome, $allsuboutcome->id);
			$groupmems = @get_records_sql_array(
	'SELECT member
	    FROM {group_member} gm
	    WHERE gm.group = ?
	    AND gm.role != ?
	    AND gm.role != ?',
	array($grouprec->id, $memval, $adminval)
);
		$suboutcomes[$i]['groupmemscnt'] = count($groupmems);
	}
}

$allsubmitted = 0;

if(!$suboutcomes){
$suboutcomes = array();
$allsubmitted = 1;
}

if($suboutcomes && $outcomedes->eval_required){
$allsubmitted = 1;
	for($i = 0; $i < count($suboutcomes); $i++){
		$suboutcomedet = @get_records_sql_array(
    'SELECT *
       FROM {outcome_results} 
       WHERE outcome = ?
       AND view_id = ?
       AND rubric_no = 0
       AND (submitted = 1 OR submitted = 2)',
    array($suboutcomes[$i]['id'], $viewid)
	);	
		if(!$suboutcomedet){
			$allsubmitted = 0;
		}else if(count($suboutcomedet) < $suboutcomes[$i]['groupmemscnt']){
			$allsubmitted = 0;
		}
	}
}

$rubrics = @get_records_sql_array(
	'SELECT rubric_no,rubric_name
		FROM {rubrics} 
        WHERE outcome_id = ?
	  AND deleted = 0',
	array($outcome_id)
);

$rubriclevels = array();

if($rubrics){
	$rubricsfound = 1;
}

		$outheader = array(
			'name'     => 'outheader',
			'method'   => 'post',
			'autofocus' => false,
			'plugintype' => 'core',
			'pluginname' => 'view',
			'elements' => array(),
		);

$outlevelsdes = "";
$hdravail = 0;
if($outcomedes->description){
	$outlevelsdes = $outlevelsdes . 'Description:  '. $outcomedes->description;
}

$rubriclevels = array();
if($outcomedes->eval_type == 'Level'){
$outcomelevels = @get_records_sql_array(
	'SELECT *
	  FROM {outcome_levels} 
        WHERE outcome_id = ?
	  AND rubric_no = 0
	  ORDER BY level_val',
	array($outcome_id)
);

$foptions = array();
$foptions['Level'] = "Select a level";
$var = "Level ";

foreach($outcomelevels as $outcomelevel){
$foptions[$var.$outcomelevel->level_val] = $var.$outcomelevel->level_val;
	$outlevelsdes = $outlevelsdes .'<br/><b>Level' . $outcomelevel->level_val . '</b>: '.$outcomelevel->level_desc;
}

}

if($outlevelsdes){
$hdravail = 1;
$outlevelsdes .'<br/>';
}

$outheader['elements']['outcomelevels'] = array(
	'type' => 'html',
	'value' => $outlevelsdes,
);

$comevaluation  = array();
if($allsubmitted){	
$hdravail = 1;
		$comevaluation = array(
			'name'     => 'comevaluation',
			'method'   => 'post',
			'autofocus' => false,
			'plugintype' => 'core',
			'pluginname' => 'view',
			'elements' => array(),
		);

$assessments = @get_records_sql_array(
    'SELECT v.rubric_no,v.level_assigned,v.comments
       FROM {outcome_results} v 
	   WHERE v.member = ? 
	   AND v.view_id = ?
	   AND v.outcome = ?',
    array($USER->get('id'),$viewid, $outcome_id)
);

$issubmitted = get_record_sql(
    'SELECT level_assigned,comments,submitted
       FROM {outcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND outcome = ?
	 AND rubric_no = 0',
    array($USER->get('id'), $viewid, $outcome_id)
);
if($issubmitted && $issubmitted->submitted) {
		
		$evallvl = array();
		$evalcomm = array();
		$trubrics = $rubrics;
		foreach ($trubrics as $rubric) {
			$evallvl[$rubric->rubric_no] = "";
			$evalcomm[$rubric->rubric_no] = "";
		}
		foreach ($assessments as $assessment) {
			$evallvl[$assessment->rubric_no] = $assessment->level_assigned;
			$evalcomm[$assessment->rubric_no] = $assessment->comments;
		}
		
		if($outcomedes->eval_type == "Level"){

		$outheader['elements']['finalassess'] = array(
				'type' => 'html',
				'value' => '<br/><h3 align="center">Outcome Assessment </h3>',
		);
		$outheader['elements']['final'] = array(
				'type' => 'html',
				'value' => '<table class="fullwidth table"><thead><tr><th></th></tr></thead></table><br/>',
		);
		$outheader['elements']['assignlevel'] = array(
					'type' => 'html',
					'title' => 'Final Level',
					'value' => '<b>' . $evallvl[0] . '</b>' ,
		);
		$outheader['elements']['comments'] = array(
					'type' => 'html',
					'title' => 'Comments',
					'value' => '<b>' . $evalcomm[0] . '</b>',
		);
		
		$j=0;
		foreach($rubrics as $eachval){
			
		$rublevels = @get_records_sql_array(
	'SELECT *
	  FROM {outcome_levels} 
        WHERE outcome_id = ?
	  AND rubric_no = ?
	  ORDER BY level_val',
	array($outcome_id,$eachval->rubric_no)
);

		$options = array();
		$options['Level'] = "Select a level";
		$var = "Level ";

		$rubval = '<b>' . $eachval->rubric_name . '</b><br/>';
		foreach($rublevels as $rublevel){
			$options[$var.$rublevel->level_val] = $var.$rublevel->level_val;
			$rubval = $rubval . '<br/><b>' .  $rublevel->level_val . '</b>: ' . $rublevel->level_desc;
            }

			$j = $j + 1;
			/*$comevaluation['elements']['rubric'.$j] = array(
				'type' => 'html',
				'value' => '<table class="fullwidth table"><thead><tr><th>'.$eachval->rubric_name.'</th></tr></thead></table><br/>'.'<b>Level1</b>: '.$eachval->level1.'<br/><b>Level2</b>: '.$eachval->level2.'<br/><b>Level3</b>: '.$eachval->level3.'<br/><b>Level4</b>: '.$eachval->level4.'<br/>',
			);*/
			$comevaluation['elements']['line'.$j] = array(
				'type' => 'html',
				'value' => '<table class="fullwidth table"><thead><tr><th></th></tr></thead></table>',
			);
			/*$comevaluation['elements']['rubric'.$j] = array(
				'type' => 'html',
				'value' => '<b>' . $eachval->rubric_name . '</b>'.'<br/><br/><b>Level1</b>: '.$eachval->level1.'<br/><b>Level2</b>: '.$eachval->level2.'<br/><b>Level3</b>: '.$eachval->level3.'<br/><b>Level4</b>: '.$eachval->level4.'<br/><br/>',
			);*/

			$comevaluation['elements']['rubric'.$j] = array(
				'type' => 'html',
				'value' => $rubval,
			);

			$comevaluation['elements']['assignlevel'.$j] = array(
				'type' => 'html',
				'title' => 'Assigned Level',
				'value' => '<b>' .$evallvl[$eachval->rubric_no].'</b>',
			);
			$comevaluation['elements']['comments'.$j] = array(
				'type' => 'html',
				'title' => 'Comments',
				'value' => '<b>' .$evalcomm[$eachval->rubric_no].'</b>',
			);
		}
		/*$comevaluation['elements']['final'] = array(
				'type' => 'html',
				'value' => '<table class="fullwidth table"><thead><tr><th>Final Assessment</th></tr></thead></table><br/>',
		);*/
		}else{	
		$outheader['elements']['finalassess'] = array(
				'type' => 'html',
				'value' => '<br/><h3 align="center">Outcome Assessment </h3>',
		);
		$outheader['elements']['final'] = array(
				'type' => 'html',
				'value' => '<table class="fullwidth table"><thead><tr><th></th></tr></thead></table><br/>',
		);
		$outheader['elements']['assignlevel'] = array(
					'type' => 'html',
					'title' => 'Assessment',
					'value' => '<b>' . $evallvl[0] . '</b>' ,
		);
		$outheader['elements']['comments'] = array(
					'type' => 'html',
					'title' => 'Comments',
					'value' => '<b>' . $evalcomm[0] . '</b>',
		);	
				$options = array();
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
			$j=0;
		foreach($rubrics as $eachval){
			
			$j = $j + 1;
			$comevaluation['elements']['rubric'.$j] = array(
				'type' => 'html',
				'value' => '<br/><h6>' . $j . ". " . $eachval->rubric_name,
			);
			$comevaluation['elements']['assignlevel'.$j] = array(
				'type' => 'html',
				'title' => 'Assessment',
				'value' => '<b>' .$evallvl[$eachval->rubric_no].'<b>',
			);
			$comevaluation['elements']['comments'.$j] = array(
				'type' => 'html',
				'title' => 'Comments',
				'value' => '<b>' .$evalcomm[$eachval->rubric_no].'<b>',
			);
		}

		}
	
}else {

		$evallvl = array();
		$evalcomm = array();	
		$trubrics = $rubrics;
		foreach ($trubrics as $rubric) {
			$evallvl[$rubric->rubric_no] = "";
			$evalcomm[$rubric->rubric_no] = "";
		}
		foreach ($assessments as $assessment) {
			$evallvl[$assessment->rubric_no] = $assessment->level_assigned;
			$evalcomm[$assessment->rubric_no] = $assessment->comments;
		}
		
		$comevaluation['elements']['outcomeid'] = array (
			'type' => 'hidden',
			'value' => $outcome_id,
		);

		$comevaluation['elements']['main_outcome'] = array (
			'type' => 'hidden',
			'value' => $main_outcome,
		);

		$comevaluation['elements']['first'] = array (
			'type' => 'hidden',
			'value' => $first,
		);

		$comevaluation['elements']['allsubmitted'] = array (
			'type' => 'hidden',
			'value' => $allsubmitted,
		);

		if($outcomedes->eval_type == "Level"){
		
		$j=0;

		foreach($rubrics as $eachval){
			
			$rublevels = @get_records_sql_array(
	'SELECT *
	  FROM {outcome_levels} 
        WHERE outcome_id = ?
	  AND rubric_no = ?
	  ORDER BY level_val',
	array($outcome_id,$eachval->rubric_no)
);

		$options = array();
		$options['Level'] = "Select a level";
		$var = "Level ";

		$rubval = '<b>' . $eachval->rubric_name . '</b><br/>';
		foreach($rublevels as $rublevel){
			$options[$var.$rublevel->level_val] = $var.$rublevel->level_val;
			$rubval = $rubval . '<br/><b>' .  $rublevel->level_val . '</b>: ' . $rublevel->level_desc;
            }
			$j = $j + 1;
			$comevaluation['elements']['rubricno'.$j] = array (
				'type' => 'hidden',
				'value' => $eachval->rubric_no,		
			);

			/*$comevaluation['elements']['rubric'.$j] = array(
				'type' => 'html',
				'value' => '<table class="fullwidth table"><thead><tr><th>'.$eachval->rubric_name.'</th></tr></thead></table><br/>'.'<b>Level1</b>: '.$eachval->level1.'<br/><b>Level2</b>: '.$eachval->level2.'<br/><b>Level3</b>: '.$eachval->level3.'<br/><b>Level4</b>: '.$eachval->level4.'<br/>',
			);*/	
			$comevaluation['elements']['line'.$j] = array(
				'type' => 'html',
				'value' => '<table class="fullwidth table"><thead><tr><th></th></tr></thead></table>',
			);
			/*$comevaluation['elements']['rubric'.$j] = array(
				'type' => 'html',
				'value' => '<b>' . $eachval->rubric_name . '</b>'.'<br/><br/><b>Level1</b>: '.$eachval->level1.'<br/><b>Level2</b>: '.$eachval->level2.'<br/><b>Level3</b>: '.$eachval->level3.'<br/><b>Level4</b>: '.$eachval->level4.'<br/><br/>',
			);	*/

			$comevaluation['elements']['rubric'.$j] = array(
				'type' => 'html',
				'value' => $rubval,
			);		
			$comevaluation['elements']['assignlevel'.$j] = array(
				'type' => 'select',
				'title' => 'Assign a Level',
				'defaultvalue' => $evallvl[$eachval->rubric_no],
				'collapseifoneoption' => false,
				'options' => $options,
			);
			$comevaluation['elements']['comments'.$j] = array(
				'type' => 'wysiwyg',
				'title' => 'Comments',
				'defaultvalue' => $evalcomm[$eachval->rubric_no],
				'rows'         => 10,
				'cols'         => 70,
			);
		}
		$comevaluation['elements']['countno'] = array (
				'type' => 'hidden',
				'value' => $j,	
		);
		if($evallvl[0]){
			$finallvl = $evallvl[0];
			$finalcomm = $evalcomm[0];
		}else {
			$finallvl = "Level";
			$finalcomm = "";
		}
		$comevaluation['elements']['finalassess'] = array(
			'type' => 'html',
			'value' => '<br/><h3 align="center">Outcome Assessment </h3>',
		);
		$comevaluation['elements']['final'] = array(
				'type' => 'html',
				'value' => '<table class="fullwidth table"><thead><tr><th></th></tr></thead></table><br/>',
		);
		
		$comevaluation['elements']['assignlevel'] = array(
					'type' => 'select',
					'title' => 'Assign a Level',
					'defaultvalue' => $evallvl[0],
					'collapseifoneoption' => false,
					'options' => $foptions,
		);
		$comevaluation['elements']['comments'] = array(
					'type' => 'wysiwyg',
					'title' => 'Comments',
					'defaultvalue' => $evalcomm[0],
					'rows'  => 10,
					'cols'  => 70,
		);
		$comevaluation['elements']['saveeval'] = array(
					'type'  => 'submit',
					'value' => get_string('save'),
		);
		$comevaluation['elements']['subeval'] = array(
					'type'  => 'submit',
					'value' => get_string('submit'),
		);
	      }else{

				$options = array();
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
		$j=0;

		foreach($rubrics as $eachval){
			
			$j = $j + 1;
			$comevaluation['elements']['rubricno'.$j] = array (
				'type' => 'hidden',
				'value' => $eachval->rubric_no,		
			);

			$comevaluation['elements']['rubric'.$j] = array(
				'type' => 'html',
				'value' => '<br/><h6>' . $j . ". " . $eachval->rubric_name .'</h6>',
			);			
			$comevaluation['elements']['assignlevel'.$j] = array(
				'type' => 'select',
				'title' => 'Assessment',
				'defaultvalue' => $evallvl[$eachval->rubric_no],
				'collapseifoneoption' => false,
				'options' => $options,
			);
			$comevaluation['elements']['comments'.$j] = array(
				'type' => 'wysiwyg',
				'title' => 'Comments',
				'defaultvalue' => $evalcomm[$eachval->rubric_no],
				'rows'         => 10,
				'cols'         => 70,
			);
		}
		$comevaluation['elements']['countno'] = array (
				'type' => 'hidden',
				'value' => $j,	
		);

		$comevaluation['elements']['finalassess'] = array(
				'type' => 'html',
				'value' => '<br/><h3 align="center">Outcome Assessment </h3>',
		);
		$comevaluation['elements']['final'] = array(
				'type' => 'html',
				'value' => '<table class="fullwidth table"><thead><tr><th></th></tr></thead></table><br/>',
		);
		
		$comevaluation['elements']['assignlevel'] = array(
					'type' => 'select',
					'title' => 'Assessment',
					'defaultvalue' => $evallvl[0],
					'collapseifoneoption' => false,
					'options' => $options,
		);
		$comevaluation['elements']['comments'] = array(
					'type' => 'wysiwyg',
					'title' => 'Comments',
					'defaultvalue' => $evalcomm[0],
					'rows'  => 10,
					'cols'  => 70,
		);
		$comevaluation['elements']['saveeval'] = array(
					'type'  => 'submit',
					'value' => get_string('save'),
		);
		$comevaluation['elements']['subeval'] = array(
					'type'  => 'submit',
					'value' => get_string('submit'),
		);
	     }
}

}

if($comevaluation){
$comevaluation = pieform($comevaluation);
}
$outheader = pieform($outheader);

define('TITLE', get_string('assessmenttitle', 'view'));
$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));

/*if ($USER->is_logged_in() && !empty($_SERVER['HTTP_REFERER'])) {
    $page = get_config('wwwroot') . 'view/listassessments.php?id=' . $viewid.'&outcome='.$outcome_id;
    if ($_SERVER['HTTP_REFERER'] != $page) {
        $smarty->assign('backurl', $_SERVER['HTTP_REFERER']);
    }
}*/

$owner = get_record('usr','id',$view->owner);
$smarty->assign('outcomename',$outcomedes->outcome_name);
if($outcomedes->eval_required){
$smarty->assign('evalrequired',$outcomedes->eval_required);
}else{
$smarty->assign('evalrequired',0);
}
$smarty->assign('eval_type',$outcomedes->eval_type);
$smarty->assign('suboutcomes',$suboutcomes);
$smarty->assign('mainoutcomename',$mainoutcomename);
$smarty->assign('viewid',$viewid);
if($outcome_id == $view->submittedoutcome){
	$smarty->assign('subgroup',0);
}else{
	$smarty->assign('subgroup',$group->id);
}
$smarty->assign('outcome_id',$outcome_id);
$smarty->assign('rubricsfound',$rubricsfound);
$smarty->assign('main_outcome',$outcomedes->main_outcome);
$smarty->assign('first',$first);
$smarty->assign('hdravail',$hdravail);
$smarty->assign('comevaluation',$comevaluation);
$smarty->assign('outheader',$outheader);
$smarty->assign('viewtitle',$view->title);
$smarty->assign('ownername',$owner->username);
$smarty->assign('ownerlink', 'user/view.php?id=' . $view->owner);
$smarty->display('view/assessment.tpl');

function comevaluation_validate(Pieform $comevaluation, $values) {
	if(isset($values['subeval'])) {
		for($i=1;$i<=$values['countno'];$i++) {
			if($values['assignlevel'.$i] == 'Level'){
				$comevaluation->set_error('assignlevel'.$i, get_string('selflevelneeded', 'view'));
			}
			if($values['comments'.$i] == ""){
				$comevaluation->set_error('comments'.$i, get_string('descriptionneeded', 'view'));
			}	
		}

		if($values['assignlevel'] == 'Level'){
			$comevaluation->set_error('assignlevel', get_string('selflevelneeded', 'view'));
		}
		if($values['comments'] == ""){
			$comevaluation->set_error('comments', get_string('descriptionneeded', 'view'));
		}
	}
}

function comevaluation_submit(Pieform $comevaluation, $values) {
	global $USER, $viewid; 
	db_begin();
	if(isset($_POST['subeval'])) {
		$submitted = 1;
	} else {
		$submitted = 0;
	}

$assess = get_record_sql(
    'SELECT id
       FROM {outcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND outcome = ?
	 AND rubric_no = 0',
    array($USER->get('id'), $viewid, $_POST['outcomeid'])
);
	if($assess) {
		update_record('outcome_results', array('level_assigned' => $_POST['assignlevel'],'comments' => $_POST['comments'],'submitted' => $submitted),array('id' => $assess->id));
	}else{
		$outresultid = insert_record('outcome_results', (object)array('rubric_no' => 0,'member' => $USER->get('id'),'level_assigned' => $_POST['assignlevel'],'comments' => $_POST['comments'],'view_id' => $viewid, 'submitted' => $submitted, 'outcome' => $_POST['outcomeid']),'id',true);
	}

	if($submitted && $_POST['main_outcome'] != 0){

$allsuboutcomes = @get_records_sql_array(
	'SELECT id, special_access
	    FROM {outcomes}
	    WHERE main_outcome = ?
	    AND deleted = 0',
	array($outcome_id)
);

$suboutcomes = array();
$i = -1;
$totalCnt = 0;
$subCnt = 0;
foreach($allsuboutcomes as $allsuboutcome){
	if($allsuboutcome->special_access){
		if($allsuboutcome->special_access == $ownerrec->primary_focus){
			$totalCnt = $totalCnt + 1;
			$suboutcomedet = @get_records_sql_array(
    			'SELECT *
       		FROM {outcome_results} 
      		WHERE outcome = ?
		      AND view_id = ?
       		AND rubric_no = 0
       		AND submitted = 1',
    			array($allsuboutcome->id, $viewid)
			);
			if($suboutcomedet){
				$subCnt = $subCnt + 1;
			}
		  }
	}else{
			$totalCnt = $totalCnt + 1;
			$suboutcomedet = @get_records_sql_array(
    			'SELECT *
       		FROM {outcome_results} 
      		WHERE outcome = ?
		      AND view_id = ?
       		AND rubric_no = 0
       		AND (submitted = 1 OR submitted = 2)',
    			array($suboutcome->id, $viewid)
			);
			if($suboutcomedet){
				$subCnt = $subCnt + 1;
			}
	}
}

if($totalCnt == $subCnt){
$assess = get_record_sql(
    'SELECT id
       FROM {outcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND outcome = ?
	 AND rubric_no = 0',
    array($USER->get('id'), $viewid, $_POST['main_outcome'])
);
	if($assess) {
		update_record('outcome_results', array('level_assigned' => 'final','comments' => 'final','submitted' => 1),array('id' => $assess->id));
	}else{
		$outresultid = insert_record('outcome_results', (object)array('rubric_no' => 0,'member' => $USER->get('id'),'level_assigned' => 'final','comments' => 'final','view_id' => $viewid, 'submitted' => 1, 'outcome' => $_POST['main_outcome']),'id',true);
	}
}
	}

	for($i=1;$i<=$_POST['countno'];$i++) {
$assess = get_record_sql(
    'SELECT id
       FROM {outcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND outcome = ?
	 AND rubric_no = ?',
    array($USER->get('id'), $viewid, $_POST['outcomeid'], $_POST['rubricno'.$i])
);
		if($assess) {
			update_record('outcome_results', array('level_assigned' => $_POST['assignlevel'.$i],'comments' => $_POST['comments'.$i]),array('id' => $assess->id));
		}else{
			$outresultid = insert_record('outcome_results', (object)array('rubric_no' => $_POST['rubricno'.$i],'member' => $USER->get('id'), 'level_assigned' => $_POST['assignlevel'.$i],'comments' => $_POST['comments'.$i],'view_id' => $viewid, 'outcome' => $_POST['outcomeid']),'id',true);
		}
	}
	db_commit();
	redirect(get_config('wwwroot') . 'view/listassessments.php?id=' .$viewid .'&outcome=' .$_POST['outcomeid'] .'&first=' .$_POST['first']);
}

?>
