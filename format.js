    $(".flexdates-report-progress-cell").click(function(){
        var data = $(this).attr('id').split('-');
        console.log(data);
        var module = data[0];
        var instance = data[1];
        if($('#khanesque-'+module+'-'+instance).length){
            var $this = $('#khanesque-'+module+'-'+instance)
            $this.removeClass('khanesquefadeout')
            .addClass('khanesquefadein');
            var tgl = function($this){
                $this.removeClass('khanesquefadein')
                .addClass('khanesquefadeout');
            }
            setTimeout(tgl,500,$this);
        }
        else{
            $.ajax({
                type:"POST",
                dataType:"html",
                url:'/moodle/course/format/khanesque/ajax/addtask.php?instance='+instance+'&modid='+module,
                success: function(msg){
                    $('.khanesque-upnext-outer-container').prepend(msg);
                }
            });
            
            $('#khanesque-'+module+'-'+instance)
        }
    });

    $(".khanesque-upnext-outer-container").on("click",".khanesque-clicktoremove",function(){
        var container = $(this).closest(".khanesque-upnext-container");
        console.log(container);
        var data = container.attr('id').split('-');
        var module = data[1];
        var instance = data[2];
        
        $.ajax({
            type:"POST",
            dataType:"html",
            url:'/moodle/course/format/khanesque/ajax/removetask.php?instance='+instance+'&modid='+module,
            success: function(msg){
                
            }
        })
        
        container.remove();
    });
    
    function khanesqueGrabFrame(url,id,nodeid){
        $.ajax({
            url: url,
            success: function(html) {
                var content = $('<div />').html(html).find("[role='main']");
                $('#khanesque-content-area-'+id).html(content);
            }
        });
        $('.khanesque-modal-skillnode').removeClass('khanesque-modal-skillnode-highlight');
        $('#'+nodeid).addClass('khanesque-modal-skillnode-highlight');
    }


// Javascript functions for Topics course format

M.course = M.course || {};

M.course.format = M.course.format || {};

/**
 * Get sections config for this format
 *
 * The section structure is:
 * <ul class="topics">
 *  <li class="section">...</li>
 *  <li class="section">...</li>
 *   ...
 * </ul>
 *
 * @return {object} section list configuration
 */
M.course.format.get_config = function() {
    return {
        container_node : 'ul',
        container_class : 'topics',
        section_node : 'li',
        section_class : 'section'
    };
}

/**
 * Swap section
 *
 * @param {YUI} Y YUI3 instance
 * @param {string} node1 node to swap to
 * @param {string} node2 node to swap with
 * @return {NodeList} section list
 */
M.course.format.swap_sections = function(Y, node1, node2) {
    var CSS = {
        COURSECONTENT : 'course-content',
        SECTIONADDMENUS : 'section_add_menus'
    };

    var sectionlist = Y.Node.all('.'+CSS.COURSECONTENT+' '+M.course.format.get_section_selector(Y));
    // Swap menus.
    sectionlist.item(node1).one('.'+CSS.SECTIONADDMENUS).swap(sectionlist.item(node2).one('.'+CSS.SECTIONADDMENUS));
}

/**
 * Process sections after ajax response
 *
 * @param {YUI} Y YUI3 instance
 * @param {array} response ajax response
 * @param {string} sectionfrom first affected section
 * @param {string} sectionto last affected section
 * @return void
 */
M.course.format.process_sections = function(Y, sectionlist, response, sectionfrom, sectionto) {
    var CSS = {
        SECTIONNAME : 'sectionname'
    },
    SELECTORS = {
        SECTIONLEFTSIDE : '.left .section-handle img'
    };

    if (response.action == 'move') {
        // If moving up swap around 'sectionfrom' and 'sectionto' so the that loop operates.
        if (sectionfrom > sectionto) {
            var temp = sectionto;
            sectionto = sectionfrom;
            sectionfrom = temp;
        }

        // Update titles and move icons in all affected sections.
        var ele, str, stridx, newstr;

        for (var i = sectionfrom; i <= sectionto; i++) {
            // Update section title.
            sectionlist.item(i).one('.'+CSS.SECTIONNAME).setContent(response.sectiontitles[i]);
            // Update move icon.
            ele = sectionlist.item(i).one(SELECTORS.SECTIONLEFTSIDE);
            str = ele.getAttribute('alt');
            stridx = str.lastIndexOf(' ');
            newstr = str.substr(0, stridx +1) + i;
            ele.setAttribute('alt', newstr);
            ele.setAttribute('title', newstr); // For FireFox as 'alt' is not refreshed.
        }
    }
}
