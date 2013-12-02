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
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'view');

require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'view.php');
require('group.php');
require('outcome.php');

// access key for roaming teachers
$mnettoken = $SESSION->get('mnetuser') ? param_alphanum('mt', null) : null;

// access key for logged out users
$usertoken = (is_null($mnettoken) && get_config('allowpublicviews')) ? param_alphanum('t', null) : null;

if ($mnettoken) {
    if (!$viewid = get_view_from_token($mnettoken, false)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    if ($mnettoken != get_cookie('mviewaccess:'.$viewid)) {
        set_cookie('mviewaccess:'.$viewid, $mnettoken);
    }
}
else if ($usertoken) {
    if (!$viewid = get_view_from_token($usertoken, true)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    if ($usertoken != get_cookie('mviewaccess:'.$viewid)) {
        set_cookie('mviewaccess:'.$viewid, $usertoken);
    }
}
else {
    $viewid = param_integer('id');
    $groupid = param_integer('group',0);
}
$new = param_boolean('new');

$accessdenied = 0;
if (!can_view_view($viewid, null, $usertoken, $mnettoken)) {
	$accessdenied = 1;
}

if($accessdenied && $groupid){
if (!suboutcomes_can_view($viewid, null, $groupid)) {
	$accessdenied = 1;
}else{
	$accessdenied = 0;
}
}

if($accessdenied){
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

// Feedback list pagination requires limit/offset params
$limit    = param_integer('limit', 10);
$offset   = param_integer('offset', 0);

$view = new View($viewid);

// Create the "make feedback private form" now if it's been submitted
if (param_variable('make_private_submit', null)) {
    pieform(make_private_form(param_integer('feedback')));
}

$group = $view->get('group');

$title = $view->get('title');
define('TITLE', $title);

$submittedgroup = (int)$view->get('submittedgroup');

//Start- Anusha
$submittedoutcome = $view->get('submittedoutcome');
$admin = 0;
$finalAssessment = 0;

$grouprec = "";
if($groupid){
$grouprec = get_record('group',id,$groupid);
}
//End - Anusha

$isallowed = 0;
//Test code for Shashank
if ($submittedoutcome)
{
if ($submittedgroup){
	if(!$groupid && group_user_can_assess_submitted_views($submittedgroup, $USER->get('id'))) {  
	$isallowed = 1;
	$ismain = 1;
	}else if($grouprec && $submittedoutcome && $grouprec->outcome != $submittedoutcome){
	}else if($grouprec && $submittedoutcome && $grouprec->outcome != $submittedoutcome){

			$suboutcome = get_record('outcomes','id',$grouprec->outcome,'deleted',0);
			if(!$suboutcome){
    				throw new AccessDeniedException('Outcome does not exist');					
			}
			$temp_outcome = $suboutcome->main_outcome;
			while($temp_outcome){
				if($submittedoutcome == $temp_outcome){
					$submittedgroup = $groupid;
					$submittedoutcome = $grouprec->outcome;
					$isallowed = 1;
					$ismain = 0;
					$temp_outcome = "";
				}else{
					$suboutcome = get_record('outcomes','id',$temp_outcome,'deleted',0);
					if(!$suboutcome){
    						throw new AccessDeniedException('Outcome does not exist');							
					}
					$temp_outcome = $suboutcome->main_outcome;
				}
			}
	}
}
$backgroup = $submittedgroup;
$outcomedes = get_record('outcomes','id',$submittedoutcome,'deleted',0);
//$ownerrec = get_record('usr','id',$view->get('owner'));
//if($outcomedes->special_access){
//	if($outcomedes->special_access != $ownerrec->primary_focus){
//    		throw new AccessDeniedException(get_string('cantviewassessments', 'view'));
//	}
//}

if($isallowed){
    // The user is a tutor of the group that this view has
    // been submitted to, and is entitled to release the view, and to
    // upload an additional file when submitting feedback.
    $submittedgroup = get_record('group', 'id', $submittedgroup);
	
	//Start- Anusha
	$groupdet = get_record('group_member','group', $submittedgroup->id, 'member', $USER->get('id'));
	
	// Find out if it is admin
	if($groupdet && $groupdet->role == "chair") {
		$admin = 1;
		if($ismain){
		$memval = 'member';
		$adminval = 'admin';
		$groupmems = @get_records_sql_array(
	'SELECT member
	    FROM {group_member} gm
	    WHERE gm.group = ?
	    AND gm.role != ?
	    AND gm.role != ?',
	array($groupdet->group, $memval, $adminval)
);

		$assessment = @get_records_sql_array(
    'SELECT *
       FROM {outcome_results} 
       WHERE outcome = ?
       AND view_id = ?
       AND rubric_no = 0
       AND (submitted = 1 OR submitted = 2)',
    array($submittedoutcome, $viewid)
	);
		if($assessment && count($assessment) == count($groupmems)){
			$finalAssessment = 1;
		}
		}
	}
	
	if(!$submittedoutcome){
	//End-Anusha	
		$releaseform = pieform(array(
			'name'     => 'releaseview',
			'method'   => 'post',
			'plugintype' => 'core',
			'pluginname' => 'view',
			'autofocus' => false,
			'elements' => array(
				'submittedview' => array(
					'type'  => 'html',
					'value' => get_string('viewsubmittedtogroup', 'view', get_config('wwwroot') . 'group/view.php?id=' . $submittedgroup->id, $submittedgroup->name),
				),
				'submit' => array(
					'type'  => 'submit',
					'value' => get_string('releaseview', 'group'),
				),
			),
		));
	
	//Start-Anusha
		$selfevaluation = '';
	}else{
		$releaseform = array(
			'name'     => 'releaseview',
			'method'   => 'post',
			'plugintype' => 'core',
			'pluginname' => 'view',
			'autofocus' => false,
			'elements' => array(
				'submittedview' => array(
					'type'  => 'html',
					'value' => get_string('viewsubmittedtogroup', 'view', get_config('wwwroot') . 'group/view.php?id=' . $submittedgroup->id, $submittedgroup->name),
				),
			),
		);
		if($admin && $ismain) {
			$releaseform['elements']['submit'] = array(
					'type'  => 'submit',
					'value' => get_string('releaseview', 'group'),
			);
		}	
		$releaseform = pieform($releaseform);

		$selfevaluation = pieform(array(
			'name'     => 'selfeval',
			'method'   => 'get',
			'plugintype' => 'core',
			'pluginname' => 'view',
			'autofocus' => false,
			'elements' => array(
				'selflvl' => array(
					'type'  => 'html',
					'title' => 'Assigned Level',
					'value' => $view->get('self_level'),
				),
				'selfdescription' => array(
					'type'  => 'html',
					'title' => 'Explanation',
					'value' => $view->get('self_describe'),
				),
			),
		));
	}
	//End-Anusha
	
    $allowattachments = true;
}
else {
    $releaseform = '';
	//Start-Anusha
	$selfevaluation = '';
	//End-Anusha
    $allowattachments = false;
}
}
else
{
	if ($USER->is_logged_in() && $submittedgroup && group_user_can_assess_submitted_views($submittedgroup, $USER->get('id'))) {
    // The user is a tutor of the group that this view has
    // been submitted to, and is entitled to release the view
    $submittedgroup = get_record('group', 'id', $submittedgroup);
    if ($view->get('submittedtime')) {
        $text = get_string('viewsubmittedtogroupon', 'view', get_config('wwwroot') . 'group/view.php?id=' . $submittedgroup->id, hsc($submittedgroup->name), format_date(strtotime($view->get('submittedtime'))));
    }
    else {
        $text = get_string('viewsubmittedtogroup', 'view', get_config('wwwroot') . 'group/view.php?id=' . $submittedgroup->id, hsc($submittedgroup->name));
    }
    $releaseform = pieform(array(
        'name'     => 'releaseview',
        'method'   => 'post',
        'plugintype' => 'core',
        'pluginname' => 'view',
        'autofocus' => false,
        'elements' => array(
            'submittedview' => array(
                'type'  => 'html',
                'value' => $text,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('releaseview', 'group'),
            ),
        ),
    ));
}
else {
    $releaseform = '';
}

}

function releaseview_submit() {
    global $USER, $SESSION, $view;
    $groupid = $view->get('submittedgroup');
    $view->release($USER);
    $SESSION->add_ok_msg(get_string('viewreleasedsuccess', 'group'));
    if ($groupid) {
        // The tutor might not have access to the view any more; send
        // them back to the group page.
        redirect(get_config('wwwroot') . 'group/view.php?id='.$groupid);
    }
    redirect(get_config('wwwroot') . 'view/view.php?id='.$view->get('id'));
}
  
$viewbeingwatched = (int)record_exists('usr_watchlist_view', 'usr', $USER->get('id'), 'view', $viewid);

$feedback = $view->get_feedback($limit, $offset);
build_feedback_html($feedback);

$anonfeedback = !$USER->is_logged_in() && ($usertoken || $viewid == get_view_from_token(get_cookie('viewaccess:'.$viewid)));
if ($USER->is_logged_in() || $anonfeedback) {
    $addfeedbackform = pieform(add_feedback_form($allowattachments));
}
if ($USER->is_logged_in()) {
    $objectionform = pieform(objection_form());
}

$can_edit = $USER->can_edit_view($view) && !$submittedgroup && !$view->is_submitted();

$smarty = smarty(
    array('paginator', 'feedbacklist', 'artefact/resume/resumeshowhide.js'),
    array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">'),
    array(),
    array(
        'stylesheets' => array('style/views.css'),
        'sidebars' => false,
    )
);

$javascript = <<<EOF
var viewid = {$viewid};
addLoadEvent(function () {
    paginator = {$feedback->pagination_js}
});
EOF;

$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('new', $new);
$smarty->assign('viewid', $viewid);
$smarty->assign('viewtitle', $view->get('title'));
$smarty->assign('feedback', $feedback);

$owner = $view->get('owner');
$smarty->assign('owner', $owner);
$smarty->assign('tags', $view->get('tags'));
if ($owner) {
    $smarty->assign('ownerlink', 'user/view.php?id=' . $owner);
}
else if ($group) {
    $smarty->assign('ownerlink', 'group/view.php?id=' . $group);
}
if ($can_edit) {
    $smarty->assign('can_edit', 1);
}
if ($USER->is_logged_in() && !empty($_SERVER['HTTP_REFERER'])) {
	//Start-Anusha
	if($view->get('submittedgroup') && submittedoutcome){
		if(($_SERVER['HTTP_REFERER'] == get_config('wwwroot') . 'view/listassessments.php?id=' . $viewid .'outcome=' .$submittedoutcome) || ($_SERVER['HTTP_REFERER'] == get_config('wwwroot') . 'view/showassessments.php?id=' . $viewid .'outcome=' .$submittedoutcome) || ($_SERVER['HTTP_REFERER'] == get_config('wwwroot') . 'view/finalassessment.php?id=' . $viewid .'outcome=' .$submittedoutcome)) {
			$urlback = get_config('wwwroot') . 'group/view.php?id=' . $submittedgroup;
			$smarty->assign('backurl', $urlback);
		}
	}else{
	//End - Anusha
	
		$page = get_config('wwwroot') . 'view/view.php?id=' . $viewid . ($new ? '&new=1' : '');
		if ($_SERVER['HTTP_REFERER'] != $page) {
			$smarty->assign('backurl', $_SERVER['HTTP_REFERER']);
		}
	//Start - Anusha
	}
	//End - Anusha
}

// Provide a link for roaming teachers to return
if ($mnetviewlist = $SESSION->get('mnetviewaccess')) {
    if (isset($mnetviewlist[$view->get('id')])) {
        $returnurl = $SESSION->get('mnetuserfrom');
        require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
        if ($peer = get_peer_from_instanceid($SESSION->get('authinstance'))) {
            $smarty->assign('mnethost', array(
                'name'      => $peer->name,
                'url'       => $returnurl ? $returnurl : $peer->wwwroot,
            ));
        }
    }
}

$smarty->assign('ownername', $view->formatted_owner());
$smarty->assign('viewdescription', $view->get('description'));
$smarty->assign('viewcontent', $view->build_columns());
$smarty->assign('releaseform', $releaseform);
$smarty->assign('anonfeedback', $anonfeedback);
if (isset($addfeedbackform)) {
    $smarty->assign('addfeedbackform', $addfeedbackform);
}
if (isset($objectionform)) {
    $smarty->assign('objectionform', $objectionform);
}
$smarty->assign('viewbeingwatched', $viewbeingwatched);
//Start-Anusha
$smarty->assign('selfevaluation',$selfevaluation);
$smarty->assign('admin', $admin);
$smarty->assign('finalAssessment',$finalAssessment);
$smarty->assign('submittedoutcome',$submittedoutcome);

$smarty->assign('subgroup',$groupid);
$smarty->assign('backgroup',$backgroup);
//End-Anusha

$smarty->display('view/view.tpl');

?>
