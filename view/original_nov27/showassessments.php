<meta http-equiv="refresh"> 
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

if (!$view) {
   throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
}

$outrec = is_outcome_available($outcome_id);
if(!$outrec){
    throw new AccessDeniedException('Outcome does not exist');
}

$accessview = 0;
if($outrec->id == $view->submittedoutcome){
	$accessview = 1;
}

$mainoutcome = $outrec->main_outcome;
if($mainoutcome){
	$mainoutcomedes = is_outcome_available($mainoutcome);
	if(!$mainoutcomedes){
    		throw new AccessDeniedException('Outcome does not exist');
	}
}

while((!$group || !$accessview) && $mainoutcome){

if(!$group){
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.outcome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.outcome = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $mainoutcome)
);
}
if($mainoutcome == $view->submittedoutcome){
	$accessview = 1;
}

if($mainoutcomedes->main_outcome){
	$mainoutcome = $mainoutcomedes->main_outcome;
	$mainoutcomedes = is_outcome_available($mainoutcome);
	if(!$mainoutcomedes){
    		throw new AccessDeniedException('Outcome does not exist');
	}
}else{
	$mainoutcome = "";
}
}

if (!$group || $group->role != "chair" || !$accessview) {
   throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
}

$ownerrec = get_record('usr',id,$view->owner);
if($outrec->special_access){
	if($outrec->special_access != $ownerrec->primary_focus){
    		throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
	}
}

if(!$outrec->eval_required){
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
		}
	}else{
			$i = $i + 1;
			$suboutcomes[$i] = array();
			$suboutcomes[$i]['id'] = $allsuboutcome->id;
			$suboutcomes[$i]['outcome_name'] = $allsuboutcome->outcome_name;
			$suboutcomes[$i]['eval_required'] = $allsuboutcome->eval_required;
	}
}
}

$allsubmitted = 0;
if(!$suboutcomes){
	$suboutcomes = array();
	$allsubmitted = 1;
}else if($outcomedes->eval_required){
	$allsubmitted = 1;
}

$comm = array();
if($allsubmitted){
$committee = @get_records_sql_array(
	'SELECT DISTINCT(o.member) as member, u.username
		FROM {outcome_results} o
		INNER JOIN {usr} u ON (u.id = o.member)
		WHERE o.view_id = ?
		AND o.outcome = ?
		AND o.rubric_no = 0
		AND (o.submitted = 1 OR o.submitted = 2)',
	array($viewid, $outcome_id)
);

if($committee) {

		$j = -1;		
	foreach ($committee as $eachmember) {	
		$comm[$eachmember->member] = @get_records_sql_array(
			'SELECT o.rubric_no,r.rubric_name,o.level_assigned,o.comments
				FROM {outcome_results} o
				INNER JOIN {rubrics} r ON (r.rubric_no = o.rubric_no AND deleted = 0)
				WHERE r.outcome_id = ?
				AND o.view_id = ?
				AND o.outcome = ?
				AND o.member = ?
				AND o.rubric_no != 0',
			array($outcome_id,$viewid,$outcome_id,$eachmember->member)
		);
		$finalcomm[$eachmember->member] = get_record_sql(
    'SELECT *
       FROM {outcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND outcome = ?
	 AND rubric_no = 0',
    array($eachmember->member, $viewid, $outcome_id)
);
		$j = $j + 1;
		$eachmember->cnt = $j;
		$rework = array(
			'name'     => 'rework'.$j,
			'method'   => 'post',
			'autofocus' => false,
			'successcallback' => 'rework_submit',
			'plugintype' => 'core',
			'pluginname' => 'view',
			'elements' => array(),
		);
		$rework['elements']['sub'] = array(
			'type' => 'submit',
			'value' => 'Request Reassessment',
		);
		$rework['elements']['outid'] = array(
			'type' => 'hidden',
			'value' => $finalcomm[$eachmember->member]->id,
		);
		$rework['elements']['viewid'] = array(
			'type' => 'hidden',
			'value' => $viewid,
		);
		$rework['elements']['first'] = array(
			'type' => 'hidden',
			'value' => $first,
		);
		$eachmember->rework = pieform($rework);
	}
}
}

define('TITLE', get_string('assessmenttitle', 'view'));

$smarty = smarty();

/*if ($USER->is_logged_in() && !empty($_SERVER['HTTP_REFERER'])) {
    $page = get_config('wwwroot') . 'view/showassessments.php?id=' . $viewid . '&outcome=' . $outcome_id;
    if ($_SERVER['HTTP_REFERER'] != $page) {
        $smarty->assign('backurl', $_SERVER['HTTP_REFERER']);
    }
}*/
$owner = get_record('usr','id',$view->owner);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('viewid',$viewid);
$smarty->assign('first',$first);
$smarty->assign('outcome_id',$outcome_id);
$smarty->assign('main_outcome',$outrec->main_outcome);
if($outcome_id == $view->submittedoutcome){
	$smarty->assign('subgroup',0);
}else{
	$smarty->assign('subgroup',$group->id);
}
$smarty->assign('committee',$committee);
$smarty->assign('comm',$comm);
$smarty->assign('finalcomm',$finalcomm);
$smarty->assign('suboutcomes',$suboutcomes);
$smarty->assign('zeroval',0);
$smarty->assign('viewtitle',$view->title);
$smarty->assign('outname',$outrec->outcome_name." Outcome");
$smarty->assign('evaltype',$outrec->eval_type);
$smarty->assign('ownername',$owner->username);
$smarty->assign('ownerlink', 'user/view.php?id=' . $view->owner);
$smarty->display('view/showassessments.tpl');

function rework_submit(Pieform $rework, $values) {
	global $USER, $viewid; 
	echo "Hello";
	db_begin();
	if(isset($_POST['sub'])){
		update_record('outcome_results', array('submitted' => null),array('id' => $_POST['outid']));
		$outcomedet = get_record('outcome_results','id',$_POST['outid']);
		$main_outcomedet = get_record('outcomes','id', $outcomedet->outcome);
		$mem = $outcomedet->member;
		$main_outcome = "";	
		
		if($main_outcomedet->main_outcome){
			$main_outcome = get_record('outcomes','id', $main_outcomedet->main_outcome);
		}

		//while($main_outcome && !$main_outcome->eval_required){			
			$assess = get_record_sql(
			    'SELECT id 
			    FROM {outcome_results}
			    WHERE member = ?
			    AND view_id = ?
			    AND outcome = ?
			    AND rubric_no = 0
			    AND (submitted = 1 OR submitted = 2)',
			    array($mem, $viewid, $main_outcome->id)
			);
			if($assess){
				update_record('outcome_results', array('submitted' => null), array('id' => $assess->id));				
				update_record('outcome_results', array('reassess' => 1	), array('id' => $assess->id));
			}
			if($main_outcome->main_outcome){
				$main_outcome = get_record('outcomes','id', $main_outcome->main_outcome);
			}
		//}
	}
	echo "Hello3";
	db_commit();
	redirect(get_config('wwwroot') . 'view/showassessments.php?id='.$viewid . '&outcome=' .$outcomedet->outcome .'&first=' .$_POST['first']);
}
?>
