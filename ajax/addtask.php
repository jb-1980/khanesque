<?php

define('AJAX_SCRIPT', true);

require('../../../../config.php');
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/grade_grade.php');
global $DB,$USER;

// Get parameters
$instanceid = required_param('instance',PARAM_INT);
$moduleid = required_param('modid',PARAM_INT);

// Get mod data
$mod = $DB->get_record('course_modules',array('module'=>$moduleid,'instance'=>$instanceid));

$cms = get_fast_modinfo($mod->course)->cms;
$cm = $cms[$mod->id];
$section = $mod->section;
$indent = $mod->indent;
$nodeid = 'khanesque-modal-'.$mod->module.'-'.$mod->instance;

$grade_item = grade_item::fetch(array('itemmodule'=>$cm->modname,'iteminstance'=>$instanceid));
$grade_grades = grade_grade::fetch_users_grades($grade_item, array($USER->id), true);
$levels = $DB->get_record('local_fd_trackcourse',array('courseid'=>$cm->course));
$level = $grade_grades[$USER->id]->finalgrade / $grade_item->grademax;
$o = new stdClass;
$o->finalgrade = $grade_grades[$USER->id]->finalgrade;
$o->grademax = $grade_item->grademax;
$o->level = $level;
$o->levels = $levels;
//print_object($o);
#print_object($grade_item);
#print_object($grade_grades);
if($level == null){
    $glyphcolor = '#DDD';
} elseif($level >= $levels->mastered){
    $glyphcolor = '#1C758A';
} elseif($level >= $levels->level2){
    $glyphcolor = '#29ABCA';
} elseif($level >= $levels->level1){
    $glyphcolor = '#58C4DD';
} elseif($level >= $levels->practiced){
    $glyphcolor = '#9CDCEB';
} else{
    $glyphcolor = '#C30202';
}
//print_object($cm->course);
// Create mod container
#$added= '<div class="khanesque-upnext-container" id="khanesque-'.$mod->module.'-'.$mod->instance.'">
#                                            
#                                            
#              <div class="khanesque-skill-glyph" style="background-color:'.$glyphcolor.';" data-toggle="modal" data-target="#'.$id.'" onClick="khanesqueGrabFrame(\''.$cm->url.'\',\''.$id.'\',\''.$nodeid.'\')">
#                <span class="glyphicon glyphicon-inverse glyphicon-star-empty" style="color:white;font-size:40px;line-height:50px;"></span>
#              </div>
#              <div style="display:inline-block;padding-left:85px;">
#                <div class="khanesque-youadded" data-toggle="modal" data-target="#'.$id.'" onClick="khanesqueGrabFrame(\''.$cm->url.'\',\''.$id.'\',\''.$nodeid.'\')">You added</div>
#                <div class="khanesque-mod-title" data-toggle="modal" data-target="#'.$id.'" onClick="khanesqueGrabFrame(\''.$cm->url.'\',\''.$id.'\',\''.$nodeid.'\')">'.$cm->name.'</div>
#                <div class="khanesque-clicktoremove">Click to remove</div>
#              </div>
#            
#              
#            
#          </div>';

$added= '<div class="khanesque-upnext-container" id="khanesque-'.$mod->module.'-'.$mod->instance.'">
  <div class="khanesque-skill-glyph"
    style="background-color:'.$glyphcolor.';"
    data-toggle="modal"
    data-target="#tasks-modal"
    data-cm="'.$mod->course.'$'.$section.'$'.$indent.'$'.$mod->url.'$'.$nodeid.'$'.$mod->module.'$'.$mod->instance.'"
  >
    <span class="glyphicon glyphicon-inverse glyphicon-star-empty" style="color:white;font-size:40px;line-height:50px;"></span>
  </div>
  <div style="display:inline-block;padding-left:85px;">
    <div class="khanesque-youadded"
      data-toggle="modal"
      data-target="#tasks-modal"
      data-cm="'.$mod->course.'$'.$section.'$'.$indent.'$'.$mod->url.'$'.$nodeid.'$'.$mod->module.'$'.$mod->instance.'"
    >
      You added
    </div>
    <div class="khanesque-mod-title"
     data-toggle="modal" 
     data-target="#tasks-modal"
     data-cm="'.$mod->course.'$'.$section.'$'.$indent.'$'.$mod->url.'$'.$nodeid.'$'.$mod->module.'$'.$mod->instance.'"
    >'.$cm->name.'</div>
    <div class="khanesque-clicktoremove">Click to remove</div>
  </div>
</div>';

// Send back mod html
echo $added;

if(!$record = $DB->get_record('format_khanesque',array('courseid'=>$mod->course,'userid'=>$USER->id,'modid'=>$mod->id))){
  $dataobject = new stdClass;
  $dataobject->courseid = $mod->course;
  $dataobject->userid = $USER->id;
  $dataobject->modid = $mod->id;
  $dataobject->added = 1;
  $DB->insert_record('format_khanesque',$dataobject);
} else{
    $record->added = 1;
    $DB->update_record('format_khanesque', $record);
}
