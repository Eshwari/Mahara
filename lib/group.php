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

defined('INTERNAL') || die();

// Role related functions

/**
 * Establishes what role a user has in a given group.
 *
 * If the user is not in the group, this returns false.
 *
 * @param mixed $groupid  ID of the group to check
 * @param mixed $userid   ID of the user to check. Defaults to the logged in 
 *                        user.
 * @return mixed          The role the user has in the group, or false if they 
 *                        have no role in the group
 */
function group_user_access($groupid, $userid=null) {
    static $result;

    if (!is_logged_in()) {
        return false;
    }

    $groupid = group_param_groupid($groupid);
    $userid  = group_param_userid($userid);

    if (isset($result[$groupid][$userid])) {
        return $result[$groupid][$userid];
    }

    return $result[$groupid][$userid] = get_field('group_member', 'role', 'group', $groupid, 'member', $userid);
}

/**
 * Returns whether the given user is the only administrator in the given group.
 *
 * If the user isn't in the group, or they're not an admin, or there is another admin, false 
 * is returned.
 *
 * @param int $groupid The ID of the group to check
 * @param int $userid  The ID of the user to check
 * @returns boolean
 */
function group_is_only_admin($groupid, $userid=null) {
    static $result;

    $groupid = group_param_groupid($groupid);
    $userid  = group_param_userid($userid);

    if (isset($result[$groupid][$userid])) {
        return $result[$groupid][$userid];
    }

    return $result[$groupid][$userid] = (group_user_access($groupid, $userid) == 'admin'
        && count_records('group_member', 'group', $groupid, 'role', 'admin') == 1);
}

/**
 * Returns whether the given user is allowed to change their role to the 
 * requested role in the given group.
 *
 * This function is checking whether _role changes_ are allowed, not if a user 
 * is allowed to be added to a group.
 *
 * @param int $groupid The ID of the group to check
 * @param int $userid  The ID of the user to check
 * @param string $role The role the user wishes to switch to
 * @returns boolean
 */
function group_can_change_role($groupid, $userid, $role) {
    $groupid = group_param_groupid($groupid);
    $userid  = group_param_userid($userid);

    if (!group_user_access($groupid, $userid)) {
        return false;
    }

    // Sole remaining admins can never change their role
    if (group_is_only_admin($groupid, $userid)) {
        return false;
    }

    // admin role permissions check
    if ($role == 'admin') {
        $group = group_current_group();
        $user = new User();
        $user->find_by_id($userid);
        safe_require('grouptype', $group->grouptype);
        return in_array($group->jointype, call_static_method('GroupType' . $group->grouptype, 'user_allowed_join_types', $user));
    }

    return true;
}

/**
 * Changes a user role in a group, if this is allowed.
 *
 * @param int $groupid The ID of the group
 * @param int $userid  The ID of the user whose role needs changing
 * @param string $role The role the user wishes to switch to
 * @throws AccessDeniedException If the specified role change is not allowed. 
 *                               Check with group_can_change_role first if you 
 *                               need to.
 */
function group_change_role($groupid, $userid, $role) {
    // group_can_change_role checks whether the group and user parameters are valid
    if (!group_can_change_role($groupid, $userid, $role)) {
        throw new AccessDeniedException(get_string('usercannotchangetothisrole', 'group'));
    }

    set_field('group_member', 'role', $role, 'group', $groupid, 'member', $userid);
}

/**
 * Returns whether a user is allowed to edit views in a given group
 *
 * @param int $groupid The ID of the group
 * @param int $userid The ID of the user
 * @returns boolean
 */
function group_user_can_edit_views($groupid, $userid=null) {
    if (!is_logged_in()) {
        return false;
    }

    $groupid = group_param_groupid($groupid);
    $userid  = group_param_userid($userid);

    return get_field_sql('
        SELECT
            r.edit_views
        FROM
            {group_member} m
            INNER JOIN {group} g ON (m.group = g.id AND g.deleted = 0)
            INNER JOIN {grouptype_roles} r ON (g.grouptype = r.grouptype AND m.role = r.role)
        WHERE
            m.group = ?
            AND m.member = ?', array($groupid, $userid));
}

/**
 * Returns whether a user is allowed to assess views that have been submitted 
 * to the given group.
 *
 * @param int $groupid ID of group
 * @param int $userid  ID of user
 * @return boolean
 */
function group_user_can_assess_submitted_views($groupid, $userid) {
    $groupid = group_param_groupid($groupid);
    $userid  = group_param_userid($userid);

    return get_field_sql('
        SELECT
            r.see_submitted_views
        FROM
            {group_member} m
            INNER JOIN {group} g ON (m.group = g.id AND g.deleted = 0)
            INNER JOIN {grouptype_roles} r ON (g.grouptype = r.grouptype AND r.role = m.role)
        WHERE
            m.member = ?
            AND m.group = ?', array($userid, $groupid));
}

// Functions for creation/deletion of groups, and adding/removing users to groups

/**
 * Creates a group.
 *
 * All group creation should be done through this function, as the 
 * implementation of group creation may change over time.
 *
 * @param array $data Data required to create the group. The following 
 * key/value pairs can be specified:
 *
 * - name: The group name [required, must be unique]
 * - description: The group description [optional, defaults to empty string]
 * - grouptype: The grouptype for the new group. Must be an installed grouptype.
 * - jointype: The jointype for the new group. One of 'open', 'invite', 
 *             'request' or 'controlled'
 * - ctime: The unix timestamp of the time the group will be recorded as having 
 *          been created. Defaults to the current time.
 * - members: Array of users who should be in the group, structured like this:
 *            array(
 *                userid => role,
 *                userid => role,
 *                ...
 *            )
 * @return int The ID of the created group
 */
function group_create($data) {
    if (!is_array($data)) {
        throw new InvalidArgumentException("group_create: data must be an array, see the doc comment for this "
            . "function for details on its format");
    }

    if (!isset($data['name'])) {
        throw new InvalidArgumentException("group_create: must specify a name for the group");
    }

    if (!isset($data['grouptype']) || !in_array($data['grouptype'], group_get_grouptypes())) {
        throw new InvalidArgumentException("group_create: grouptype specified must be an installed grouptype");
    }

    safe_require('grouptype', $data['grouptype']);

    if (isset($data['jointype'])) {
        if (!in_array($data['jointype'], call_static_method('GroupType' . $data['grouptype'], 'allowed_join_types'))) {
            throw new InvalidArgumentException("group_create: jointype specified is not allowed by the grouptype specified");
        }
    }
    else {
        throw new InvalidArgumentException("group_create: jointype specified must be one of the valid join types");
    }

    if (!isset($data['ctime'])) {
        $data['ctime'] = time();
    }
    $data['ctime'] = db_format_timestamp($data['ctime']);

    if (!is_array($data['members']) || count($data['members']) == 0) {
        throw new InvalidArgumentException("group_create: at least one member must be specified for adding to the group");
    }

    $data['public'] = (isset($data['public'])) ? intval($data['public']) : 0;
    $data['usersautoadded'] = (isset($data['usersautoadded'])) ? intval($data['usersautoadded']) : 0;

    db_begin();

//Start-Anusha
    /*$id = insert_record(
        'group',
        (object) array(
            'name'           => $data['name'],
            'description'    => $data['description'],
            'grouptype'      => $data['grouptype'],
            'jointype'       => $data['jointype'],
            'ctime'          => $data['ctime'],
            'mtime'          => $data['ctime'],
            'public'         => $data['public'],
            'usersautoadded' => $data['usersautoadded'],
        ),
        'id',
        true
    );*/
	
	//Start -Eshwari added courseoutcome
	    $id = insert_record(
        'group',
        (object) array(
            'name'           => $data['name'],
            'description'    => $data['description'],
            'grouptype'      => $data['grouptype'],
            'jointype'       => $data['jointype'],
            'ctime'          => $data['ctime'],
            'mtime'          => $data['ctime'],
            'public'         => $data['public'],
            'usersautoadded' => $data['usersautoadded'],
			'outcome'        => $data['outcome'],
			'courseoutcome'  => $data['courseoutcome'],
			'coursetemplate'  => $data['coursetemplate'],
			'courseoffering'  => $data['courseoffering'],
			'parent_group'   => $data['parent_group'],
        ),
        'id',
        true
    );
//End-Anusha

    foreach ($data['members'] as $userid => $role) {
        insert_record(
            'group_member',
            (object) array(
                'group'  => $id,
                'member' => $userid,
                'role'   => $role,
                'ctime'  => $data['ctime'],
            )
        );
    }

    // Copy views for the new group
    $templates = get_column('view_autocreate_grouptype', 'view', 'grouptype', $data['grouptype']);
    $templates = get_records_sql_array("
        SELECT v.id, v.title, v.description 
        FROM {view} v
        INNER JOIN {view_autocreate_grouptype} vag ON vag.view = v.id
        WHERE vag.grouptype = 'standard'", array());
    if ($templates) {
        require_once(get_config('libroot') . 'view.php');
        foreach ($templates as $template) {
            list($view) = View::create_from_template(array(
                'group'       => $id,
                'title'       => $template->title,
                'description' => $template->description,
            ), $template->id);
            $view->set_access(array(array(
                'type'      => 'group',
                'id'        => $id,
                'startdate' => null,
                'stopdate'  => null,
                'role'      => null
            )));
        }
    }

    $data['id'] = $id;
    handle_event('creategroup', $data);
    db_commit();

    return $id;
}



/**
 * Deletes a group.
 *
 * All group deleting should be done through this function, even though it is 
 * simple. What is required to perform group deletion may change over time.
 *
 * @param int $groupid The group to delete
 *
 * {{@internal Maybe later we can have a group_can_be_deleted function if 
 * necessary}}
 */
function group_delete($groupid) {
    $groupid = group_param_groupid($groupid);
    update_record('group',
        array(
            'deleted' => 1,
            'name' => get_field('group', 'name', 'id', $groupid) . '.deleted.' . time(),
        ),
        array(
            'id' => $groupid,
        )
    );
}

/**
 * Adds a member to a group.
 *
 * Doesn't do any jointype checking, that should be handled by the caller.
 *
 * TODO: it should though. We should probably have group_user_can_be_added
 *
 * @param int $groupid
 * @param int $userid
 * @param string $role
 */
function group_add_user($groupid, $userid, $role=null) {
    $groupid = group_param_groupid($groupid);
    $userid  = group_param_userid($userid);

    $gm = new StdClass;
    $gm->member = $userid;
    $gm->group = $groupid;
    $gm->ctime =  db_format_timestamp(time());
    if (!$role) {
        $role = get_field_sql('SELECT gt.defaultrole FROM {grouptype} gt, {group} g WHERE g.id = ? AND g.grouptype = gt.name', array($groupid));
    }
    $gm->role = $role;

    db_begin();
    insert_record('group_member', $gm);
    delete_records('group_member_request', 'group', $groupid, 'member', $userid);
    handle_event('userjoinsgroup', $gm);
    db_commit();
}

/**
 * Checks whether a user is allowed to leave a group.
 *
 * This checks things like if they're the owner and the group membership type
 *
 * @param mixed $group  DB record or ID of group to check
 * @param int   $userid (optional, will default to logged in user)
 */
function group_user_can_leave($group, $userid=null) {
    global $USER;
    static $result;

    $userid = optional_userid($userid);

    if (is_numeric($group)) {
        if (!$group = get_record('group', 'id', $group, 'deleted', 0)) {
            return false;
        }
    }

    // Return cached value if we have it
    if (isset($result[$group->id][$userid])) {
        return $result[$group->id][$userid];
    }

    if ($group->jointype == 'controlled' && group_user_access($group->id, $USER->get('id')) != 'admin') {
        return ($result[$group->id][$userid] = false);
    }

    if (group_is_only_admin($group->id, $userid)) {
        return ($result[$group->id][$userid] = false);
    }

    return ($result[$group->id][$userid] = true);
}

/**
 * Removes a user from a group.
 *
 * Also removes view access given by the user to the group
 *
 * @param int $groupid ID of group
 * @param int $userid  ID of user to remove
 */
function group_remove_user($groupid, $userid=null, $force=false) {
    // group_user_can_leave checks the validity of groupid and userid
    if (!$force && !group_user_can_leave($groupid, $userid)) {
        throw new AccessDeniedException(get_string('usercantleavegroup', 'group'));
    }
    db_begin();
    delete_records('group_member', 'group', $groupid, 'member', $userid);
    delete_records_sql(
        'DELETE FROM {view_access_group}
        WHERE "group" = ?
        AND view IN (
            SELECT v.id
            FROM {view} v
            WHERE v.owner = ?
        )',
        array($groupid, $userid)
    );
    db_commit();

    require_once(get_config('docroot') . 'interaction/lib.php');
    $interactions = get_column('interaction_instance', 'id', 'group', $groupid);
    foreach ($interactions as $interaction) {
        interaction_instance_from_id($interaction)->interaction_remove_user($userid);
    }
}

// Pieforms for various operations on groups

/**
 * Form for users to join a given group
 */
function group_get_join_form($name, $groupid, $returnto='view') {
    return pieform(array(
        'name' => $name,
        'successcallback' => 'joingroup_submit',
        'autofocus' => false,
        'elements' => array(
            'join' => array(
                'type' => 'submit',
                'value' => get_string('joingroup', 'group')
            ),
            'group' => array(
                'type' => 'hidden',
                'value' => $groupid
            ),
            'returnto' => array(
                'type' => 'hidden',
                'value' => $returnto
            ),
        )
    ));
}

/**
 * Form for accepting/declining a group invite
 */
function group_get_accept_form($name, $groupid, $returnto) {
    return pieform(array(
       'name'     => $name,
       'renderer' => 'oneline',
       'successcallback' => 'group_invite_submit',
       'elements' => array(
            'accept' => array(
                'type'  => 'submit',
                'value' => get_string('acceptinvitegroup', 'group')
            ),
            'decline' => array(
                'type'  => 'submit',
                'value' => get_string('declineinvitegroup', 'group')
            ),
            'group' => array(
                'type' => 'hidden',
                'value' => $groupid
            ),
            'returnto' => array(
                'type' => 'hidden',
                'value' => $returnto
            )
        )
    ));
}

/**
 * Form for adding a user to a group
 */
function group_get_adduser_form($userid, $groupid) {
    return pieform(array(
        'name'                => 'adduser' . $userid,
        'successcallback'     => 'group_adduser_submit',
        'renderer'            => 'div',
        'elements'            => array(
            'group' => array(
                'type'    => 'hidden',
                'value' => $groupid,
            ),
            'member' => array(
                'type'  => 'hidden',
                'value' => $userid,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('add') . ' ' . display_name($userid),
            ),
        ),
    ));
}

/**
 * Form for removing a user from a group
 */
function group_get_removeuser_form($userid, $groupid) {
    require_once('pieforms/pieform.php');
    return pieform(array(
        'name'                => 'removeuser' . $userid,
        'validatecallback'    => 'group_removeuser_validate',
        'successcallback'     => 'group_removeuser_submit',
        'renderer'            => 'oneline',
        'elements'            => array(
            'group' => array(
                'type'    => 'hidden',
                'value' => $groupid,
            ),
            'member' => array(
                'type'  => 'hidden',
                'value' => $userid,
            ),
            'removeuser' => array(
                'type'  => 'submit',
                'value' => get_string('removefromgroup', 'group'),
            ),
        ),
    ));
}

// Functions for handling submission of group related forms

function joingroup_submit(Pieform $form, $values) {
    global $SESSION, $USER;
    group_add_user($values['group'], $USER->get('id'));
    $SESSION->add_ok_msg(get_string('joinedgroup', 'group'));
    if (substr($values['returnto'], 0, 1) == '/') {
        $next = $values['returnto'];
    }
    else {
        $next = '/group/view.php?id=' . $values['group'];
    }
    redirect($next);
}

function group_invite_submit(Pieform $form, $values) {
    global $SESSION, $USER;
    $inviterecord = get_record('group_member_invite', 'member', $USER->get('id'), 'group', $values['group']);
    if ($inviterecord) {
        delete_records('group_member_invite', 'group', $values['group'], 'member', $USER->get('id'));
        if (isset($values['accept'])) {
            group_add_user($values['group'], $USER->get('id'), $inviterecord->role);
            $SESSION->add_ok_msg(get_string('groupinviteaccepted', 'group'));
            if (substr($values['returnto'], 0, 1) == '/') {
                $next = $values['returnto'];
            }
            else {
                $next = '/group/view.php?id=' . $values['group'];
            }
            redirect($next);
        }
        else {
            $SESSION->add_ok_msg(get_string('groupinvitedeclined', 'group'));
            redirect($values['returnto'] == 'find' ? '/group/find.php' : '/group/mygroups.php');
        }
    }
}

function group_adduser_submit(Pieform $form, $values) {
    global $SESSION;
    $group = (int)$values['group'];
    if (group_user_access($group) != 'admin') {
        $SESSION->add_error_msg(get_string('accessdenied', 'error'));
        redirect('/group/members.php?id=' . $group . '&membershiptype=request');
    }
    group_add_user($group, $values['member']);
    $SESSION->add_ok_msg(get_string('useradded', 'group'));
    if (count_records('group_member_request', 'group', $group)) {
        redirect('/group/members.php?id=' . $group . '&membershiptype=request');
    }
    redirect('/group/members.php?id=' . $group);
}

function group_removeuser_validate(Pieform $form, $values) {
    global $user, $group, $SESSION;
    if (!group_user_can_leave($values['group'], $values['member'])) {
        $form->set_error('submit', get_string('usercantleavegroup', 'group'));
    }
}

function group_removeuser_submit(Pieform $form, $values) {
    global $SESSION;
    $group = (int)$values['group'];
    if (group_user_access($group) != 'admin') {
        $SESSION->add_error_msg(get_string('accessdenied', 'error'));
        redirect('/group/members.php?id=' . $group);
    }
    group_remove_user($group, $values['member']);
    $SESSION->add_ok_msg(get_string('userremoved', 'group'));
    redirect('/group/members.php?id=' . $group);
}

// Miscellaneous group related functions

/**
 * Returns a list of user IDs who are admins for a group
 *
 * @param int ID of group
 * @return array
 */
function group_get_admin_ids($groupid) {
    return (array)get_column_sql("SELECT member
        FROM {group_member}
        WHERE \"group\" = ?
        AND role = 'admin'", $groupid);
}

/**
 * Gets information about what the roles in a given group are able to do
 *
 * @param int $groupid ID of group to get role information for
 * @return array
 */
function group_get_role_info($groupid) {
    $roles = get_records_sql_assoc('SELECT role, edit_views, see_submitted_views, gr.grouptype FROM {grouptype_roles} gr
        INNER JOIN {group} g ON g.grouptype = gr.grouptype
        WHERE g.id = ?', array($groupid));
    foreach ($roles as $role) {
        $role->display = get_string($role->role, 'grouptype.'.$role->grouptype);
        $role->name = $role->role;
    }
    return $roles;
}

function group_get_default_artefact_permissions($groupid) {
    $type = get_field('group', 'grouptype', 'id', $groupid);
    safe_require('grouptype', $type);
    return call_static_method('GroupType' . $type, 'default_artefact_rolepermissions');
}

/**
 * Sets up groups for display in mygroups.php and find.php
 *
 * @param array $groups    Initial group data, including the current user's 
 *                         membership type in each group. See mygroups.php for
 *                         the query to build this information.
 * @param string $returnto Where forms generated for display should be told to return to
 */
function group_prepare_usergroups_for_display($groups, $returnto='mygroups') {
    if (!$groups) {
        return;
    }

    // Retrieve a list of all the group admins, for placing in each $group object
    $groupadmins = array();
    $groupids = array_map(create_function('$a', 'return $a->id;'), $groups);
    if ($groupids) {
        $groupadmins = get_records_sql_array('SELECT "group", member
            FROM {group_member}
            WHERE "group" IN (' . implode(',', db_array_to_ph($groupids)) . ")
            AND role = 'admin'", $groupids);
			
        if (!$groupadmins) {
            $groupadmins = array();
        }
    }

    $i = 0;
    foreach ($groups as $group) {
        $group->admins = array();
        foreach ($groupadmins as $admin) {
            if ($admin->group == $group->id) {
                $group->admins[] = $admin->member;
            }
        }
        $group->description = str_shorten_html($group->description, 100, true);
        if ($group->membershiptype == 'member') {
            $group->canleave = group_user_can_leave($group->id);
        }
        else if ($group->jointype == 'open') {
            $group->groupjoin = group_get_join_form('joingroup' . $i++, $group->id);
        }
        else if ($group->membershiptype == 'invite') {
            $group->invite = group_get_accept_form('invite' . $i++, $group->id, $returnto);
        }
    }
}


function group_get_membersearch_data($group, $query, $offset, $limit, $membershiptype) {
    global $USER;
    $results = get_group_user_search_results($group, $query, $offset, $limit, $membershiptype);

    $params = array();
    if (!empty($query)) {
        $params[] = 'query=' . $query;
    }
    $params[] = 'limit=' . $limit;
    if (!empty($membershiptype)) {
        $params[] = 'membershiptype=' . $membershiptype;
    }
    $searchurl = get_config('wwwroot') . 'group/members.php?id=' . $group . '&amp;' . join('&amp;', $params);

    $smarty = smarty_core();

    $role = group_user_access($group);
    $userid = $USER->get('id');
    foreach ($results['data'] as &$r) {
        if ($role == 'admin' && ($r['id'] != $userid || group_user_can_leave($group, $r['id']))) {
            $r['removeform'] = group_get_removeuser_form($r['id'], $group);
        }
        // NOTE: this is a quick approximation. We should really check whether, 
        // for each role in the group, that the user can change to it (using 
        // group_can_change_role).  This only controls whether the 'change 
        // role' link appears though, so it doesn't matter too much. If the 
        // user clicks on this link, changerole.php does the full check and 
        // sends them back here saying that the user has no roles they can 
        // change to anyway.
        $r['canchangerole'] = !group_is_only_admin($group, $r['id']);
    }

    if (!empty($membershiptype)) {
        if ($membershiptype == 'request') {
            foreach ($results['data'] as &$r) {
                $r['addform'] = group_get_adduser_form($r['id'], $group);
                // TODO: this will suck when there's quite a few on the page, 
                // would be better to grab all the reasons in one go
                $r['reason']  = get_field('group_member_request', 'reason', 'group', $group, 'member', $r['id']);
            }
        }
        $smarty->assign('membershiptype', $membershiptype);
    }

    $results['cdata'] = array_chunk($results['data'], 2);
    $results['roles'] = group_get_role_info($group);
    $smarty->assign_by_ref('results', $results);
    $smarty->assign('searchurl', $searchurl);
    $smarty->assign('pagebaseurl', $searchurl);
    $smarty->assign('caneditroles', group_user_access($group) == 'admin');
    $smarty->assign('group', $group);
    $html = $smarty->fetch('group/membersearchresults.tpl');

    $pagination = build_pagination(array(
        'id' => 'member_pagination',
        'class' => 'center',
        'url' => $searchurl,
        'count' => $results['count'],
        'limit' => $limit,
        'offset' => $offset,
        'datatable' => 'membersearchresults',
        'jsonscript' => 'group/membersearchresults.php',
        'firsttext' => '',
        'previoustext' => '',
        'nexttext' => '',
        'lasttext' => '',
        'numbersincludefirstlast' => false,
        'resultcounttextsingular' => get_string('member', 'group'),
        'resultcounttextplural' => get_string('members', 'group'),
    ));

    return array($html, $pagination, $results['count'], $offset, $membershiptype);
}


/**
 * Returns a list of available grouptypes
 *
 * @return array
 */
function group_get_grouptypes() {
    static $grouptypes = null;

    if (is_null($grouptypes)) {
        $grouptypes = get_column('grouptype', 'name');
    }

    return $grouptypes;
}


/**
 * Returns a list of grouptype & jointype options to be used in create
 * group/edit group drop-downs.
 * 
 * If there is more than one group type with the same join type,
 * prefix the join types with the group type for display.
 */
function group_get_grouptype_options($currentgrouptype=null) {
    $groupoptions = array();
    $jointypecount = array('open' => 0, 'invite' => 0, 'request' => 0, 'controlled' => 0);
    $grouptypes = group_get_grouptypes();
    $enabled = array_map(create_function('$a', 'return $a->name;'), plugins_installed('grouptype'));
    if (is_null($currentgrouptype) || in_array($currentgrouptype, $enabled)) {
        $grouptypes = array_intersect($enabled, $grouptypes);
    }
    foreach ($grouptypes as $grouptype) {
        safe_require('grouptype', $grouptype);
        if (call_static_method('GroupType' . $grouptype, 'can_be_created_by_user')) {
            $grouptypename = get_string('name', 'grouptype.' . $grouptype);
            foreach (call_static_method('GroupType' . $grouptype, 'allowed_join_types') as $jointype) {
                $jointypecount[$jointype]++;
                $groupoptions['jointype']["$grouptype.$jointype"] = get_string('membershiptype.'.$jointype, 'group');
                $groupoptions['grouptype']["$grouptype.$jointype"] = $grouptypename . ': ' . get_string('membershiptype.'.$jointype, 'group');
            }
        }
    }
    $duplicates = array_reduce($jointypecount, create_function('$a, $b', 'return $a || $b > 1;'));
    if ($duplicates) {
        return $groupoptions['grouptype'];
    }
    return $groupoptions['jointype'];
}

/**
 * Returns a datastructure describing the tabs that appear on a group page
 *
 * @param object $group Database record of group to get tabs for
 * @return array
 */
function group_get_menu_tabs() {
    global $USER;
    static $menu;
	
    $group = group_current_group();
    if (!$group) {
        return null;
    }
	
	//Start-Anusha
	if($group->outcome){
		$groupmem = get_record('group_member','member',$USER->get('id'),'group',$group->id);
	}
	//End-Anusha
	
	//Start-Eshwari
	if($group->courseoutcome){
		$groupcoursemem = get_record('group_member','member',$USER->get('id'),'group',$group->id);
	}
	
	//End-Eshwari
	//Start-Eshwari
	
	if($group->courseoffering){
		$groupofferingmem = get_record('group_member','member',$USER->get('id'),'group',$group->id);
	}
	
	/*
    $menu = array(
        'info' => array(
            'path' => 'coursetemplate/info',
            'url' => 'coursetemplate/view.php?id='.$group->id,

            'title' => 'About',
            'weight' => 20
        ),
	);
	*/
	//End-Eshwari
	 $menu = array(
        'info' => array(
            'path' => 'groups/info',
            'url' => 'group/view.php?id='.$group->id,
            'title' => get_string('About', 'group'),


            'weight' => 20
        ),
	);
//Start-Anusha
	if($groupmem){
		if($groupmem->role != "member"){
			$menu['members'] = array(
				'path' => 'groups/members',
				'url' => 'group/members.php?id='.$group->id,
				'title' => get_string('Members', 'group'),
				'weight' => 30
			);	
		}
	}else{
//End-Anusha
		$menu['members'] = array(
				'path' => 'groups/members',
				'url' => 'group/members.php?id='.$group->id,
				'title' => get_string('Members', 'group'),
				'weight' => 30
		);
//Start-Anusha
	}
	//Start-Eshwari 
	
	if($groupcoursemem){
		if($groupcoursemem->role != "member"){
			$menu['members'] = array(
				'path' => 'groups/members',
				'url' => 'group/members.php?id='.$group->id,
				'title' => get_string('Members', 'group'),
				'weight' => 30
			);	
		}
	}else{

		$menu['members'] = array(

				'path' => 'groups/members',
				'url' => 'group/members.php?id='.$group->id,
				'title' => get_string('Members', 'group'),
				'weight' => 30
		);

	}
	
	if($groupofferingmem){
		if($groupofferingmem->role != "member"){
			$menu['members'] = array(
				'path' => 'groups/members',
				'url' => 'group/members.php?id='.$group->id,
				'title' => get_string('Members', 'group'),
				'weight' => 30
			);	
		}
	}else{

		$menu['members'] = array(

				'path' => 'groups/members',
				'url' => 'group/members.php?id='.$group->id,
				'title' => get_string('Members', 'group'),
				'weight' => 30
		);

	}
	
	
	//End-Eshwari
	/*
	//Start of subgroup logic by Shashank
	$menu['subgroups'] = array(
				'path' => 'coursetemplate/subgroups',
				'url' => 'coursetemplate/subgroups.php?id='.$group->id,
				'title' => 'Sub Template',
				'weight' => 35
		);
	//End of subgroup logic by Shashank
	*/
	//Start of subgroup logic by Shashank
	$menu['subgroups'] = array(
				'path' => 'groups/subgroups',
				'url' => 'group/subgroups.php?id='.$group->id,
				'title' => 'Sub Groups',
				'weight' => 35
		);
//End of subgroup logic by Shashank
//End-Anusha	
    if ($group->public || group_user_access($group->id)) {
        $menu['forums'] = array(  // @todo: get this from a function in the interaction plugin (or better, make forums an artefact plugin)
            'path' => 'groups/forums',
            'url' => 'interaction/forum/index.php?group='.$group->id,
            'title' => get_string('nameplural', 'interaction.forum'),
            'weight' => 40,
        );
    }
	/*
    $menu['views'] = array(
        'path' => 'coursetemplate/views',
        'url' => 'view/groupviews.php?group='.$group->id,

        'title' => 'Views',
        'weight' => 50,
    );
	*/
	 $menu['views'] = array(
        'path' => 'groups/views',
        'url' => 'view/groupviews.php?group='.$group->id,
        'title' => get_string('Views', 'group'),


        'weight' => 50,
    );
//Start-Anusha
if($group->outcome){
    $menu['outcome'] = array(
        'path' => 'groups/outcomes',
        'url' => 'group/groupoutcome.php?group='.$group->id,
        'title' => 'Outcome Results',
        'weight' => 60,
    );
}
//End-Anusha
//Start-Eshwari

if($group->courseoutcome){
    $menu['courseoutcome'] = array(
        'path' => 'groups/courseofferings',
        'url' => 'group/groupcourseoffering.php?group='.$group->id,
        'title' => 'Course offering Outcome Results',
        'weight' => 60,
    );
}
//End-Eshwari


    if (group_user_access($group->id)) {
        safe_require('grouptype', $group->grouptype);
        $artefactplugins = call_static_method('GroupType' . $group->grouptype, 'get_group_artefact_plugins');
        if ($plugins = get_records_array('artefact_installed', 'active', 1)) {
            foreach ($plugins as &$plugin) {
                if (!in_array($plugin->name, $artefactplugins)) {
                    continue;
                }
                safe_require('artefact', $plugin->name);
                $plugin_menu = call_static_method(generate_class_name('artefact',$plugin->name), 'group_tabs', $group->id);
                $menu = array_merge($menu, $plugin_menu);
            }
        }
    }

    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('groups/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }

    return $menu;
}
/*
//start -eshwari
function coursetemplate_get_menu_tabs() {
    global $USER;
    static $menu;
	
    $group = group_current_group();
    if (!$group) {
        return null;
    }
	
	
	//Start-Eshwari
	if($group->courseoutcome){
		$groupcoursemem = get_record('group_member','member',$USER->get('id'),'group',$group->id);
	}
	//End-Eshwari
	
    $menu = array(
        'info' => array(
            'path' => 'coursetemplate/info',
            'url' => 'coursetemplate/viewcourse.php?id='.$group->id,
            'title' => 'About',
            'weight' => 20
        ),
	);


	if($groupcoursemem){
		if($groupcoursemem->role != "member"){
			$menu['members'] = array(
				'path' => 'coursetemplate/members',
				'url' => 'coursetemplate/members.php?id='.$group->id,
				'title' => 'Members',
				'weight' => 30
			);	
		}
	}else{

		$menu['members'] = array(

				'path' => 'coursetemplate/members',
				'url' => 'coursetemplate/members.php?id='.$group->id,
				'title' => 'Members',
				'weight' => 30
		);

	}
	
		//Start of subgroup logic by Shashank
	$menu['subgroups'] = array(
				'path' => 'coursetemplate/subgroups',
				'url' => 'coursetemplate/subgroups.php?id='.$group->id,
				'title' => 'Sub Template',
				'weight' => 35
		);
	//End of subgroup logic by Shashank
//End-Anusha	
    if ($group->public || group_user_access($group->id)) {
        $menu['forums'] = array(  // @todo: get this from a function in the interaction plugin (or better, make forums an artefact plugin)
            'path' => 'groups/forums',
            'url' => 'interaction/forum/index.php?group='.$group->id,
            'title' => get_string('nameplural', 'interaction.forum'),
            'weight' => 40,
        );
    }
    $menu['views'] = array(
        'path' => 'coursetemplate/views',
        'url' => 'view/groupviews.php?group='.$group->id,
        'title' => get_string('Views', 'group'),
        'weight' => 50,
    );

//Start-Eshwari
if($group->courseoutcome){
    $menu['courseoutcome'] = array(
        'path' => 'coursetemplate/courseoutcomes',
        'url' => 'coursetemplate/groupcourseoutcome.php?group='.$group->id,
        'title' => 'Course Outcome Results',
        'weight' => 60,
    );
}
//End-Eshwari

    if (group_user_access($group->id)) {
        safe_require('grouptype', $group->grouptype);
        $artefactplugins = call_static_method('GroupType' . $group->grouptype, 'get_group_artefact_plugins');
        if ($plugins = get_records_array('artefact_installed', 'active', 1)) {
            foreach ($plugins as &$plugin) {
                if (!in_array($plugin->name, $artefactplugins)) {
                    continue;
                }
                safe_require('artefact', $plugin->name);
                $plugin_menu = call_static_method(generate_class_name('artefact',$plugin->name), 'group_tabs', $group->id);
                $menu = array_merge($menu, $plugin_menu);
            }
        }
    }

    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('groups/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }

    return $menu;
}
*/
//end -eshwari
/**
 * Used by this file to perform validation of group ID function arguments
 *
 * @param int $groupid
 * @return int
 * @throws InvalidArgumentException
 */
function group_param_groupid($groupid) {
    $groupid = (int)$groupid;

    if ($groupid == 0) {
        throw new InvalidArgumentException("group_user_access: group argument should be an integer");
    }

    return $groupid;
}

/**
 * Used by this file to perform validation of user ID function arguments
 *
 * @param int $userid
 * @return int
 * @throws InvalidArgumentException
 */
function group_param_userid($userid) {
    if (is_null($userid)) {
        global $USER;
        $userid = (int)$USER->get('id');
    }
    else {
        $userid = (int)$userid;
    }

    if ($userid == 0) {
        throw new InvalidArgumentException("group_user_access: user argument should be an integer");
    }

    return $userid;
}


function get_subgroups($limit=20, $offset=0, $groupid) {
	if(!$groupid){
	$groupid = 0;
    }    
    $sql = 'SELECT *
		    FROM {group}
		    WHERE parent_group = ?		    
		    ORDER BY name';
	$values = array($groupid);    	
    
    $subgroups = get_records_sql_assoc($sql, $values, $offset, $limit);
	
    
	$count = count_records_sql('SELECT COUNT(*) FROM {group} WHERE parent_group = ?', $values);

    if (!$subgroups) {
        $subgroups = array();		
    }
	
	return array('subgroups' => $subgroups, 'count' => $count);

}


function can_create_subgroups() {
    global $USER;
    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        return true;
    }
    return false;
}

function group_current_group() {
    static $group;

    // This function sometimes gets called by the smarty function
    // during the execution of a GroupNotFound exception.  This
    // variable prevents a 2nd exception from being thrown.  Perhaps
    // better achieved with a global in the exception handler?
    static $dying;

    if (defined('GROUP') && !$dying) {
        $group = get_record_select('group', 'id = ? AND deleted = 0', array(GROUP), '*, ' . db_format_tsfield('ctime'));
        if (!$group) {
            $dying = 1;
            throw new GroupNotFoundException(get_string('groupnotfound', 'group', GROUP));
        }
    }
    else {
        $group = null;
    }

    return $group;
}


/**
 * creates the group sideblock
 */
function group_sideblock() {
    require_once('group.php');
    $data['group'] = group_current_group();
    if (!$data['group']) {
        return null;
    }
    $data['menu'] = group_get_menu_tabs();
    // @todo either: remove this if interactions become group
    // artefacts, or: do this in interaction/lib.php if we leave them
    // as interactions
    $data['forums'] = get_records_select_array(
        'interaction_instance',
        '"group" = ? AND deleted = ? AND plugin = ?',
        array(GROUP, 0, 'forum'),
        'ctime',
        'id, plugin, title'
    );
    if (!$data['forums']) {
        $data['forums'] = array();
    }
    else {
        safe_require('interaction', 'forum');
        $data['forums'] = PluginInteractionForum::sideblock_sort($data['forums']);
    }
    return $data;
}


function group_get_associated_groups($userid, $filter='all', $limit=20, $offset=0) {

    // Strangely, casting is only needed for invite, request and admin and only in 
    // postgres
    if (is_mysql()) {
        $invitesql  = "'invite'";
        $requestsql = "'request'";
        $adminsql   = "'admin'";
        $empty      = "''";
    }
    else {
        $invitesql  = "CAST('invite' AS TEXT)";
        $requestsql = "CAST('request' AS TEXT)";
        $adminsql   = "CAST('admin' AS TEXT)";
        $empty      = "CAST('' AS TEXT)";
    }

    // Different filters join on the different kinds of association
    if ($filter == 'admin') {
        $sql = "
            INNER JOIN (
                SELECT g.id, $adminsql AS membershiptype, $empty AS reason, $adminsql AS role
                FROM {group} g				
                INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
            ) t ON t.id = g.id";
        $values = array($userid);
    }
    else if ($filter == 'member') {
        $sql = "
            INNER JOIN (
                SELECT g.id, 'admin' AS membershiptype, $empty AS reason, $adminsql AS role
                FROM {group} g				
                INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
                UNION
                SELECT g.id, 'member' AS type, $empty AS reason, gm.role AS role
                FROM {group} g
                INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role != 'admin')
            ) t ON t.id = g.id";
        $values = array($userid, $userid);
    }
    else if ($filter == 'invite') {
        $sql = "
            INNER JOIN (
                SELECT g.id, $invitesql AS membershiptype, gmi.reason, gmi.role
                FROM {group} g
                INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
            ) t ON t.id = g.id";
        $values = array($userid);
    }
    else if ($filter == 'request') {
        $sql = "
            INNER JOIN (
                SELECT g.id, $requestsql AS membershiptype, gmr.reason, $empty AS role
                FROM {group} g
                INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
            ) t ON t.id = g.id";
        $values = array($userid);
    }
    else { // all or some other text
        $filter = 'all';
        $sql = "
            INNER JOIN (
                SELECT g.id, 'admin' AS membershiptype, '' AS reason, 'admin' AS role
                FROM {group} g				
                INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin' AND g.parent_group is null OR g.parent_group=0)
                UNION
                SELECT g.id, 'member' AS membershiptype, '' AS reason, gm.role AS role
                FROM {group} g
                INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ? AND gm.role != 'admin')
                UNION
                SELECT g.id, 'invite' AS membershiptype, gmi.reason, gmi.role
                FROM {group} g
                INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
                UNION SELECT g.id, 'request' AS membershiptype, gmr.reason, '' AS role
                FROM {group} g
                INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
            ) t ON t.id = g.id";
        $values = array($userid, $userid, $userid, $userid);
    }
    
    $values[] = 0;
    
    $count = count_records_sql('SELECT COUNT(*) FROM {group} g ' . $sql . ' WHERE g.deleted = ?', $values);
    
    // almost the same as query used in find - common parts should probably be pulled out
    // gets the groups filtered by above
    // and the first three members by id
    
	//Start-Anusha - added g.outcome & g1.outcome
    /*$sql = 'SELECT g1.id, g1.name, g1.description, g1.jointype, g1.grouptype, g1.membershiptype, g1.reason, g1.role, g1.membercount, COUNT(gmr.member) AS requests
        FROM (
        SELECT g.id, g.name, g.description, g.jointype, g.grouptype, t.membershiptype, t.reason, t.role, COUNT(gm.member) AS membercount
            FROM {group} g
            LEFT JOIN {group_member} gm ON (gm.group = g.id)' .
            $sql . '
            WHERE g.deleted = ?
            GROUP BY g.id, g.name, g.description, g.jointype, g.grouptype, t.membershiptype, t.reason, t.role
            ORDER BY g.name
        ) g1
        LEFT JOIN {group_member_request} gmr ON (gmr.group = g1.id)
        GROUP BY g1.id, g1.name, g1.description, g1.jointype, g1.grouptype, g1.membershiptype, g1.reason, g1.role, g1.membercount';
    */
	//start -Eshwari added g1.courseoffering,g.courseoffering,
	    $sql = 'SELECT g1.id, g1.name, g1.description, g1.jointype, g1.grouptype, g1.outcome,g1.courseoffering,  g1.membershiptype, g1.reason, g1.role, g1.membercount, COUNT(gmr.member) AS requests
        FROM (
        SELECT g.id, g.name, g.description, g.jointype, g.grouptype, g.outcome,g.courseoffering, t.membershiptype, t.reason, t.role, COUNT(gm.member) AS membercount
            FROM {group} g
            LEFT JOIN {group_member} gm ON (gm.group = g.id)' .
            $sql . '
            WHERE g.deleted = ?
            GROUP BY g.id, g.name, g.description, g.jointype, g.grouptype, t.membershiptype, t.reason, t.role
            ORDER BY g.name
        ) g1
        LEFT JOIN {group_member_request} gmr ON (gmr.group = g1.id)
        GROUP BY g1.id, g1.name, g1.description, g1.jointype, g1.grouptype, g1.outcome,g1.courseoffering, g1.membershiptype, g1.reason, g1.role, g1.membercount';
	//End-Anusha
	
    $groups = get_records_sql_assoc($sql, $values, $offset, $limit);
    
    if ($groups) {
        // Get a few random members from each group. We've tried this with one 
        // query before but it's painfully slow, databases don't do random rows 
        // efficiently.
        foreach (array_keys($groups) as $groupid) {
            $members = get_records_sql_array("
                SELECT u.*
                FROM {group_member} gm
                INNER JOIN {usr} u ON (gm.member = u.id AND u.deleted = 0)
                WHERE gm.group = ?
                ORDER BY " . db_random() . "
                LIMIT 3", array($groupid));
            foreach ($members as $m) {
                $groups[$groupid]->members[] = (object) array('id' => $m->id, 'name' => display_name($m));
            }
        }
        $groups = array_values($groups);
    }
    else {
        $groups = array();
    }

    return array('groups' => $groups, 'count' => $count);

}



function group_get_user_groups($userid=null, $roles=null) {
    if (is_null($userid)) {
        global $USER;
        $userid = $USER->get('id');
    }
    if ($groups = get_records_sql_array(
        "SELECT g.id, g.name, gm.role, g.jointype, g.grouptype
        FROM {group} g
        JOIN {group_member} gm ON (gm.group = g.id)
        WHERE gm.member = ?
        AND g.deleted = 0 " . (is_array($roles) ? (' AND gm.role IN (' . join(',', array_map('db_quote', $roles)) . ')') : '') . "
        ORDER BY gm.role = 'admin' DESC, gm.role, g.id", array($userid))) {
        return $groups;
    }
    return array();
}


function group_get_member_ids($group, $roles=null) {
    $rolesql = is_null($roles) ? '' : (' AND gm.role IN (' . join(',', array_map('db_quote', $roles)) . ')');
    return get_column_sql('
        SELECT gm.member
        FROM {group_member} gm INNER JOIN {group} g ON gm.group = g.id
        WHERE g.deleted = 0 AND g.id = ?' . $rolesql,
        array($group)
    );
}

function group_can_create_groups() {
    global $USER;
    $creators = get_config('creategroups');
    if ($creators == 'all') {
        return true;
    }
    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        return true;
    }
    return $creators == 'staff' && ($USER->get('staff') || $USER->is_institutional_staff());
}
//start-Eshwari
function group_can_create_coursegroups() {
    global $USER;
    $creators = get_config('creategroups');
    if ($creators == 'all') {
        return true;
    }
    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        return true;
    }
    return $creators == 'staff' && ($USER->get('staff') || $USER->is_institutional_staff());
}
//end
?>
