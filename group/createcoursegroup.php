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


define('MENUITEM', 'groups/groupsiown');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('group.php');

define('TITLE', get_string('createcoursegroup', 'group'));

if (!group_can_create_groups()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

//Start -Eshwari

 $courseoutcomes = @get_records_sql_array(
	'SELECT o.id,o.courseoutcome_name, o.degree_id
		FROM {courseoutcomes} o
		INNER JOIN {courseoutcomes} o1
		WHERE o.id != 0
		AND (o.main_courseoutcome = 0 OR (o.main_courseoutcome = o1.id AND o1.eval_required = 1))'
);
 
 $notavailable = @get_records_sql_array(
	'SELECT o.id
		FROM {courseoutcomes} o
		INNER JOIN {group} g ON (g.courseoutcome = o.id AND g.deleted=0)'
);
 
 $options[0] = array(
        'value'    => 'Select an Course Outcome',
        'disabled' => false,
    );
 
 foreach ($courseoutcomes as $courseoutcome) {
	$disable = false;
	foreach($notavailable as $not){
		if($courseoutcome->id == $not->id){
			$disable = true;
			break;
		}	
	}
	$options[$courseoutcome->id] = array(
        'value'    => $courseoutcomes->courseoutcomes_name,
        'disabled' => $disable,
    );
}
 
 if($_GET['courseoutcome']!=null)
{
	$createcoursegroup = pieform(array(
		'name'     => 'createcoursegroup',
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
			'groupcourseoutcome' => array(
					'type' => 'select',
					'title' => 'Associate the group to an course outcome',
					'collapseifoneoption' => false,
					'options' => $options,
					'disabled' 	   => $disable,
					'defaultvalue' => $_GET['courseoutcome'],
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

}
else
{
	$createcoursegroup = pieform(array(
		'name'     => 'createcoursegroup',
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
			'groupcourseoutcome' => array(
					'type' => 'select',
					'title' => 'Associate the group to an course outcome',
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
}
 
$smarty = smarty();
$smarty->assign('createcoursegroup', $createcoursegroup);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('group/createcoursegroup.tpl');
 
 
 function createcoursegroup_validate(Pieform $form, $values) {
	
    	$out = $values['groupcourseoutcome'];
		
		if($out){
		$out_rec = get_record('courseoutcomes','id',$out);
		if($out_rec){
			$deg_rec = get_record('degree_courses','id',$out_rec->degree_id);
			if($deg_rec->degree_type!=null)
			$degree_name = $deg_rec->degree_type . '-' . $values['name'];
			else //Change by Shashank in edit.php for group name
			$degree_name = $values['name'];
		}
		}
    if (get_field('group', 'id', 'name', $degree_name)) {
        $form->set_error('name', get_string('groupalreadyexists', 'group'));
    }
   
}
 
 
 function createcoursegroup_cancel_submit() {
    redirect('/group/mygroups.php');
}
 
 function createcoursegroup_submit(Pieform $form, $values) {
    global $USER;
    global $SESSION;

    list($grouptype, $jointype) = explode('.', $values['grouptype']);
    $values['public'] = (isset($values['public'])) ? $values['public'] : 0;
    $values['usersautoadded'] = (isset($values['usersautoadded'])) ? $values['usersautoadded'] : 0;


	
	if($values['groupcourseoutcome'] == 0) {
		$out = null;
	}else{
		$out = $values['groupcourseoutcome'];
		$out_rec = get_record('courseoutcomes','id',$out);
		if($out_rec){
			$deg_rec = get_record('degree_courses','id',$out_rec->degree_id);
			if($deg_rec->degree_type!=null)
			$degree_name = $deg_rec->degree_type . '-' . $values['name'];
			else //Change by Shashank in edit.php for group name
			$degree_name = $values['name'];			
		}
	}
   
    $id = group_coursecreate(array(
        'name'           => $degree_name,
        'description'    => $values['description'],
        'grouptype'      => $grouptype,
        'jointype'       => $jointype,
		'courseoutcome'        => $out,
		'public'         => intval($values['public']),
        'usersautoadded' => intval($values['usersautoadded']),
        'members'        => array($USER->get('id') => 'admin'),		
		'parent_group'   => $_GET['id'],
    ));
	

    $USER->reset_grouproles();

    $SESSION->add_ok_msg(get_string('groupsaved', 'group'));

	//update_record('group',$_GET['courseoutcome'],$_GET['id']);
    redirect('/group/view.php?id=' . $id);
}
 
 
 
 
 
 
 


?>