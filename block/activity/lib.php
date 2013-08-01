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
 * @subpackage blocktype-activtity
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
defined('INTERNAL') || die();
include('/lib/view.php');
class PluginBlocktypeActivity extends SystemBlocktype {
/*
//added by to restrict to one
   public static function single_only() {
        return true;
    }
	*/

    public static function get_title() {
        return get_string('title', 'blocktype.activity');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.activity');
    }

    public static function get_categories() {
        return array('activity');
    }

 public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        $text = (isset($configdata['text'])) ? $configdata['text'] : '';
		$texttitle = (isset($configdata['texttitle'])) ? $configdata['texttitle'] : '';
		
		$activtitytype = (isset($configdata['activtitytype'])) ? $configdata['activtitytype'] : '';	
		/*$activtitytype_selected = get_record_sql("SELECT id,activtity_name
						  FROM {course_activities} 
						  WHERE id =?",array($configdata['activtitytype']));
						  */
        if(isset($configdata['criteria'])) {
             if($configdata['criteria']==0)
					$criteria = 'Individual';
				if($configdata['criteria']==1)
					$criteria = 'Group';
		}
		
		safe_require('artefact', 'file');
        $text = ArtefactTypeFolder::append_view_url($text,$instance->get('view'));
		$texttitle = ArtefactTypeFolder::append_view_url($texttitle,$instance->get('view'));
        $html = '<h3>Activity name</h3>'.$texttitle.'<h3>Activtity type</h3>'.$criteria.'<h3>Description</h3>'.$text;	
  return clean_html($html);
  //return clean_html($text);
   }

/**
     * Returns a list of artefact IDs that are in this blockinstance.
     *
     * People may embed artefacts as images etc. They show up as links to the 
     * download script, which isn't much to go on, but should be enough for us 
     * to detect that the artefacts are therefore 'in' this blocktype.
     */
    public static function get_artefacts(BlockInstance $instance) {
        $artefacts = array();
        $configdata = $instance->get('configdata');
        if (isset($configdata['text'])) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            $artefacts = artefact_get_references_in_html($configdata['text']);
        }
        return $artefacts;
    }
   public static function has_instance_config() {
        return true;
    }

 public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
		$activity_types= array();
		
		if (true){
		$activitytype[0] = array(
			'value'    => 'Individual Activity',
			'disabled' => false,
			);
			$activitytype[1] = array(
			'value'    => 'Group Activity',
			'disabled' => false,
			);
		}
		
		
        return array(
		
		'texttitle' => array(
                'type' => 'textarea',
                'title' => 'Title',
                'width' => '70%',
                'height' => '15px',
                'defaultvalue' => isset($configdata['texttitle']) ? $configdata['texttitle'] : '',
            ),
		 
		 'criteria' => array(
                        'type'    => 'select',
                        'title'   => 'Activity type',
                        'collapseifoneoption' => false,
                        'options' => $activitytype,
                        //'defaultvalue' => $default,
                    ),
					
            'text' => array(
                'type' => 'wysiwyg',
                'title' => get_string('blockcontent', 'blocktype.activity'),
                'width' => '90%',
                'height' => '150px',
                'defaultvalue' => isset($configdata['text']) ? $configdata['text'] : '',
            ),
        );
    }

    public static function default_copy_type() {
        return 'full';
    }

}























?>























