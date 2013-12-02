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
require('courseoutcome.php');

$viewid = param_integer('id');
$courseoutcome_id = param_integer('courseoutcome');
$first = param_integer('first',0);
$sub = param_integer('sub',0);

$view = get_record('view', 'id', $viewid);

$courseoutcomedes = is_courseoutcome_available($courseoutcome_id);

if(!$courseoutcomedes || !$courseoutcomedes->main_courseoutcome){
    throw new AccessDeniedException('courseoutcome does not exist');
}

$accessview = 0;
if($courseoutcomedes->id == $view->submittedcourseoutcome){
	$accessview = 1;
}

$maincourseoutcomedes = is_courseoutcome_available($courseoutcomedes->main_courseoutcome);

if(!$maincourseoutcomedes){
    throw new AccessDeniedException('courseoutcome does not exist');
}

$maincourseoutcome = $maincourseoutcomedes;

while($maincourseoutcome && (!$accessview || !$group)){

	if(!$group && $maincourseoutcome->eval_required){
	$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.courseoutcome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.courseoutcome = ?
       AND gt.submittableto = 1',
    	array($USER->get('id'), $maincourseoutcome->id)
	);
	}
	if($view->submittedcourseoutcome == $maincourseoutcome->id){
		$accessview = 1;
	}
	$tempout = $maincourseoutcome->main_courseoutcome;
	$maincourseoutcome = "";
	if($tempout){
		$maincourseoutcome = is_courseoutcome_available($tempout);
		if(!$maincourseoutcome){
    			throw new AccessDeniedException('courseoutcome does not exist');
		}
	}
}

if (!$view || !$group || !$maincourseoutcomedes || !accessview) {
    throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
}

$ownerrec = get_record('usr',id,$view->owner);
if($courseoutcomedes->special_access){
	if($courseoutcomedes->special_access != $ownerrec->primary_focus){
    		throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
	}
}

$maincourseoutcomename = $maincourseoutcomedes->courseoutcome_name;

$allsubcourseoutcomes = @get_records_sql_array(
	'SELECT id, courseoutcome_name, eval_required, special_access
	    FROM {courseoutcomes}
	    WHERE main_courseoutcome = ?
	    AND deleted = 0',
	array($courseoutcome_id)
);

$subcourseoutcomes = array();
$i = -1;
foreach($allsubcourseoutcomes as $allsubcourseoutcome){
	if($allsubcourseoutcome->special_access){
		if($allsubcourseoutcome->special_access == $ownerrec->primary_focus){
			$i = $i + 1;
			$subcourseoutcomes[$i] = array();
			$subcourseoutcomes[$i]['id'] = $allsubcourseoutcome->id;
			$subcourseoutcomes[$i]['courseoutcome_name'] = $allsubcourseoutcome->courseoutcome_name;
			$subcourseoutcomes[$i]['eval_required'] = $allsubcourseoutcome->eval_required;
		}
	}else{
			$i = $i + 1;
			$subcourseoutcomes[$i] = array();
			$subcourseoutcomes[$i]['id'] = $allsubcourseoutcome->id;
			$subcourseoutcomes[$i]['courseoutcome_name'] = $allsubcourseoutcome->courseoutcome_name;
			$subcourseoutcomes[$i]['eval_required'] = $allsubcourseoutcome->eval_required;
	}
}

$allsubmitted = 0;

if(!$subcourseoutcomes){
$subcourseoutcomes = array();
$allsubmitted = 1;
}else if($courseoutcomedes->eval_required){
	$allsubmitted = 1;
}

$rubrics = @get_records_sql_array(
	'SELECT rubric_no,rubric_name,level1,level2,level3,level4
		FROM {rubrics} 
        WHERE courseoutcome_id = ?
	  AND deleted = 0',
	array($courseoutcome_id)
);

		$courseoutcomedetails = array(
			'name'     => 'courseoutcomedetails',
			'method'   => 'post',
			'autofocus' => false,
			'plugintype' => 'core',
			'pluginname' => 'view',
			'elements' => array(),
		);
		
$outlevelsdes = '';
$hdravail = 0;
if($courseoutcomedes->description){
	$outlevelsdes = $outlevelsdes . 'Description:  '. $courseoutcomedes->description;
}
$rubriclevels = array();
if($courseoutcomedes->eval_type == 'Level'){
$courseoutcomelevels = @get_records_sql_array(
	'SELECT *
	  FROM {courseoutcome_levels} 
        WHERE courseoutcome_id = ?
	  AND rubric_no = 0
	  ORDER BY level_val',
	array($courseoutcome_id)
);

$foptions = array();
$foptions['Level'] = "Select a level";
$var = "Level ";

foreach($courseoutcomelevels as $courseoutcomelevel){
$foptions[$var.$courseoutcomelevel->level_val] = $var.$courseoutcomelevel->level_val;
	$outlevelsdes = $outlevelsdes .'<br/><b>Level' . $courseoutcomelevel->level_val . '</b>: '.$courseoutcomelevel->level_desc;
}

}

if($outlevelsdes){
$hdravail = 1;
$outlevelsdes .'<br/>';
}

$courseoutcomedetails['elements']['courseoutcomelevels'] = array(
	'type' => 'html',
	'value' => $outlevelsdes,
);

$courseoutcomedetails = pieform($courseoutcomedetails);
$comevaluation = array();
$finalresults = array();

if($allsubmitted){

$submembers = @get_records_sql_array(
    'SELECT DISTINCT(o.member), u.username
       FROM {courseoutcome_results} o
	 INNER JOIN {usr} u ON (u.id = o.member)
	WHERE o.courseoutcome = ?
	AND o.view_id = ?',
	array($courseoutcome_id, $viewid)
);

$j = -1;
foreach($rubrics as $eachval){
		
	$j = $j + 1;
	$comevaluation[$j] = array();
	$comevaluation[$j]['name'] = $eachval->rubric_name;
	if($courseoutcomedes->eval_type == "Level"){

		$rublevels = @get_records_sql_array(
	'SELECT *
	  FROM {courseoutcome_levels} 
        WHERE courseoutcome_id = ?
	  AND rubric_no = ?
	  ORDER BY level_val',
	array($courseoutcome_id,$eachval->rubric_no)
);

	foreach($rublevels as $rublevel){
		$comevaluation[$j]['level'.$rublevel->level_val] = $rublevel->level_desc;
	}
	}
	foreach($submembers as $submember){
$issubmitted = get_record_sql(
    'SELECT level_assigned,comments,submitted
       FROM {courseoutcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND courseoutcome = ?
	 AND rubric_no = 0',
    array($submember->member, $viewid, $courseoutcome_id)
);
		if($issubmitted && $issubmitted->submitted) {
			$assessment = get_record_sql(
    'SELECT level_assigned,comments,submitted
       FROM {courseoutcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND courseoutcome = ?
	 AND rubric_no = ?',
    array($submember->member, $viewid, $courseoutcome_id,$eachval->rubric_no)
);
			if($assessment){
			$assessmentdone = 1;
			$comevaluation[$j][$submember->member] = array(
				'level' => $assessment->level_assigned,
				'comments' => $assessment->comments
			);
			}
		}
	}
}
	
	foreach($submembers as $submember){
$issubmitted = get_record_sql(
    'SELECT level_assigned,comments,submitted
       FROM {courseoutcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND courseoutcome = ?
	 AND rubric_no = 0',
    array($submember->member, $viewid, $courseoutcome_id)
);
		if($issubmitted && $issubmitted->submitted) {
			$finalresults[$submember->member] = array(
				'level' => $issubmitted->level_assigned,
				'comments' => $issubmitted->comments,
			);
		}
	}
}
define('TITLE', get_string('assessmenttitle', 'view'));
$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));

/*if ($USER->is_logged_in() && !empty($_SERVER['HTTP_REFERER'])) {
    $page = get_config('wwwroot') . 'view/listassessments.php?id=' . $viewid.'&courseoutcome='.$courseoutcome_id;
    if ($_SERVER['HTTP_REFERER'] != $page) {
        $smarty->assign('backurl', $_SERVER['HTTP_REFERER']);
    }
}*/

$owner = get_record('usr','id',$view->owner);
$smarty->assign('courseoutcomename',$courseoutcomedes->courseoutcome_name);
$smarty->assign('evaltype',$courseoutcomedes->eval_type);
$smarty->assign('maincourseoutcomename',$maincourseoutcomename);
$smarty->assign('main_courseoutcome',$maincourseoutcomedes->id);
$smarty->assign('viewid',$viewid);
$smarty->assign('hdravail',$hdravail);
$smarty->assign('first',$first);
$smarty->assign('sub',$sub);
$smarty->assign('comevaluation',$comevaluation);
$smarty->assign('courseoutcomedetails',$courseoutcomedetails);
$smarty->assign('assessmentdone',$assessmentdone);
$smarty->assign('finalresults',$finalresults);
$smarty->assign('submembers',$submembers);
$smarty->assign('subcourseoutcomes',$subcourseoutcomes);
$smarty->display('view/subassessment.tpl');

?>
