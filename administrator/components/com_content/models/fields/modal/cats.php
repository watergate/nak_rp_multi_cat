<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Supports a modal article picker.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class JFormFieldModal_Cats extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Modal_Cats';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{  
    // set uncategorized for new articles
    if(!isset($_GET['id']) && !$this->value){
      $db	= JFactory::getDBO();
      $db->setQuery(
  			'SELECT id,title' .
  			' FROM #__categories' .
  			' WHERE extension = "com_content"'.
        ' ORDER BY id ASC'
  		,0,1);
      $res = $db->loadObject();
      $this->value = $res->id;      
    }      
    
    $params		= JComponentHelper::getParams('com_multicats');
	  $layout	= $params->get('layout','chosen'); 
    if($layout == 'standard'){ 
      $html = self::loadInputTypeStandard();
    }
    else { 
      $html = self::loadInputTypeChosen();
    }    
    return $html;
  }

  /**
   * Chosen Layout
   */
  protected function loadInputTypeChosen()
	{           
    $language = JFactory::getLanguage();
    $extension = 'com_content';
    $language_tag = $language->getTag(); // loads the current language-tag
    $language->load('com_multicats', JPATH_SITE, $language_tag, true);    

    $selected = explode(',',$this->value);    
    $options = self::getOptions();

		// The current user display field.
    $html[] = ' 

              <style>
                .chzn-container-multi .chzn-choices li.search-choice span { cursor: move; }
              </style>
              
              <script src="'.JUri::root().'components/com_multicats/helpers/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
              
              <script>
                jQuery(document).ready(function ($) {
                    
                    // reorder on load
                    var search = $( "#form_catids_chzn .chzn-choices li.search-field" );
                    $( "#form_catids_chzn .chzn-choices li.search-choice" ).each(function( index ) {
                        $(this).attr(\'id\',$("#form_catids option").eq($( this ).children("a").attr("data-option-array-index")).val());
                    });
                    
                    
                    var order = $("#jformcatid").val().split(\',\');
                    
                    var ul = $(\'#form_catids_chzn ul.chzn-choices\');
                    
                    $.each(order, function(index, sort) {
                        $(\'#\' + sort).appendTo(ul);
                    });
                    
                    ul.append(search);
                    
                    // On change                  
                    //$("#form_catids").on("change", function() { $("#jformcatid").val($("#form_catids").val());});
                    $("#form_catids").on("change", function() { 
                      
                      var selectedIds = $("#form_catids").val();
                      
                      var results = [];
                      $( "#form_catids_chzn .chzn-choices li.search-choice" ).each(function( index ) {                                                        
                        var id = $("#form_catids option").eq($( this ).children("a").attr("data-option-array-index")).val();
                        if($.inArray(id, selectedIds) != -1) {
                          results.push( id );
                        } 
                      });
                      var results = results.join(",");
                      $("#jformcatid").val(results);

                    });
                    
                    
                    // On sort
                    $("#form_catids_chzn ul.chzn-choices").sortable({
                        containment: \'parent\',
                        start: function() {                                                  
                        },
                        update: function() {
                          var results = [];
                          $( "#form_catids_chzn .chzn-choices li.search-choice" ).each(function( index ) {
                            //console.log( index + ": " + $( this ).text() + " selindex: " + $( this ).children("a").attr("data-option-array-index") + " ID: " + $("#form_catids option").eq($( this ).children("a").attr("data-option-array-index")).val() );                                                        
                            results.push( $("#form_catids option").eq($( this ).children("a").attr("data-option-array-index")).val() ); 
                          });  
                          var results = results.join(","); 
                          $("#jformcatid").val(results);
                          //$("#form_catids").on("change", function() { $("#jformcatid").val($("#form_catids").val());});
                          
                          var search = $( "#form_catids_chzn .chzn-choices li.search-field" );
                          var ul = $(\'#form_catids_chzn ul.chzn-choices\');
                          ul.find(".search-field").remove();

                          ul.append(search);
                        }
                    });
          
                });
              </script>
             
          ';
    $html[] = '<input type="hidden" value="'. ( (isset($this->value)) ? $this->value : '' ).'" name="jform[catid]" id="jformcatid" />';
          
		// The current user display field.
		$html[] = '<div class="fltlft">';
    //$html[] = JHTML::_('select.genericlist', $options, 'mcats[]', 'name="mcats[]" multiple="true" class="inputbox"', 'value', 'text', $selected);
    //$html[] = JHTML::_('select.genericlist', $options, ''.$this->name.'[]', 'name="'.$this->name.'" multiple="true" class="inputbox"', 'value', 'text', $selected);
    
    $html[] = JHTML::_('select.genericlist', $options, 'form_catids[]', 'id="form_catids" multiple="true" class="inputbox"', 'value', 'text', $selected);
    
    $html[] = '</div>';
        
    return implode("\n", $html);
  }  
  
  /**
   * Standard Layout
   */        
  protected function loadInputTypeStandard()
	{
    $document = JFactory::getDocument();
    //$document->addScript('http://code.jquery.com/jquery-latest.js');
    //$document->addScript(JUri::base().'components/com_content/helpers/jquery1.7.2.js');
    
    $script = "
    if (typeof jQuery == 'undefined') {
       var script = document.createElement('script');
       script.type = 'text/javascript';
       script.src = '".JUri::base()."components/com_multicats/helpers/jquery1.7.2.js';
       document.getElementsByTagName('head')[0].appendChild(script);
    }";
    $document->addScriptDeclaration($script);    
    
    $document->addScript(JUri::root().'components/com_multicats/helpers/jquery-ui-1.10.4.custom.min.js'); // UI for drag n sort and autocomplete   
    //$document->addStyleSheet("//code.jquery.com/ui/1.10.4/themes/start/jquery-ui.css");
    $document->addStyleSheet(JUri::root().'components/com_multicats/helpers/jquery-ui.css');
    
    $document->addStyleDeclaration("
    .catz { display: inline-block; padding: 5px; margin: 3px; -webkit-border-radius: 5px; border-radius: 5px; color: #555; border: 1px solid #AADE66;
      background: #ffffff; /* Old browsers */
      background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZmZmZmZiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNlNWU1ZTUiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
      background: -moz-linear-gradient(top,  #ffffff 0%, #e5e5e5 100%); /* FF3.6+ */
      background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(100%,#e5e5e5)); /* Chrome,Safari4+ */
      background: -webkit-linear-gradient(top,  #ffffff 0%,#e5e5e5 100%); /* Chrome10+,Safari5.1+ */
      background: -o-linear-gradient(top,  #ffffff 0%,#e5e5e5 100%); /* Opera 11.10+ */
      background: -ms-linear-gradient(top,  #ffffff 0%,#e5e5e5 100%); /* IE10+ */
      background: linear-gradient(to bottom,  #ffffff 0%,#e5e5e5 100%); /* W3C */
      filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e5e5e5',GradientType=0 ); /* IE6-8 */      
    }
    .catz.red { border: 1px solid #FF8080;}
    
    .catz img { padding: 0px; margin: 0px; width: 16px; height: 16px; margin-right: 5px; cursor: pointer;}
    #catmask {width: 100%; height: 100%; position: absolute; display: none; background: rgba(255,255,255,0.7) url(".JURI::root()."administrator/components/com_multicats/assets/images/loading.gif) center no-repeat; }   
    .drag { cursor: move; }
    #sortable { padding: 0px; margin: 0px; }
    #sortable li.catz { margin: 5px 5px 5px 5px; padding: 5px; font-size: 11px; line-height: 16px; }
    .ui-state-highlight { height: 25px; width: 100px; border: 1px dotted #E8B761; list-style: none !important }
    #search_cat { margin-top: 5px; margin-left: 5px; }
    ");
 
    $language = JFactory::getLanguage();
    $extension = 'com_content';
    $language_tag = $language->getTag(); // loads the current language-tag
    $language->load('com_multicats', JPATH_SITE, $language_tag, true);
       
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		// Build the script.
		$script = array();
	  $script[] = '    function jSelectCategory_'.$this->id.'(id, title, object) {';
	  $script[] = '        var txt=title.replace(/,/g,"</span><span style=\"display: inline-block; padding: 5px; margin: 3px; -webkit-border-radius: 5px; border-radius: 5px; color: #555; border: 2px solid #adadad\">");';
    $script[] = '        document.id("sortable").innerText = "";
                          if(document.all){
                               document.id("sortable").innerText = "";
                          } else{
                              document.id("sortable").textContent = "";

                          }
                ';
    $script[] = '        
      var $cjq = jQuery.noConflict();
      $cjq(document).ready(function($) {
        $cjq().ready(function() {  
      
          ids = id.split(",");
          arr = title.split(";");
          if(arr[0] != ""){
            $cjq.each(arr, function(key, value) { 
              var spanTag = document.createElement("li"); 
              spanTag.className = "catz drag ui-state-default";
              spanTag.setAttribute("id","cb-"+ids[key]); 
              icon = "<img src=\"'.JURI::root().'administrator/components/com_multicats/assets/images/del.png\" onclick=\"uncheckCat(id, "+ids[key]+", &quot;&quot;, false );\" />";
              spanTag.innerHTML = icon+value;  
              document.id("sortable").appendChild(spanTag);
            });
          } else {
              var spanTag = document.createElement("li"); 
              spanTag.className = "catz red"; 
              spanTag.innerHTML = "'.JText::_('COM_CONTENT_NO_CATEGORY').'"; 
              document.id("sortable").appendChild(spanTag);            
          }
          link	= "index.php?option=com_categories&view=categories&extension=com_content&layout=cwmodal&tmpl=component&catz="+id+"&function=jSelectCategory_'.$this->id.'&'.JSession::getFormToken().'=1";
          $cjq(".catmodal a.modal").attr({"href": link });          
          
          //$cjq(\'<div>\', {id:"catmask" } ).appendTo("#'.$this->id.'_title");  //recreate catmask

        });
      });
    
   
    
    ';
        
    $script[] = '        document.id("'.$this->id.'_id").value = id;';
	  //$script[] = '        document.id("'.$this->id.'_title").innerText = txt;';
	  $script[] = '        SqueezeBox.close();';
	  $script[] = '    }';

    // drag n sort
    $script[] = '
      var $cjq = jQuery.noConflict();
      $cjq(function() {
        $cjq( "#sortable" ).sortable({
          placeholder: "ui-state-highlight",
          stop: function(event, ui) {
            var data = [];
            
            $cjq("#sortable li").each(function(i, el){
                var p = $cjq(el).attr("id");
                //alert(p);
                p = p.split("-");
                data.push(p[1]);
            });
            $cjq("#'.$this->id.'_id").val(data);
            //alert(data);
          }
        });
        $cjq( "#sortable" ).disableSelection();
        
      });  
    ';
		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));


    $delscript[] = '
   
  function uncheckCat(id, item, title, chck) {
    var $cjq = jQuery.noConflict();
    $cjq(document).ready(function($) {
      $cjq().ready(function() {  
      
        var rand = \'rand=\'+Math.random();
        $cjq("#catmask").css("display" , "block");
  
        $cjq.ajax({
          type: "GET",                                                                             
          //url:"'.JUri::root().'components/com_multicats/mcats.php",
          url:"index.php?option=com_multicats&task=multicats&format=raw",
          data: rand+"&item="+item+"&chck="+chck+"&client=administrator&rand2="+Math.random(),
          success:function(results){
              //alert(results);
              var obj=$cjq.parseJSON(results); // now obj is a json object
              var cattitles = \'\';
              var catids = \'\';
              var i = 1;
              var j = 1;
              $cjq.each( obj, function(key){  
                $cjq.each( obj[key], function(k,v){
                 if(k == \'id\'){
                  if(i == 1) { catids = v; }
                  else { catids = catids + \',\' + v; }
                  i = i + 1;
                 }
                 if(k == \'title\'){
                  if(j == 1) { cattitles = v; }
                  else { cattitles = cattitles + \';\' + v; }
                  j = j + 1;
                 }
                });
              });
    
              $cjq("#sortable").remove(\'.catz\');
              document.getElementById("'.$this->id.'_id").value=catids;
              $cjq("#sortable li.catz").remove();
              
              //create new span structure
              arr = cattitles.split(";"); //titles
              ids = catids.split(","); //ids
              
              if(arr[0] != ""){
                $cjq.each(arr, function(key, value) { 
                  var spanTag = document.createElement("li"); 
                  spanTag.className = "catz drag ui-state-default";
                  spanTag.setAttribute("id","cb-"+ids[key]);                  
                  icon = "<img src=\"'.JURI::root().'administrator/components/com_multicats/assets/images/del.png\" onclick=\"uncheckCat(id, "+ids[key]+", &quot;&quot;, false );\" />";
                  spanTag.innerHTML = icon+value; 
                  document.id("sortable").appendChild(spanTag);
                  
                });
              } else { 
                  var spanTag = document.createElement("li"); 
                  spanTag.className = "catz red"; 
                  spanTag.innerHTML = "'.JText::_('No category').'"; 
                  document.id("sortable").appendChild(spanTag);            
              }
              $cjq("#catmask").css("display" , "none");
                       
            } // end success
          });
      });
    });
  }
  


';
		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $delscript));

		// Setup variables for display.
		$html	= array();
		//$link	= 'index.php?option=com_categories&amp;view=categories&amp;extension=com_content&amp;layout=modal&amp;tmpl=component&amp;function=jSelectCategory_'.$this->id;
   	$link	= 'index.php?option=com_categories&amp;view=categories&amp;extension=com_content&amp;layout=cwmodal&amp;tmpl=component&amp;catz='.$this->value.'&amp;function=jSelectCategory_'.$this->id;

    //tvorba řetězce názvů kategorií
		$titles = '<ul id="sortable">';
    $arr = explode(',',$this->value);
    $i = 1;
    $i = 1;
    $db	= JFactory::getDBO();
    foreach($arr as $key => $value){      
  		$db->setQuery(
  			'SELECT title' .
  			' FROM #__categories' .
  			' WHERE id = '.(int) $value.' AND id > 1'
  		);

      if($name = $db->loadResult()){        
        $titles .= "<li id='cid-".$value."' class='catz drag ui-state-default'><img src='".JURI::root()."administrator/components/com_multicats/assets/images/del.png' onclick=\"uncheckCat(id, ".$value.", &quot;&quot;, false);\"/>".$name."</li>";
        $data[] =  array("id" => $value, "title" =>  $name);
      } else {
        $titles .= "<li class='catz red'>".JText::_('COM_CONTENT_NO_CATEGORY')."</span>";
      }
      
      //if($i < count($arr)){ $titles .= '<br /> ';}
      $i++;
    }
    $titles .= '</ul>';

		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
		}

		if (empty($title)) {
			$title = JText::_('COM_CONTENT_SELECT_AN_ARTICLE');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

    //set session
    $session = JFactory::getSession();
    
    //set session
    //$session = JFactory::getSession();
    
    /* SESSION */
    $catz = '';
    if(isset($data)) {$catz = json_encode($data);}
    //print_r($catz);
    //$_SESSION['mcatz'] = $catz;
    //$session->set("catz", $catz);
    
    $mainframe = JFactory::getApplication();
    $data = $mainframe->setUserState( "com_content.mcats", $catz );
    
    ?>

  <style>
  .ui-autocomplete-loading {
    background: white url('<?php echo JUri::root();?>components/com_multicats/helpers/assets/images/indicator.gif') right center no-repeat;
  }
  </style>
  <script>
  var j$ = jQuery.noConflict();
  
  j$(function() {
    function log(title, id) {

        // add a new id
        catids = document.getElementById("<?php echo $this->id.'_id';?>").value;

        ids = catids.split(",");
        var exists = false;
        if (ids[0] != "") {
            j$.each(ids, function (key, value) {
                if (value == id) {
                    exists = true;
                }
            });
        }
        // only unique id
        if (exists == false) {

            j$(".catz.red").remove();
            
            var spanTag = document.createElement("li");
            spanTag.className = "catz drag ui-state-default";
            spanTag.setAttribute("id", "cb-" + id);
            icon = "<img src=\"<?php echo JURI::root();?>administrator/components/com_multicats/assets/images/del.png\" onclick=\"uncheckCat(id, " + id + ", &quot;&quot;, false );\" />";
            spanTag.innerHTML = icon + title;
            document.id("sortable").appendChild(spanTag);

            if (catids != '') {
                catids = catids + ',';
            }
            catids = catids + id;

            document.getElementById("<?php echo $this->id.'_id';?>").value = catids;
            //$cjq(\'<div>\', {id:"catmask" } ).appendTo("#'.$this->id.'_title");  //recreate catmask

            //refresh link to modal window
            link = "index.php?option=com_categories&view=categories&extension=com_content&layout=cwmodal&tmpl=component&catz=" + catids + "&function=jSelectCategory_<?php echo $this->id;?>&<?php echo JSession::getFormToken();?>=1";
            j$(".catmodal a.modal").attr({
                "href": link
            });

            checkCategory(id);
        }
    }
 
    
    j$( "#search_cat" ).autocomplete({
      source: "index.php?option=com_multicats&task=autocomplete&format=raw",
      minLength: 1,
      select: function( event, ui ) {
        log( ui.item.value, ui.item.id );
        //alert(ui.item.value + ' * ' + ui.item.id);
        
        //clear the search input
        j$(this).val('');
        return false;
        
      }
    });

    function checkCategory(item) {
      var $mcats = jQuery.noConflict();
      $mcats("#catmask").css("display" , "block");
      
      //catztitles = document.getElementById("catztitles").value;
      catzz = document.getElementById("<?php echo $this->id.'_id';?>").value;
      //alert(catzz);
      $mcats.ajax({
        type: "GET",
        url:"index.php?option=com_multicats&task=multicats&format=raw",
        //data: <?php echo '"item="+item+"&title="+title+"&chck="+chck';?>,
        data: "item="+item+"&chck=true&catz="+catzz+"&catztitles=x&client=site",
        success:function(results){
            //alert(results);
            $mcats("#catmask").css("display" , "none");
        }
      });
 
    }
    
    j$("#search_cat").click(function (){
      j$(this).val('');
    });
  });
  
  var $ = jQuery.noConflict();
  </script>


    
    <?php  
		// The current user display field.
		$html[] = '<div class="fltlft">';
		//$html[] = '  <input type="text" id="'.$this->id.'_name" value="'.$titles.'" disabled="disabled" size="100" />';
    //if($titles != '') {} 
    $html[] = '  <div style="position: relative; margin-top: 5px; -webkit-border-radius: 5px; border-radius: 5px; display: block; border: 1px solid #d5d5d5; padding: 5px 10px 5px 5px; margin-bottom: 10px; " id="'.$this->id.'_title">';
    $html[] = '   <div id="catmask"></div>';
    $html[] = '   <input type="text" id="search_cat" name="search_cat" value="'.JText::_('JSEARCH_FILTER_SUBMIT').'..." />';
		// The user select button.
    $html[] = '   <a class="cwbutton modal" title="'.JText::_('COM_CONTENT_CHANGE_CATEGORY').'"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('COM_CONTENT_CHANGE_CATEGORY_BUTTON').'</a>';
    $html[] = '   '.$titles.'';
    $html[] = '  </div>';

		$html[] = '</div>';


		// The active article id field.
		if (0 == (int)$this->value) {
			$value = '';
		} else {
			$value = (int)$this->value;
		}

		// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$this->value.'" />';

		return implode("\n", $html);
	}


  /**
   * Method for getting category options  
  */
	protected function getOptions()
	{
		$options = array();
		$extension = 'com_content';
		$published = (string) $this->element['published'];

		// Load the category options for a given extension.
		if (!empty($extension))
		{
			// Filter over published state or not depending upon if it is present.
			if ($published)
			{
				$options = JHtml::_('category.options', $extension, array('filter.published' => explode(',', $published)));
			}
			else
			{
				$options = JHtml::_('category.options', $extension);
			}

			// Verify permissions.  If the action attribute is set, then we scan the options.
			if ((string) $this->element['action'])
			{

				// Get the current user object.
				$user = JFactory::getUser();

				foreach ($options as $i => $option)
				{
					/*
					 * To take save or create in a category you need to have create rights for that category
					 * unless the item is already in that category.
					 * Unset the option if the user isn't authorised for it. In this field assets are always categories.
					 */
					if ($user->authorise('core.create', $extension . '.category.' . $option->value) != true)
					{
						unset($options[$i]);
					}
				}

			}

			if (isset($this->element['show_root']))
			{
				array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
			}
		}
		else
		{
			JLog::add(JText::_('JLIB_FORM_ERROR_FIELDS_CATEGORY_ERROR_EXTENSION_EMPTY'), JLog::WARNING, 'jerror');
		}


		return $options;
	}

}
