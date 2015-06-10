<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Renderer for outputting the topics course format.
 *
 * @package format_khanesque
 * @copyright 2015 Joseph Gilgen, <gilgenlabs@gmail.com>>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.9
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');
require_once($CFG->dirroot.'/course/format/khanesque/lib.php');

/**
 * Basic renderer for khanesque format.
 *
 * @copyright 2015 Joseph Gilgen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_khanesque_renderer extends format_section_renderer_base {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_topics_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }
    protected function print_modal($title,$id,$cms,$grades){
        $out = '<!-- Modal -->
<div class="modal fade" id="'.$id.'" tabindex="-1" role="dialog" aria-labelledby="'.$id.'-Label" aria-hidden="true">
  <div class="modal-dialog" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="'.$id.'-Label">'.$title.'</h4>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="khanesque-sidebar col-md-3" style="position:absolute;">
             '."\n";
              foreach($cms as $cm){
                  if(!array_key_exists($cm->modname,$grades)){
                      $glyphcolor = '#DDD';
                  } else if(!array_key_exists($cm->instance,$grades[$cm->modname])){
                      $glyphcolor = '#DDD';
                  } else{
                      $glyphcolor = $grades[$cm->modname][$cm->instance]->grades->level;
                  }
                  $nodeid = 'khanesque-modal-'.$cm->module.'-'.$cm->instance;
                  $out.='<div style="min-height:60px;"><div class="khanesque-modal-skillnode" id="'.$nodeid.'" onClick="khanesqueGrabFrame(\''.$cm->url.'\',\''.$id.'\',\''.$nodeid.'\')">
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
              <div id="khanesque-content-area-'.$id.'" style="min-height:600px"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div><!-- end Modal -->';
        return $out;
        
    }
    public function print_stuff($course,$userid){
        global $DB;
        
        //print_object($course);
        $modinfo = get_fast_modinfo($course);
        //print_object($modinfo);
        $cms = $modinfo->cms;
        $sections = $modinfo->sections;
        //print_object($sections);
        //print_object($cms);
        
        $gradeitems = khanesque_get_student_grades($course->id, $userid);
        //print_object($gradeitems);
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
        //print_object($grades);
        $grouped_cms = array();
        foreach($sections as $sid=>$section){
            $grouped_cms[$sid]=array();
            foreach($section as $key=>$cmid){
                $cm = $cms[$cmid];
                //print_object($cm);
                if(array_key_exists($cm->indent,$grouped_cms[$sid])){
                    $o = new stdClass;
                    $o->id = $cm->id;
                    $o->name = $cm->name;
                    $o->modname = $cm->modname;
                    $o->module = $cm->module;
                    $o->instance = $cm->instance;
                    $o->url = $cm->url;
                    $grouped_cms[$sid][$cm->indent][] = $o;
                } else{
                    $grouped_cms[$sid][$cm->indent] = array();
                    $o = new stdClass;
                    $o->id = $cm->id;
                    $o->name = $cm->name;
                    $o->modname = $cm->modname;
                    $o->module = $cm->module;
                    $o->instance = $cm->instance;
                    $o->url = $cm->url;
                    $grouped_cms[$sid][$cm->indent][] = $o;
                }
            }
        }
        //print_object($grouped_cms);
        $out='';
        foreach ($grouped_cms as $section => $groups){
            foreach($groups as $indent=>$group){
                $title = $group[0]->name;
                $id = 'modal-'.($section+1).'-'.$indent;
                
                $out.= $this->print_modal($title,$id,$group,$grades);
            }
        }
        
        $user_added_mods = $DB->get_records('format_khanesque',array('courseid'=>$course->id,'userid'=>$userid),'modid','modid,added');
        //print_object($user_added_mods);
        $out.= '
  <div class="khanesque-upnext-outer-container">'."\n";
        $added = '';
        $upcoming = '';
        
        $max = 0;
        foreach ($grouped_cms as $section => $groups){
            foreach($groups as $indent=>$group){
                $id = 'modal-'.($section+1).'-'.$indent;
                foreach($group as $key=>$mod){
                    if(!array_key_exists($mod->modname,$grades)){
                        continue;
                    }
                    if(!array_key_exists($mod->instance,$grades[$mod->modname])){
                        continue;
                    }
                    if($max > $course->maxitems){break;}
                    $nodeid = 'khanesque-modal-'.$mod->module.'-'.$mod->instance;
                    $glyphcolor = $grades[$mod->modname][$mod->instance]->grades->level;
                    if($grades[$mod->modname][$mod->instance]->grades->grade
                       or $grades[$mod->modname][$mod->instance]->grades->hidden
                       or $grades[$mod->modname][$mod->instance]->grades->excluded){
                        
                        if(array_key_exists($mod->id,$user_added_mods)){
                            if($user_added_mods[$mod->id]->added){
                                
                                $added.= '<div class="khanesque-upnext-container" id="khanesque-'.$mod->module.'-'.$mod->instance.'">
                                            
                                            
                                              <div class="khanesque-skill-glyph" style="background-color:'.$glyphcolor.';" data-toggle="modal" data-target="#'.$id.'" onClick="khanesqueGrabFrame(\''.$mod->url.'\',\''.$id.'\',\''.$nodeid.'\')">
                                                <span class="glyphicon glyphicon-inverse glyphicon-star-empty" style="color:white;font-size:40px;line-height:50px;"></span>
                                              </div>
                                              <div style="display:inline-block;padding-left:85px;">
                                                <div class="khanesque-youadded" data-toggle="modal" data-target="#'.$id.'" onClick="khanesqueGrabFrame(\''.$mod->url.'\',\''.$id.'\',\''.$nodeid.'\')">You added</div>
                                                <div class="khanesque-mod-title" data-toggle="modal" data-target="#'.$id.'" onClick="khanesqueGrabFrame(\''.$mod->url.'\',\''.$id.'\',\''.$nodeid.'\')">'.$mod->name.'</div>
                                                <div class="khanesque-clicktoremove">Click to remove</div>
                                              </div>
                                            
                                              
                                            
                                          </div>';
                                $max ++;
                            }
                        }
                        continue;
                    }
                    //print_object($mod);
                    $upcoming.= '<div class="khanesque-upnext-container" id="khanesque-'.$mod->module.'-'.$mod->instance.'" data-toggle="modal" data-target="#'.$id.'" onClick="khanesqueGrabFrame(\''.$mod->url.'\',\''.$id.'\',\''.$nodeid.'\')">'."\n";
                    $upcoming.= '        <div class="khanesque-skill-glyph" style="background-color:'.$glyphcolor.';">';
                    $upcoming.= '          <span class="glyphicon glyphicon-inverse glyphicon-star-empty" style="color:white;font-size:40px;line-height:50px;"></span>'."\n";
                    $upcoming.= "        </div>";
                    $upcoming.= "        <div style='display:inline-block;padding-left:80px;position:absolute;top:50%;transform:translateY(-50%);'>\n";
                    $upcoming.= '          <div class="khanesque-mod-title">'.$mod->name.'</div>'."\n";
                    $upcoming.= "        </div>
                                 </div>\n";
                    $max ++;
                }
                
            }
        }
        $out.=$added.$upcoming.'</div>'."\n";
        
        return $out;
    }
    
    private function get_glyph_color($masterylevel){
        
    }
    
    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'topics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_controls($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $isstealth = $section->section > $course->numsections;
        $controls = array();
        if (!$isstealth && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $controls[] = html_writer::link($url,
                                    html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),
                                        'class' => 'icon ', 'alt' => get_string('markedthistopic'))),
                                    array('title' => get_string('markedthistopic'), 'class' => 'editing_highlight'));
            } else {
                $url->param('marker', $section->section);
                $controls[] = html_writer::link($url,
                                html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),
                                    'class' => 'icon', 'alt' => get_string('markthistopic'))),
                                array('title' => get_string('markthistopic'), 'class' => 'editing_highlight'));
            }
        }

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
    }
}
