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
require('courseoutocome.php');
$viewid = param_integer('id');
$courseoutocome_id = param_integer('courseoutocome');
$first = param_integer('first',0);

$view = get_record('view', 'id', $viewid);
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.courseoutocome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.courseoutocome = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $courseoutocome_id)
);

if (!$view) {
   throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
}

$outrec = is_courseoutocome_available($courseoutocome_id);
if(!$outrec){
    throw new AccessDeniedException('courseoutocome does not exist');
}

$accessview = 0;
if($outrec->id == $view->submittedcourseoutocome){
	$accessview = 1;
}

$maincourseoutocome = $outrec->main_courseoutocome;
if($maincourseoutocome){
	$maincourseoutocomedes = is_courseoutocome_available($maincourseoutocome);
	if(!$maincourseoutocomedes){
    		throw new AccessDeniedException('courseoutocome does not exist');
	}
}

while((!$group || !$accessview) && $maincourseoutocome){

if(!$group){
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.courseoutocome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.courseoutocome = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $maincourseoutocome)
);
}
if($maincourseoutocome == $view->submittedcourseoutocome){
	$accessview = 1;
}

if($maincourseoutocomedes->main_courseoutocome){
	$maincourseoutocome = $maincourseoutocomedes->main_courseoutocome;
	$maincourseoutocomedes = is_courseoutocome_available($maincourseoutocome);
	if(!$maincourseoutocomedes){
    		throw new AccessDeniedException('courseoutocome does not exist');
	}
}else{
	$maincourseoutocome = "";
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
$allsubcourseoutocomes = @get_records_sql_array(
	'SELECT id, courseoutocome_name, eval_required, special_access
	    FROM {courseoutocomes}
	    WHERE main_courseoutocome = ?
	    AND deleted = 0',
	array($courseoutocome_id)
);

$subcourseoutocomes = array();
$i = -1;
foreach($allsubcourseoutocomes as $allsubcourseoutocome){
	if($allsubcourseoutocome->special_access){
		if($allsubcourseoutocome->special_access == $ownerrec->primary_focus){
			$i = $i + 1;
			$subcourseoutocomes[$i] = array();
			$subcourseoutocomes[$i]['id'] = $allsubcourseoutocome->id;
			$subcourseoutocomes[$i]['courseoutocome_name'] = $allsubcourseoutocome->courseoutocome_name;
			$subcourseoutocomes[$i]['eval_required'] = $allsubcourseoutocome->eval_required;
		}
	}else{
			$i = $i + 1;
			$subcourseoutocomes[$i] = array();
			$subcourseoutocomes[$i]['id'] = $allsubcourseoutocome->id;
			$subcourseoutocomes[$i]['courseoutocome_name'] = $allsubcourseoutocome->courseoutocome_name;
			$subcourseoutocomes[$i]['eval_required'] = $allsubcourseoutocome->eval_required;
	}
}
}

$allsubmitted = 0;
if(!$subcourseoutocomes){
	$subcourseoutocomes = array();
	$allsubmitted = 1;
}else if($courseoutocomedes->eval_required){
	$allsubmitted = 1;
}

$comm = array();
if($allsubmitted){
$committee = @get_records_sql_array(
	'SELECT DISTINCT(o.member) as member, u.username
		FROM {courseoutocome_results} o
		INNER JOIN {usr} u ON (u.id = o.member)
		WHERE o.view_id = ?
		AND o.courseoutocome = ?
		AND o.rubric_no = 0
		AND (o.submitted = 1 OR o.submitted = 2)',
	array($viewid, $courseoutocome_id)
);

if($committee) {

		$j = -1;		
	foreach ($committee as $eachmember) {	
		$comm[$eachmember->member] = @get_records_sql_array(
			'SELECT o.rubric_no,r.rubric_name,o.level_assigned,o.comments
				FROM {courseoutocome_results} o
				INNER JOIN {rubrics} r ON (r.rubric_no = o.rubric_no AND deleted = 0)
				WHERE r.courseoutocome_id = ?
				AND o.view_id = ?
				AND o.courseoutocome = ?
				AND o.member = ?
				AND o.rubric_no != 0',
			array($courseoutocome_id,$viewid,$courseoutocome_id,$eachmember->member)
		);
		$finalcomm[$eachmember->member] = get_record_sql(
    'SELECT *
       FROM {courseoutocome_results}
       WHERE member = ?
	 AND view_id = ?
       AND courseoutocome = ?
	 AND rubric_no = 0',
    array($eachmember->member, $viewid, $courseoutocome_id)
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
    $page = get_config('wwwroot') . 'view/showassessments.php?id=' . $viewid . '&courseoutocome=' . $courseoutocome_id;
    if ($_SERVER['HTTP_REFERER'] != $page) {
        $smarty->assign('backurl', $_SERVER['HTTP_REFERER']);
    }
}*/
$owner = get_record('usr','id',$view->owner);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('viewid',$viewid);
$smarty->assign('first',$first);
$smarty->assign('courseoutocome_id',$courseoutocome_id);
$smarty->assign('main_courseoutocome',$outrec->main_courseoutocome);
if($courseoutocome_id == $view->submittedcourseoutocome){
	$smarty->assign('subgroup',0);
}else{
	$smarty->assign('subgroup',$group->id);
}
$smarty->assign('committee',$committee);
$smarty->assign('comm',$comm);
$smarty->assign('finalcomm',$finalcomm);
$smarty->assign('subcourseoutocomes',$subcourseoutocomes);
$smarty->assign('zeroval',0);
$smarty->assign('viewtitle',$view->title);
$smarty->assign('outname',$outrec->courseoutocome_name." courseoutocome");
$smarty->assign('evaltype',$outrec->eval_type);
$smarty->assign('ownername',$owner->username);
$smarty->assign('ownerlink', 'user/view.php?id=' . $view->owner);
$smarty->display('view/showassessments.tpl');

function rework_submit(Pieform $rework, $values) {
	global $USER, $viewid; 
	echo "Hello";
	db_begin();
	if(isset($_POST['sub'])){
		update_record('courseoutocome_results', array('submitted' => null),array('id' => $_POST['outid']));
		$courseoutocomedet = get_record('courseoutocome_results','id',$_POST['outid']);
		$main_courseoutocomedet = get_record('courseoutocomes','id', $courseoutocomedet->courseoutocome);
		$mem = $courseoutocomedet->member;
		$main_courseoutocome = "";	
		
		if($main_courseoutocomedet->main_courseoutocome){
			$main_courseoutocome = get_record('courseoutocomes','id', $main_courseoutocomedet->main_courseoutocome);
		}

		//while($main_courseoutocome && !$main_courseoutocome->eval_required){			
			$assess = get_record_sql(
			    'SELECT id 
			    FROM {courseoutocome_results}
			    WHERE member = ?
			    AND view_id = ?
			    AND courseoutocome = ?
			    AND rubric_no = 0
			    AND (submitted = 1 OR submitted = 2)',
			    array($mem, $viewid, $main_courseoutocome->id)
			);
			if($assess){
				update_record('courseoutocome_results', array('submitted' => null), array('id' => $assess->id));				
				update_record('courseoutocome_results', array('reassess' => 1	), array('id' => $assess->id));
			}
			if($main_courseoutocome->main_courseoutocome){
				$main_courseoutocome = get_record('courseoutocomes','id', $main_courseoutocome->main_courseoutocome);
			}
		//}
	}
	echo "Hello3";
	db_commit();
	redirect(get_config('wwwroot') . 'view/showassessments.php?id='.$viewid . '&courseoutocome=' .$courseoutocomedet->courseoutocome .'&first=' .$_POST['first']);
}
?>
