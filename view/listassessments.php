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
$view = get_record('view', 'id', $viewid);
printf('here');

$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.courseoutcome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.courseoutcome = ?
       AND gt.submittableto = 1',
    array($USER->get('id'),$courseoutcome_id)
);
printf($group);
printf('here2');
/*
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.courseoutcome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?

       //AND g.courseoutcome = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $courseoutcome_id)
);*/
$courseoutcomedes = is_courseoutcome_available($courseoutcome_id);
printf($courseoutcomedes->id);
printf('here');
if(!$courseoutcomedes){
    throw new AccessDeniedException('courseoutcome does not exist');
}

$accessview = 0;
if($courseoutcomedes->id == $view->submittedcourseoutcome){
	$accessview = 1;
}
$main_courseoutcome = 0;
if($courseoutcomedes->main_courseoutcome){
	$maincourseoutcomedes = is_courseoutcome_available($courseoutcomedes->main_courseoutcome
);
if(!$maincourseoutcomedes){
    throw new AccessDeniedException('courseoutcome does not exist');
}
	$maincourseoutcomename = $maincourseoutcomedes->courseoutcome_name;

if(!$maincourseoutcomedes->eval_required){
	$main_courseoutcome = $maincourseoutcomedes->id;
}
$maincourseoutcome = $maincourseoutcomedes;

if(!$accessview && $view->submittedcourseoutcome == $courseoutcomedes->main_courseoutcome){
	$accessview = 1;
}
while((!$group || !$accessview) && $maincourseoutcome){

if(!$group && !$maincourseoutcome->eval_required){
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.courseoutcome, u.role
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
      
       AND gt.submittableto = 1',
    array($USER->get('id'))
);
/*
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
*/
}
	$tempout = $maincourseoutcome->main_courseoutcome;
	$maincourseoutcome = "";
	if($tempout){
		if($tempout == $view->submittedcourseoutcome){
			$accessview = 1;
		}
		$maincourseoutcome = is_courseoutcome_available($tempout);
		if(!$maincourseoutcome){
    			throw new AccessDeniedException('courseoutcome does not exist');
		}
	}

}
}else{
	$maincourseoutcomename = "";
}

//if (!$view || !$group || !$accessview) {
if (!$view || !$accessview) {
    throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
}

$ownerrec = get_record('usr','id',$view->owner);
if($courseoutcomedes->special_access){
	if($courseoutcomedes->special_access != $ownerrec->primary_focus){
    		throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
	}
}

$memval = 'member';
$adminval = 'admin';

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

					$grouprec = get_record('group',courseoutcome, $allsubcourseoutcome->id);
			$groupmems = @get_records_sql_array(
	'SELECT member
	    FROM {group_member} gm
	    WHERE gm.group = ?
	    AND gm.role != ?
	    AND gm.role != ?',
	array($grouprec->id, $memval, $adminval)
);
		$subcourseoutcomes[$i]['groupmemscnt'] = count($groupmems);

		}
	}else{
			$i = $i + 1;
			$subcourseoutcomes[$i] = array();
			$subcourseoutcomes[$i]['id'] = $allsubcourseoutcome->id;
			$subcourseoutcomes[$i]['courseoutcome_name'] = $allsubcourseoutcome->courseoutcome_name;
			$subcourseoutcomes[$i]['eval_required'] = $allsubcourseoutcome->eval_required;
			
			$grouprec = get_record('group',courseoutcome, $allsubcourseoutcome->id);
			$groupmems = @get_records_sql_array(
	'SELECT member
	    FROM {group_member} gm
	    WHERE gm.group = ?
	    AND gm.role != ?
	    AND gm.role != ?',
	array($grouprec->id, $memval, $adminval)
);
		$subcourseoutcomes[$i]['groupmemscnt'] = count($groupmems);
	}
}

$allsubmitted = 0;

if(!$subcourseoutcomes){
$subcourseoutcomes = array();
$allsubmitted = 1;
}

if($subcourseoutcomes && $courseoutcomedes->eval_required){
$allsubmitted = 1;
	for($i = 0; $i < count($subcourseoutcomes); $i++){
		$subcourseoutcomedet = @get_records_sql_array(
    'SELECT *
       FROM {courseoutcome_results} 
       WHERE courseoutcome = ?
       AND view_id = ?
       AND rubric_no = 0
       AND (submitted = 1 OR submitted = 2)',
    array($subcourseoutcomes[$i]['id'], $viewid)
	);	
		if(!$subcourseoutcomedet){
			$allsubmitted = 0;
		}else if(count($subcourseoutcomedet) < $subcourseoutcomes[$i]['groupmemscnt']){
			$allsubmitted = 0;
		}
	}
}

$rubrics = @get_records_sql_array(
	'SELECT rubric_no,rubric_name
		FROM {rubrics} 
        WHERE courseoutcome_id = ?
	  AND deleted = 0',
	array($courseoutcome_id)
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

$outheader['elements']['courseoutcomelevels'] = array(
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
       FROM {courseoutcome_results} v 
	   WHERE v.member = ? 
	   AND v.view_id = ?
	   AND v.courseoutcome = ?',
    array($USER->get('id'),$viewid, $courseoutcome_id)
);

$issubmitted = get_record_sql(
    'SELECT level_assigned,comments,submitted
       FROM {courseoutcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND courseoutcome = ?
	 AND rubric_no = 0',
    array($USER->get('id'), $viewid, $courseoutcome_id)
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
		
		if($courseoutcomedes->eval_type == "Level"){

		$outheader['elements']['finalassess'] = array(
				'type' => 'html',
				'value' => '<br/><h3 align="center">Course Outcome Assessment </h3>',
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
	  FROM {courseoutcome_levels} 
        WHERE courseoutcome_id = ?
	  AND rubric_no = ?
	  ORDER BY level_val',
	array($courseoutcome_id,$eachval->rubric_no)
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
				'value' => '<br/><h3 align="center">courseoutcome Assessment </h3>',
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
				$subst = $courseoutcomedes->eval_type;
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
		
		$comevaluation['elements']['courseoutcomeid'] = array (
			'type' => 'hidden',
			'value' => $courseoutcome_id,
		);

		$comevaluation['elements']['main_courseoutcome'] = array (
			'type' => 'hidden',
			'value' => $main_courseoutcome,
		);

		$comevaluation['elements']['first'] = array (
			'type' => 'hidden',
			'value' => $first,
		);

		$comevaluation['elements']['allsubmitted'] = array (
			'type' => 'hidden',
			'value' => $allsubmitted,
		);

		if($courseoutcomedes->eval_type == "Level"){
		
		$j=0;

		foreach($rubrics as $eachval){
			
			$rublevels = @get_records_sql_array(
	'SELECT *
	  FROM {courseoutcome_levels} 
        WHERE courseoutcome_id = ?
	  AND rubric_no = ?
	  ORDER BY level_val',
	array($courseoutcome_id,$eachval->rubric_no)
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
			'value' => '<br/><h3 align="center">courseoutcome Assessment </h3>',
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
				$subst = $courseoutcomedes->eval_type;
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
				'value' => '<br/><h3 align="center">courseoutcome Assessment </h3>',
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
    $page = get_config('wwwroot') . 'view/listassessments.php?id=' . $viewid.'&courseoutcome='.$courseoutcome_id;
    if ($_SERVER['HTTP_REFERER'] != $page) {
        $smarty->assign('backurl', $_SERVER['HTTP_REFERER']);
    }
}*/

$owner = get_record('usr','id',$view->owner);
$smarty->assign('courseoutcomename',$courseoutcomedes->courseoutcome_name);
if($courseoutcomedes->eval_required){
$smarty->assign('evalrequired',$courseoutcomedes->eval_required);
}else{
$smarty->assign('evalrequired',0);
}
$smarty->assign('eval_type',$courseoutcomedes->eval_type);
$smarty->assign('subcourseoutcomes',$subcourseoutcomes);
$smarty->assign('maincourseoutcomename',$maincourseoutcomename);
$smarty->assign('viewid',$viewid);
if($courseoutcome_id == $view->submittedcourseoutcome){
	$smarty->assign('subgroup',0);
}else{
	$smarty->assign('subgroup',$group->id);
}
$smarty->assign('courseoutcome_id',$courseoutcome_id);
$smarty->assign('rubricsfound',$rubricsfound);
$smarty->assign('main_courseoutcome',$courseoutcomedes->main_courseoutcome);
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
       FROM {courseoutcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND courseoutcome = ?
	 AND rubric_no = 0',
    array($USER->get('id'), $viewid, $_POST['courseoutcomeid'])
);
	if($assess) {
		update_record('courseoutcome_results', array('level_assigned' => $_POST['assignlevel'],'comments' => $_POST['comments'],'submitted' => $submitted),array('id' => $assess->id));
	}else{
		$outresultid = insert_record('courseoutcome_results', (object)array('rubric_no' => 0,'member' => $USER->get('id'),'level_assigned' => $_POST['assignlevel'],'comments' => $_POST['comments'],'view_id' => $viewid, 'submitted' => $submitted, 'courseoutcome' => $_POST['courseoutcomeid']),'id',true);
	}

	if($submitted && $_POST['main_courseoutcome'] != 0){

$allsubcourseoutcomes = @get_records_sql_array(
	'SELECT id, special_access
	    FROM {courseoutcomes}
	    WHERE main_courseoutcome = ?
	    AND deleted = 0',
	array($courseoutcome_id)
);

$subcourseoutcomes = array();
$i = -1;
$totalCnt = 0;
$subCnt = 0;
foreach($allsubcourseoutcomes as $allsubcourseoutcome){
	if($allsubcourseoutcome->special_access){
		if($allsubcourseoutcome->special_access == $ownerrec->primary_focus){
			$totalCnt = $totalCnt + 1;
			$subcourseoutcomedet = @get_records_sql_array(
    			'SELECT *
       		FROM {courseoutcome_results} 
      		WHERE courseoutcome = ?
		      AND view_id = ?
       		AND rubric_no = 0
       		AND submitted = 1',
    			array($allsubcourseoutcome->id, $viewid)
			);
			if($subcourseoutcomedet){
				$subCnt = $subCnt + 1;
			}
		  }
	}else{
			$totalCnt = $totalCnt + 1;
			$subcourseoutcomedet = @get_records_sql_array(
    			'SELECT *
       		FROM {courseoutcome_results} 
      		WHERE courseoutcome = ?
		      AND view_id = ?
       		AND rubric_no = 0
       		AND (submitted = 1 OR submitted = 2)',
    			array($subcourseoutcome->id, $viewid)
			);
			if($subcourseoutcomedet){
				$subCnt = $subCnt + 1;
			}
	}
}

if($totalCnt == $subCnt){
$assess = get_record_sql(
    'SELECT id
       FROM {courseoutcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND courseoutcome = ?
	 AND rubric_no = 0',
    array($USER->get('id'), $viewid, $_POST['main_courseoutcome'])
);
	if($assess) {
		update_record('courseoutcome_results', array('level_assigned' => 'final','comments' => 'final','submitted' => 1),array('id' => $assess->id));
	}else{
		$outresultid = insert_record('courseoutcome_results', (object)array('rubric_no' => 0,'member' => $USER->get('id'),'level_assigned' => 'final','comments' => 'final','view_id' => $viewid, 'submitted' => 1, 'courseoutcome' => $_POST['main_courseoutcome']),'id',true);
	}
}
	}

	for($i=1;$i<=$_POST['countno'];$i++) {
$assess = get_record_sql(
    'SELECT id
       FROM {courseoutcome_results}
       WHERE member = ?
	 AND view_id = ?
       AND courseoutcome = ?
	 AND rubric_no = ?',
    array($USER->get('id'), $viewid, $_POST['courseoutcomeid'], $_POST['rubricno'.$i])
);
		if($assess) {
			update_record('courseoutcome_results', array('level_assigned' => $_POST['assignlevel'.$i],'comments' => $_POST['comments'.$i]),array('id' => $assess->id));
		}else{
			$outresultid = insert_record('courseoutcome_results', (object)array('rubric_no' => $_POST['rubricno'.$i],'member' => $USER->get('id'), 'level_assigned' => $_POST['assignlevel'.$i],'comments' => $_POST['comments'.$i],'view_id' => $viewid, 'courseoutcome' => $_POST['courseoutcomeid']),'id',true);
		}
	}
	db_commit();
	redirect(get_config('wwwroot') . 'view/listassessments.php?id=' .$viewid .'&courseoutcome=' .$_POST['courseoutcomeid'] .'&first=' .$_POST['first']);
}

?>
