<?php

define('AJAX_SCRIPT', true);

require('../../../../config.php');
global $DB,$USER;

// Get parameters
$instanceid = required_param('instance',PARAM_INT);
$moduleid = required_param('modid',PARAM_INT);

// Get mod data
$mod = $DB->get_record('course_modules',array('module'=>$moduleid,'instance'=>$instanceid));

if(!$record = $DB->get_record('format_khanesque',array('courseid'=>$mod->course,'userid'=>$USER->id,'modid'=>$mod->id))){
  $dataobject = new stdClass;
  $dataobject->courseid = $mod->course;
  $dataobject->userid = $USER->id;
  $dataobject->modid = $mod->id;
  $dataobject->added = 0;
  $DB->insert_record('format_khanesque',$dataobject);
} else{
    $record->added = 0;
    $DB->update_record('format_khanesque', $record);
}
