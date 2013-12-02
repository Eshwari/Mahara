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
define('MENUITEM', 'groups/info');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('courseoutcome.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'file');

define('GROUP', param_integer('id'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name);
$group->ctime = strftime(get_string('strftimedate'), $group->ctime);

$group->admins = get_column_sql("SELECT member
    FROM {group_member}
    WHERE \"group\" = ?
    AND role = 'admin'", array($group->id));

$role = group_user_access($group->id);

if (is_logged_in()) {
    $afterjoin = param_variable('next', 'view');
    if ($role) {
        if ($role == 'admin') {
            $group->membershiptype = 'admin';
            $group->requests = count_records('group_member_request', 'group', $group->id);
        }
        else {
            $group->membershiptype = 'member';
        }
        $group->canleave = group_user_can_leave($group->id);
    }
    else if ($group->jointype == 'invite'
             and $invite = get_record('group_member_invite', 'group', $group->id, 'member', $USER->get('id'))) {
        $group->membershiptype = 'invite';
        $group->invite = group_get_accept_form('invite', $group->id, $afterjoin);
    }
    else if ($group->jointype == 'request'
             and $request = get_record('group_member_request', 'group', $group->id, 'member', $USER->get('id'))) {
        $group->membershiptype = 'request';
    }
    else if ($group->jointype == 'open') {
        $group->groupjoin = group_get_join_form('joingroup', $group->id, $afterjoin);
    }
}

$filecounts = ArtefactTypeFileBase::count_user_files(null, $group->id, null);

// Latest forums posts
// NOTE: it would be nicer if there was some generic way to get information 
// from any installed interaction. But the only interaction plugin is forum, 
// and group info pages might be replaced with views anyway...
$foruminfo = null;
if ($role || $group->public) {
    $foruminfo = get_records_sql_array('
        SELECT
            p.id, p.subject, p.body, p.poster, p.topic, t.forum, pt.subject AS topicname
        FROM
            {interaction_forum_post} p
            INNER JOIN {interaction_forum_topic} t ON (t.id = p.topic)
            INNER JOIN {interaction_instance} i ON (i.id = t.forum)
            INNER JOIN {interaction_forum_post} pt ON (pt.topic = p.topic AND pt.parent IS NULL)
        WHERE
            i.group = ?
            AND i.deleted = 0
            AND t.deleted = 0
            AND p.deleted = 0
        ORDER BY
            p.ctime DESC
        LIMIT 5;
        ', array($group->id));
}
$smarty = smarty();
$smarty->assign('group', $group);
$smarty->assign('groupid', $group->id);
$smarty->assign('foruminfo', $foruminfo);
$smarty->assign('membercount', count_records('group_member', 'group', $group->id));
$smarty->assign('viewcount', count_records('view', 'group', $group->id));
$smarty->assign('filecount', $filecounts->files);
$smarty->assign('foldercount', $filecounts->folders);
$subgroup = 0;

if ($role) {
    // For group members, display a list of views that others have
    // shared to the group
    $viewdata = View::get_sharedviews_data(null, 0, $group->id);
    $smarty->assign('sharedviews', $viewdata->data);
    if (group_user_can_assess_submitted_views($group->id, $USER->get('id'))) {
        // Display a list of views submitted to the group

	
	if($group->outcome){
		$outcomedes = get_record('outcomes','id',$group->outcome);
		if($outcomedes->main_outcome){
			$main_group = get_record('group','outcome',$outcomedes->main_outcome);
			if($main_group){
			  // Display a list of views submitted to the group
        		  $smarty->assign('submittedviews', View::get_submitted_views_for_outcome($main_group->id, $group->outcome));				  
			  $subgroup = 1;
			}
		}else{
        		$smarty->assign('submittedviews', View::get_submitted_views($group->id));				
		}
	}else{
        $smarty->assign('submittedviews', View::get_submitted_views($group->id));		
	}
	
	//start courseoffering -outcomes
	if ($group->courseoffering){
	$courseofferingdes= get_record('course_offering', 'id', $group->courseoffering);
		if($courseofferingdes->coursetmp_id){
		$coursetempdes =get_record('course_template', 'id',$courseofferingdes->coursetmp_id);
		//printf($coursetempdes->id);
		//$sampledes =get_courseoutcomes($coursetempdes->id);
		
		  $outcomelists= @get_records_sql_array('SELECT  c.id FROM {courseoutcomes} c INNER JOIN {course_template} ct 
				  ON c.coursetemplate_id = ct.id
				WHERE c.coursetemplate_id =? ', array($coursetempdes->id)
				);
	  $course_lists = array();
	  $i=0;
	  foreach($outcomelists as $crstest){
		
		$course_lists[$i]=$crstest->id;
		$i++;
	} 
	//$no_courseoutcomes =count($course_lists);
	//printf(count($course_lists));
	for ($i= 1; $i <= count($course_lists);$i++){
	
	//printf('---');
	printf($course_lists[$i]);
	//printf('here');
	
	$courseoutcomedes = get_record('courseoutcomes','id',$course_lists[$i]);
		if($courseoutcomedes->main_courseoutcome){
			$main_group = get_record('group','courseoffering',$courseoutcomedes->main_courseoutcome);
			
			if($main_group){
			  // Display a list of views submitted to the group
        		  $smarty->assign('submittedviews2', View::get_submitted_views_for_courseoutcome($main_group->id, $course_lists[i]));				  
			  $subgroup = 1;
			}
		}else{
		 $smarty->assign('submittedviews2', View::get_submitted_views($group->id));
		}
	}  //for loop
	} //1st if
		
		
	}else {
	 $smarty->assign('submittedviews2', View::get_submitted_views($group->id));	
	}
	
	//end
	
    }
}

if (group_user_can_assess_submitted_views($group->id, $USER->get('id')))
{
	$smarty->assign('formativeviews', View::get_formative_views($group->id, $USER->get('id')));
	//start-esha
	$smarty->assign('formativeviews2', View::get_formative_courseviews($group->id, $USER->get('id')));
	//end
	
}

//$smarty->assign('finalviews', View::get_final_views_for_outcome($main_group->id, $group->outcome));


/*$viewFormative = @get_records_sql_array('SELECT v.id, v.title, v.description, v.owner, v.ownerformat, v.self_level_formative, v.self_describe_formative,v.formativecount
            FROM {view} v
            WHERE v.group = ?			
            AND v.self_describe_formative IS NOT NULL
			ORDER BY v.title, v.id',
			array($group->id)
	);*/
//Start of view status login by Shashank
$commmems = @get_records_sql_array(
	'SELECT member
	    FROM {group_member} gm
	    WHERE gm.group = ?
	    AND gm.role != ?
		AND gm.role != ?
	    AND gm.role != ?',
	array($group->id, 'member', 'admin','chair')
);
	
$chairmems = @get_records_sql_array(
	'SELECT member
	    FROM {group_member} gm
	    WHERE gm.group = ?	    
	    AND gm.role = ?',
	array($group->id, 'chair')
);
	//outcome
//$summativeViews = View::get_submitted_views_for_outcome($group->id, $group->outcome);
$outrec = get_record('outcomes','id',$group->outcome);
	  if($outrec->special_access){
		$viewdata = get_records_sql_array('
            SELECT v.id, v.title, v.description, v.owner, v.ownerformat, v.group, v.institution, v.assessCount
            FROM {view} v
		INNER JOIN {usr} u ON (u.id = v.owner)
            WHERE submittedgroup = ?
		AND u.primary_focus = ?
            ORDER BY title, id',
            array($group->id, $outrec->special_access)
        	);
	  }else{
        	$viewdata = get_records_sql_array('
            SELECT id, title, description, owner, ownerformat, "group", institution, assessCount
            FROM {view}
            WHERE submittedgroup = ?
            ORDER BY title, id',
            array($group->id)
        	);
	  }

foreach($viewdata as $view)
{		
	$assessment = @get_records_sql_array(
		'SELECT *
		   FROM {outcome_results} 
		   WHERE outcome = ?
		   AND view_id = ?
		   AND rubric_no = 0
		   AND (submitted = 1 OR submitted = 2)',
		array($group->outcome, $view->id)
		);
	
	$view_suboutcomes = get_records_sql_array("SELECT id from outcomes where main_outcome=?", array($group->outcome));
		
	$assessedviews = @get_records_sql_array(
		'SELECT *
		   FROM {outcome_results} 		   
		   WHERE view_id = ?
		   AND rubric_no = 0
		   AND (submitted = 1 OR submitted = 2)',
		array($view->id)
		);	
		
		$chairs=0;
		$coms=0;
		if($chairmems)
		{
			$chairs=count($chairmems);
		}
		if($commmems)
		{
			$coms=count($commmems);		
		}
		if($assessment && count($assessment) == $chairs+$coms)		{			
			
			if(count($assessedviews)== (count($assessment)*(count($view_suboutcomes)+1)))
			{
				update_record('view', array('assessCount' => 2), array('id' => $view->id));
				$smarty->assign('status',2);
			}
			else
			{
				update_record('view', array('assessCount' => 1), array('id' => $view->id));
				$smarty->assign('status',1);
			}
		}		
		else
		{
			update_record('view', array('assessCount' => 1), array('id' => $view->id));
			$smarty->assign('status',1);
		}		
}

//start courseoffering
$courseofferingdes= get_record('course_offering', 'id', $group->courseoffering); 
$coursetempdes =get_record('course_template', 'id',$courseofferingdes->coursetmp_id);
$sampledes =get_courseoutcomes($coursetempdes->id);
$outcomelists= @get_records_sql_array('SELECT  c.id FROM {courseoutcomes} c INNER JOIN {course_template} ct 
				  ON c.coursetemplate_id = ct.id
				WHERE c.coursetemplate_id =? ', array($coursetempdes->id)
				);
	  $course_lists = array();
	  $i=0;
	  foreach($outcomelists as $crstest){
		
		$course_lists[$i]=$crstest->id;
		$i++;
	}
	for ($i= 1; $i <= count($course_lists);$i++){
	printf($course_lists[$i]);
	$courseoutrec = get_record('courseoutcomes','id',$course_lists[$i]);
	  if($courseoutrec->special_access){
		$viewdata2 = get_records_sql_array('
            SELECT v.id, v.title, v.description, v.owner, v.ownerformat, v.group, v.institution, v.assessCount
            FROM {view} v
		INNER JOIN {usr} u ON (u.id = v.owner)
            WHERE submittedgroup = ?
		AND u.primary_focus = ?
            ORDER BY title, id',
            array($group->id, $courseoutrec->special_access)
        	);
	  }else{
        	$viewdata2 = get_records_sql_array('
            SELECT id, title, description, owner, ownerformat, "group", institution, assessCount
            FROM {view}
            WHERE submittedgroup = ?
            ORDER BY title, id',
            array($group->id)
        	);
	  }
	}	//for loop
	  
	  
foreach($viewdata2 as $view2)
{		
	$assessment2 = @get_records_sql_array(
		'SELECT *
		   FROM {courseoutcome_results} 
		   WHERE courseoutcome = ?
		   AND view_id = ?
		   AND rubric_no = 0
		   AND (submitted = 1 OR submitted = 2)',
		array($course_lists[$i], $view->id)
		);
	
	$view_subcourseoutcomes = get_records_sql_array("SELECT id from courseoutcomes where main_courseoutcome=?", array($course_lists[$i]));
		
	$assessedviews2 = @get_records_sql_array(
		'SELECT *
		   FROM {courseoutcome_results} 		   
		   WHERE view_id = ?
		   AND rubric_no = 0
		   AND (submitted = 1 OR submitted = 2)',
		array($view->id)
		);	
		
		$chairs=0;
		$coms=0;
		if($chairmems)
		{
			$chairs=count($chairmems);
		}
		if($commmems)
		{
			$coms=count($commmems);		
		}
		if($assessment2 && count($assessment2) == $chairs+$coms)		{			
			
			if(count($assessedviews2)== (count($assessment2)*(count($view_subcourseoutcomes)+1)))
			{
				update_record('view', array('assessCount' => 2), array('id' => $view->id));
				$smarty->assign('status',2);
			}
			else
			{
				update_record('view', array('assessCount' => 1), array('id' => $view->id));
				$smarty->assign('status',1);
			}
		}		
		else
		{
			update_record('view', array('assessCount' => 1), array('id' => $view->id));
			$smarty->assign('status',1);
		}		
}
	  
//end 
//End of view status logic
//$smarty->assign('finalviews2', View::get_submitted_views_for_outcome($main_group->id, $group->outcome));
$groupinfo = get_record_sql(
    'SELECT u.role
       FROM {group_member} u
	   WHERE u.group=?	
       AND u.member = ?',
    array($group->id,$USER->get('id'))
);

if($groupinfo->role=="chair")
{
/*$finalviews = @get_records_sql_array(
	'SELECT *
	    FROM {view}
	    WHERE assessCount = 2'	
);*/
$smarty->assign('finalviews', View::get_final_views_for_outcome($group->id,$group->outcome));
$smarty->assign('finalviews2', View::get_final_views_for_courseoutcome($group->id,$course_lists[$i]));
}

$smarty->assign('subgroup',$subgroup);
$smarty->assign('role', $role);
//$smarty->assign('GROUP',group_get_menu_tabs($group->id) )
$smarty->display('group/view.tpl');

?>
