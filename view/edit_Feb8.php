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

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'edit');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$view = new View(param_integer('id'));

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

// If the view has been submitted, disallow editing
if ($view->is_submitted()) {
    $submittedto = $view->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'view', $submittedto['name']));
}

$group = $view->get('group');
$institution = $view->get('institution');
View::set_nav($group, $institution);

$new = param_boolean('new', 0);

if ($new) {
    define('TITLE', get_string('createviewsteptwo', 'view'));
}
else {
    define('TITLE', get_string('editviewdetails', 'view', $view->get('title')));
}

$heading = TITLE; // for the smarty template

require_once('pieforms/pieform.php');

$formatstring = '%s (%s)';
$ownerformatoptions = array(
    FORMAT_NAME_FIRSTNAME => sprintf($formatstring, get_string('firstname'), $USER->get('firstname')),
    FORMAT_NAME_LASTNAME => sprintf($formatstring, get_string('lastname'), $USER->get('lastname')),
    FORMAT_NAME_FIRSTNAMELASTNAME => sprintf($formatstring, get_string('fullname'), full_name())
);

$preferredname = $USER->get('preferredname');
if ($preferredname !== '') {
    $ownerformatoptions[FORMAT_NAME_PREFERREDNAME] = sprintf($formatstring, get_string('preferredname'), $preferredname);
}
$studentid = (string)get_field('artefact', 'title', 'owner', $USER->get('id'), 'artefacttype', 'studentid');
if ($studentid !== '') {
    $ownerformatoptions[FORMAT_NAME_STUDENTID] = sprintf($formatstring, get_string('studentid'), $studentid);
}
$ownerformatoptions[FORMAT_NAME_DISPLAYNAME] = sprintf($formatstring, get_string('displayname'), display_name($USER));

$editview = array(
    'name'     => 'editview',
    'method'   => 'post',
    'autofocus' => 'title',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'elements' => array(
        'id' => array(
            'type'  => 'hidden',
            'value' => $view->get('id'),
        ),
        'new' => array(
            'type' => 'hidden',
            'value' => $new,
        ),
        'title' => array(
            'type'         => 'text',
            'title'        => get_string('title','view'),
            'defaultvalue' => $view->get('title'),
            'rules'        => array( 'required' => true ),
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('description','view'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => $view->get('description'),
        ),
        'tags'        => array(
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdescprofile'),
            'defaultvalue' => $view->get('tags'),
            'help'         => true,
        ),
    ),
);

if (!($group || $institution)) {
    $default = $view->get('ownerformat');
    if (!$default) {
        $default = FORMAT_NAME_DISPLAYNAME;
    }
    $editview['elements']['ownerformat'] = array(
        'type'         => 'select',
        'title'        => get_string('ownerformat','view'),
        'description'  => get_string('ownerformatdescription','view'),
        'options'      => $ownerformatoptions,
        'defaultvalue' => $default,
        'rules'        => array('required' => true),
    );

//Start-Anusha
	if($view->get('submittedoutcome')){
		$editview['elements']['viewout'] = array(
					'type' => 'hidden',
					'value' => true,
		);		
	}
}else if($group) {
	$grouprec = get_record('group', 'id', $group);
	if($grouprec->outcome){
	$outrec = get_record('outcomes','id',$grouprec->outcome);

		if($view->get('submittedoutcome')){
			$outset = true;
		}else{
			$outset = false;
		}
		$editview['elements']['outcome'] = array(
					'type' => 'checkbox',
					'title' => 'Associate with '.$outrec->outcome_name.' outcome',
					'defaultvalue' => $outset,
	);
	}
		$editview['elements']['groupout'] = array(
					'type' => 'hidden',
					'value' => $grouprec->outcome,
	);
//End-Anusha
}

if ($new) {
    $editview['elements']['submit'] = array(
        'type'  => 'cancelbackcreate',
        'value' => array(get_string('cancel'), get_string('back','view'), get_string('next')),
        'confirm' => array(get_string('confirmcancelcreatingview', 'view'), null, null),
    );
}
else {
    $editview['elements']['submit'] = array(
        'type'  => 'submit',
        'value' => get_string('done'),
    );
}
$editview = pieform($editview);

function editview_cancel_submit() {
    global $view, $new, $group, $institution;
    if ($new) {
        $view->delete();
    }
    if ($group) {
        redirect('/view/groupviews.php?group='.$group);
    }
    if ($institution) {
        redirect('/view/institutionviews.php?institution='.$institution);
    }
    redirect('/view');
}

function editview_submit(Pieform $form, $values) {

    global $view, $SESSION;

    if (param_boolean('back')) {
        redirect('/view/blocks.php?id=' . $view->get('id') . '&new=' . $new);
    }

    $view->set('title', $values['title']);
    $view->set('description', $values['description']);
    $view->set('tags', $values['tags']);
    if (isset($values['ownerformat']) && $view->get('owner')) {
        $view->set('ownerformat', $values['ownerformat']);
    }
	
//Start-Anusha
	if($values['outcome'] == true) {
		$view->set('submittedoutcome', $values['groupout']);
	}else {
		if(!$values['viewout']){
			$view->set('submittedoutcome',null);
		}
	}
//End-Anusha

    $view->commit();

    if ($values['new']) {
        $redirecturl = '/view/access.php?id=' . $view->get('id') . '&new=1';
    } 
    else {
        $SESSION->add_ok_msg(get_string('viewsavedsuccessfully', 'view'));
        if ($view->get('group')) {
            $redirecturl = '/view/groupviews.php?group='.$view->get('group');
        }
        else if ($view->get('institution')) {
            $redirecturl = '/view/institutionviews.php?institution=' . $view->get('institution');
        }
        else {
            $redirecturl = '/view/index.php';
        }
    }
    redirect($redirecturl);
}

$smarty = smarty(array(), array(), array(), array('sidebars' => false));
$smarty->assign('PAGEHEADING', hsc($heading));
$smarty->assign('editview', $editview);
$smarty->display('view/edit.tpl');

?>
