<?php

define('AJAX_SCRIPT', true);

require('../../../../config.php');
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/grade_grade.php');
require_once($CFG->dirroot.'/course/format/khanesque/lib.php');

global $USER;

// Get parameters
$course = required_param('course',PARAM_INT);
$section = required_param('section',PARAM_INT);
$indent = required_param('indent',PARAM_INT);
$instanceid = required_param('instance',PARAM_INT);
$moduleid = required_param('modid',PARAM_INT);

$modinfo = get_fast_modinfo($course);
$cms = $modinfo->cms;
$section_data = $modinfo->sections[$section];

$gradeitems = khanesque_get_student_grades($course, $USER->id);

$grades = array();
foreach($gradeitems as $gradeitem){
    if($gradeitem->itemtype == 'course'){
        continue;
    }
    if(array_key_exists($gradeitem->itemmodule,$grades)){
        $grades[$gradeitem->itemmodule][$gradeitem->iteminstance]=$gradeitem;
    } else{
        $grades[$gradeitem->itemmodule] = array();
        $grades[$gradeitem->itemmodule][$gradeitem->iteminstance]=$gradeitem;
    }

}
$title = 'Activity Group';
$grouped_cms = array();
foreach($section_data as $key=>$cmid){
    $cm = $cms[$cmid];

    if($cm->indent==$indent){
        if($cm->module == $moduleid and $cm->instance == $instanceid){
            $title = $cm->name;
        }
        $o = new stdClass;
        $o->id = $cm->id;
        $o->name = $cm->name;
        $o->modname = $cm->modname;
        $o->module = $cm->module;
        $o->instance = $cm->instance;
        $o->url = $cm->url;
        $grouped_cms[] = $o;
    }
}

$out ='
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
  <h4 class="modal-title" id="tasks-modal-title">'.$title.'</h4>
</div>
<div class="modal-body">
  <div class="container-fluid">
    <div class="row">
      <div class="khanesque-sidebar col-md-3" style="position:absolute;">'."\n";
        foreach($grouped_cms as $cm){
            if(!array_key_exists($cm->modname,$grades)){
                $glyphcolor = '#DDD';
            } else if(!array_key_exists($cm->instance,$grades[$cm->modname])){
                $glyphcolor = '#DDD';
            } else{
                $glyphcolor = $grades[$cm->modname][$cm->instance]->grades->level;
            }
            $nodeid = 'khanesque-modal-'.$cm->module.'-'.$cm->instance;
            $out.='<div style="min-height:60px;"><div class="khanesque-modal-skillnode" id="'.$nodeid.'" onClick="khanesqueGrabFrame(\''.$cm->url.'\',\''.$nodeid.'\')">
            <div class="khanesque-skill-glyph" style="background-color:'.$glyphcolor.';">';
            if(!array_key_exists($cm->modname,$grades)){
                  $glyph = 'glyphicon glyphicon-file';
              } else{
                  $glyph = 'glyphicon glyphicon-inverse glyphicon-star-empty';
              }
            $out.='<span class="'.$glyph.'" style="color:white;font-size:40px;line-height:50px;"></span></div>
            <div class="khanesque-modal-skilltitle">'.$cm->name.'</div></div>
            </div><div class="clearfix"></div>';
        }
        $out.='
      </div>
      <div class="col-sm-9 col-sm-offset-3 col-md-9 col-md-offset-3 main">
        <div id="khanesque-content-area" style="min-height:600px"></div>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>';

echo $out;
