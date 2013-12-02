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
define('MENUITEM', 'groups/groupsiown');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('group.php');
define('TITLE', get_string('creategroup', 'group'));

if (!group_can_create_groups()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

//Start-Anusha
$outcomes = @get_records_sql_array(
	'SELECT o.id,o.outcome_name
		FROM {outcomes} o
		INNER JOIN {outcomes} o1
		WHERE o.id != 0
		AND (o.main_outcome = 0 OR (o.main_outcome = o1.id AND o1.eval_required = 1))'
);

$notavailable = @get_records_sql_array(
	'SELECT o.id
		FROM {outcomes} o
		INNER JOIN {group} g ON (g.outcome = o.id AND g.deleted=0)'
);

$options[0] = array(
        'value'    => 'Select an outcome',
        'disabled' => false,
    );
foreach ($outcomes as $outcome) {
	$disable = false;
	foreach($notavailable as $not){
		if($outcome->id == $not->id){
			$disable = true;
			break;
		}	
	}
	$options[$outcome->id] = array(
        'value'    => $outcome->outcome_name,
        'disabled' => $disable,
    );
}
/*$creategroup = pieform(array(
    'name'     => 'creategroup',
    'method'   => 'post',
    'plugintype' => 'core',
    'pluginname' => 'groups',
    'elements' => array(
        'name' => array(
            'type'         => 'text',
            'title'        => get_string('groupname', 'group'),
            'rules'        => array( 'required' => true, 'maxlength' => 128 ),
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('groupdescription', 'group'),
            'rows'         => 10,
            'cols'         => 55,
        ),
        'grouptype' => array(
            'type'         => 'select',
            'title'        => get_string('grouptype', 'group'),
            'options'      => group_get_grouptype_options(),
            'defaultvalue' => 'standard.open',
            'help'         => true,
        ),
        'public' => array(
            'type'         => 'select',
            'title'        => get_string('publiclyviewablegroup', 'group'),
            'description'  => get_string('publiclyviewablegroupdescription', 'group'),
            'options'      => array(true  => get_string('yes'),
                                    false => get_string('no')),
            'defaultvalue' => 'no',
            'help'         => true,
            'ignore'       => !(get_config('createpublicgroups') == 'all' || get_config('createpublicgroups') == 'admins' && $USER->get('admin')),
        ),
        'usersautoadded' => array(
            'type'         => 'select',
            'title'        => get_string('usersautoadded', 'group'),
            'description'  => get_string('usersautoaddeddescription', 'group'),
            'options'      => array(true  => get_string('yes'),
                                    false => get_string('no')),
            'defaultvalue' => 'no',
            'help'         => true,
            'ignore'       => !$USER->get('admin'),
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('savegroup', 'group'), get_string('cancel')),
        ),
    ),
));*/
$creategroup = pieform(array(
    'name'     => 'creategroup',
    'method'   => 'post',
    'plugintype' => 'core',
    'pluginname' => 'groups',
    'elements' => array(
        'name' => array(
            'type'         => 'text',
            'title'        => get_string('groupname', 'group'),
            'rules'        => array( 'required' => true, 'maxlength' => 128 ),
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('groupdescription', 'group'),
            'rows'         => 10,
            'cols'         => 55,
        ),
        'grouptype' => array(
            'type'         => 'select',
            'title'        => get_string('grouptype', 'group'),
            'options'      => group_get_grouptype_options(),
            'defaultvalue' => 'standard.open',
            'help'         => true,
        ),
		'groupoutcome' => array(
				'type' => 'select',
				'title' => 'Associate the group to an outcome',
				'collapseifoneoption' => false,
				'options' => $options,
		),
        'public' => array(
            'type'         => 'select',
            'title'        => get_string('publiclyviewablegroup', 'group'),
            'description'  => get_string('publiclyviewablegroupdescription', 'group'),
            'options'      => array(true  => get_string('yes'),
                                    false => get_string('no')),
            'defaultvalue' => 'no',
            'help'         => true,
            'ignore'       => !(get_config('createpublicgroups') == 'all' || get_config('createpublicgroups') == 'admins' && $USER->get('admin')),
        ),
        'usersautoadded' => array(
            'type'         => 'select',
            'title'        => get_string('usersautoadded', 'group'),
            'description'  => get_string('usersautoaddeddescription', 'group'),
            'options'      => array(true  => get_string('yes'),
                                    false => get_string('no')),
            'defaultvalue' => 'no',
            'help'         => true,
            'ignore'       => !$USER->get('admin'),
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('savegroup', 'group'), get_string('cancel')),
        ),
    ),
));
//End-Anusha

$smarty = smarty();
$smarty->assign('creategroup', $creategroup);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('group/create.tpl');


function creategroup_validate(Pieform $form, $values) {
	//Start - Anusha
	    /*if (get_field('group', 'id', 'name', $values['name']) {
        	$form->set_error('name', get_string('groupalreadyexists', 'group'));
    	   }*/
    		$out = $values['groupoutcome'];
		if($out){
		$out_rec = get_record('outcomes','id',$out);
		if($out_rec){
			$deg_rec = get_record('degree_programs','id',$out_rec->degree_id);
			if($deg_rec->degree_type!=null)
			$degree_name = $deg_rec->degree_type . '-' . $values['name'];
			else //Change by Shashank in edit.php for group name
			$degree_name = $values['name'];
		}
		}
    if (get_field('group', 'id', 'name', $degree_name) {
        $form->set_error('name', get_string('groupalreadyexists', 'group'));
    }
    //End - Anusha
}

function creategroup_cancel_submit() {
    redirect('/group/mygroups.php');
}

function creategroup_submit(Pieform $form, $values) {
    global $USER;
    global $SESSION;

    list($grouptype, $jointype) = explode('.', $values['grouptype']);
    $values['public'] = (isset($values['public'])) ? $values['public'] : 0;
    $values['usersautoadded'] = (isset($values['usersautoadded'])) ? $values['usersautoadded'] : 0;

	//Start-Anusha
	if($values['groupoutcome'] == 0) {
		$out = null;
	}else{
		$out = $values['groupoutcome'];
		$out_rec = get_record('outcomes','id',$out);
		if($out_rec){
			$deg_rec = get_record('degree_programs','id',$out_rec->degree_id);
			if($deg_rec->degree_type!=null)
			$degree_name = $deg_rec->degree_type . '-' . $values['name'];
			else //Change by Shashank in edit.php for group name
			$degree_name = $values['name'];			
		}
	}
    /*$id = group_create(array(
        'name'           => $values['name'],
        'description'    => $values['description'],
        'grouptype'      => $grouptype,
        'jointype'       => $jointype,
        'public'         => intval($values['public']),
        'usersautoadded' => intval($values['usersautoadded']),
        'members'        => array($USER->get('id') => 'admin'),
    ));	*/
    $id = group_create(array(
        'name'           => $degree_name,
        'description'    => $values['description'],
        'grouptype'      => $grouptype,
        'jointype'       => $jointype,
		'outcome'        => $out,
        'public'         => intval($values['public']),
        'usersautoadded' => intval($values['usersautoadded']),
        'members'        => array($USER->get('id') => 'admin'),
    ));
	//End-Anusha

    $USER->reset_grouproles();

    $SESSION->add_ok_msg(get_string('groupsaved', 'group'));

    redirect('/group/view.php?id=' . $id);
}

?>
