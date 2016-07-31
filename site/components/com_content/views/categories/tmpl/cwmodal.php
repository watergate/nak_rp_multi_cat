<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
//JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$userId		= $user->get('id');
/*$extension	= $this->escape($this->state->get('filter.extension'));
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$ordering 	= ($listOrder == 'a.lft');
//$saveOrder 	= ($listOrder == 'a.lft' && $listDirn == 'asc');
*/
$language = JFactory::getLanguage();
$extension = 'com_content';
$language_tag = $language->getTag(); // loads the current language-tag
$language->load('com_multicats', JPATH_SITE, $language_tag, true);
 
$ordering = 'a.lft';
$function = JRequest::getCmd('function', 'jSelectCategory');

//$session =& JFactory::getSession();
//$data = $session->get("catz");
//echo $data;
//$data = $_SESSION['mcatz'];

$mainframe = JFactory::getApplication();
$data = $mainframe->getUserState( "com_content.mcats", '' );

$data = json_decode($data);
?>

<script type="text/javascript">
function checkCategory(id, item, title, chck) {
      var $mcats = jQuery.noConflict();
      $mcats("#catmask").css("display" , "block");
      
      catztitles = document.getElementById("catztitles").value;
      catzz = document.getElementById("catz").value;
      //alert(catztitles);
      $mcats.ajax({
        type: "GET",
        //url:"<?php echo JUri::base();?>components/com_content/helpers/ajax.php",
        //url:"<?php echo JUri::root();?>components/com_multicats/mcats.php",
        url:"index.php?option=com_multicats&task=multicats&format=raw",
        //data: <?php echo '"item="+item+"&title="+title+"&chck="+chck';?>,
        data: "item="+item+"&chck="+chck+"&catz="+catzz+"&catztitles=x&client=site",
        success:function(results){
            //alert(results);
            var obj=$mcats.parseJSON(results); // now obj is a json object
            var cattitles = '';
            var catids = '';
            var i = 1;
            var j = 1;
            $mcats.each( obj, function(key){  

              $mcats.each( obj[key], function(k,v){
               //alert( "Key: " + k + ", Value: " + v );
               if(k == 'id'){
                //alert( "ID: " + v );
                //if(i == obj.length) {
                if(i == 1) { catids = v; }
                else { catids = catids + ',' + v; }
                i = i + 1;
               }
               if(k == 'title'){
                //alert( "Title: " + v );
                if(j == 1) { cattitles = v; }
                else { cattitles = cattitles + ';' + v; }
                j = j + 1;
               }
               //alert(obj[key].title); // will alert "1"
              });
            });
            //if(catids.substring(0,1) == ','){catids = catids.substring(1);}  
            //if(cattitles.substring(0,4) == 'null'){cattitles = cattitles.substring(5);}
            //alert(catids); 
            //alert(cattitles);
            document.getElementById("catz").value=catids;
            document.getElementById("catztitles").value=cattitles;
            //alert(obj[0].id); // will alert "1"
            //alert(obj[0].title); // will alert "This is some content"
            
            $mcats("#catmask").css("display" , "none");
        }
      });
 
}
</script>
  
<?php
$document = JFactory::getDocument();
$document->addScript('http://code.jquery.com/jquery-latest.js');
//$document->addScript(JUri::base().'administrator/components/com_content/helpers/jquery1.7.2.js');
$document->addStyleDeclaration('
body { font-family: arial; font-size: 12px; }
form#adminForm { width: 90%}
.conf { cursor: pointer; display: inline-block; padding: 5px; margin: 3px; -webkit-border-radius: 5px; border-radius: 5px; color: #555; border: 2px solid #AADE66; }
.conf:hover { border: 2px solid;}
.pagination ul li {display: inline; padding: 5px; margin: 3px; -webkit-border-radius: 5px; border-radius: 5px; color: #888; /* border: 2px solid #AADE66; */}
.pagination ul li a { color: #000; font-weight: bold; text-decoration: none;}
.pagination ul li a:hover { color: #AADE66}
table { border-collapse: collapse;}
table td { border: 1px solid #f1f1f1; padding: 5px; }
tfoot { text-align: center;}
.center { text-align: center; }

#catmask {width: 100%; height: 100%; position: fixed; display: none; background: rgba(255,255,255,0.7) url('.JURI::root().'administrator/components/com_multicats/assets/images/loading.gif) center 0px no-repeat; }
.catform { position: relative; }
');
?>

<form class="catform" action="<?php echo JRoute::_('index.php?option=com_content&view=categories&layout=cwmodal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm">
<?php 
//echo "<pre>"; print_r($_SESSION['__default']['catz']); echo "</pre>";
/*
$session =& JFactory::getSession();
$data = $session->get("catz");
$data = json_decode($data);

*/
$i = 1;
$catz = '';
$catztitles = ''; 
//$data = object_to_array($data);
//echo "<pre>"; print_r($data); echo "</pre>";
$count = count( (array) $data);
if($count > 0)
{
  foreach($data as $key => $item){
    $catz .= $item->id;
    $catztitles .= $item->title;
    if($i < $count) {
      $catz .= ',';
      $catztitles .= ';';  
    }
    $i++;
  }
}
/*
$i = 1;

$catz = '';
$catztitles = ''; 

if(isset($_GET['catz']) AND $_GET['catz'] != ''){
  $catz = $_GET['catz'];
  $catzarray = explode(',',$_GET['catz']);
  $db = &JFactory::getDbo();
  foreach($catzarray as $key => $cat){
    $query = "SELECT title FROM #__categories WHERE id = ".(int)$cat."";
    $db->setQuery($query);
    $result = $db->loadObject();
    $catztitles .= $result->title;
    if(count($catzarray) > $key+1){ $catztitles .= ','; }
  }
} */
?>
	<span><?php echo JText::_('COM_CONTENT_SELECTED_CATEGORIES'); ?></span>
  <input type="inputbox" id="catz" name="catz" value="<?php echo $catz; ?>" readonly="readonly" /><br />
  <span><?php echo JText::_('COM_CONTENT_SELECTED_CATEGORIES_TITLES'); ?></span>
  <input type="inputbox" id="catztitles" name="catztitles" value="<?php echo htmlentities($catztitles, ENT_QUOTES, 'UTF-8'); ?>" size="60" readonly="readonly"/>

  <button class="conf" type="button" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>(document.id('catz').value, document.id('catztitles').value);"><?php echo JText::_('COM_CONTENT_CAT_SUBMIT'); ?></button>
  
  
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="13%">
          <button type="button" style="font-size: 90%" name="checkall-togglef" title="<?php echo JText::_('COM_CONTENT_CAT_CANCEL'); ?>" onclick="uncheck();" /><?php echo JText::_('COM_CONTENT_CAT_CANCEL'); ?></button>
				</th>
				<th>
					<?php echo JText::_('JGLOBAL_TITLE'); ?>
				</th>
				<th width="5%">
          <?php echo JText::_('JSTATUS'); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JText::_('JGRID_HEADING_LANGUAGE'); ?>
				</th>
				<th width="1%" class="nowrap">
          <?php echo JText::_('JGRID_HEADING_ID'); ?>
				</th>
			</tr>
		</thead>

<?php
    $session = JFactory::getSession();
    $registry   = $session->get('registry');
    $limit = $registry->get('global.list.limit', 0);
    $app = JFactory::getApplication();
    if(!$limit) { $limit = $app->getCfg('list_limit'); }
    $limitstart = $registry->get('global.list.start', 0);

  	//echo "<pre>"; print_r($_SESSION['__default']); echo "</pre>";
    //get users acl
    $user  = JFactory::getUser();

    $aid = $user->getAuthorisedViewLevels();
    $levels = implode(',',$aid);

    $db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
				'a.id, a.title, a.alias, a.note, a.published, a.access' .
				', a.checked_out, a.checked_out_time, a.created_user_id' .
				', a.path, a.parent_id, a.level, a.lft, a.rgt' .
				', a.language'

		);
		$query->from('#__categories AS a');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_user_id');
    
    $query->where('a.extension = '.$db->quote($extension));
    /*
		// Filter by extension
		if ($extension = $this->getState('filter.extension')) {
			$query->where('a.extension = '.$db->quote($extension));
		} */
    /*
		// Filter on the level.
		if ($level = $this->getState('filter.level')) {
			$query->where('a.level <= '.(int) $level);
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = ' . (int) $access);
		}
    */
		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

		// Filter by published state
		//$published = $this->getState('filter.published');
    $published = '';
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}
    /*
		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $db->Quote('%'.$db->escape(substr($search, 7), true).'%');
				$query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.' OR a.note LIKE '.$search.')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('a.language = '.$db->quote($language));
		}
    */
		// Add the list ordering clause
		//$listOrdering = $this->getState('list.ordering', 'a.lft');
		//$listDirn = $db->escape($this->getState('list.direction', 'ASC'));
    $listOrdering = 'a.lft';
    $listDirn = 'ASC';
		if ($listOrdering == 'a.access') {
			$query->order('a.access '.$listDirn.', a.lft '.$listDirn);
		} else {
			$query->order($db->escape($listOrdering).' '.$listDirn);
		}

    /* get query */
    
    
    //SELECT * FROM #__categories WHERE extension = 'com_content' AND access IN(".$levels.") ORDER BY ordering LIMIT ".$limitstart.' , '.$limit;
    

    
    $db->setQuery($query,$limitstart,$limit);
    $categories = $db->loadObjectList();

    /*
    //celkem
    $query = "SELECT count(id) FROM #__categories WHERE extension = 'com_content' AND published = '1'";
    $db->setQuery($query);
    $count = $db->loadResult();
    $pages = ceil($count / $limit);
    $page = 1+($limitstart/$limit);  
    */  
?>
		<tfoot>
			<tr>
				<td colspan="15">
						<nav class="pagination">
            <?php echo $this->pagination->getListFooter();?>	
            </nav>            
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$catzarray = explode(',',$catz);
      
      $originalOrders = array();
       
			foreach ($categories as $i => $item) :
				
        //$orderkey	= array_search($item->id, $this->ordering[$item->parent_id]);
				$canEdit	= $user->authorise('core.edit',			$extension.'.category.'.$item->id);
				$canCheckin	= $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				$canEditOwn	= $user->authorise('core.edit.own',		$extension.'.category.'.$item->id) && $item->created_user_id == $userId;
				$canChange	= $user->authorise('core.edit.state',	$extension.'.category.'.$item->id) && $canCheckin;
        
        //echo $item->title." - ".$userId ." - ".$canChange."<br />";
        $visible = $user->authorise('core.create',	$extension.'.category.'.$item->id) && $canCheckin;
        $canChange = false;
			?>                     
      						<?php if ($visible) { ?>

				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php //echo JHtml::_('grid.id', $i, $item->id);
            if(in_array($item->id,$catzarray)){
              $chck = ' checked="checked"';
            } else { $chck = ''; }
            ?>
            <input class="cats" <?php echo $chck;?> type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $item->id; ?>" onclick="checkCategory(id, <?php echo $item->id;?>, <?php echo '&quot;'.$this->escape(htmlentities($item->title)).'&quot;'; ?>, this.checked);" title="" />
					</td>
					<td>
						<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level-1) ?>
						<?php //if ($canEdit || $canEditOwn) :
            $klik = false;?>
						<?php if ($klik == true) : ?>
              <a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>');">
                <?php echo $this->escape($item->title); ?></a>
                               
						<?php else : ?>
							<?php echo $this->escape($item->title); ?>
						<?php endif; ?>

					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange);?>
					</td>

					<td class="center nowrap">
					<?php if ($item->language=='*'):?>
						<?php echo JText::alt('JALL', 'language'); ?>
					<?php else:?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif;?>
					</td>
					<td class="center">
						<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt);?>">
							<?php echo (int) $item->id; ?></span>
					</td>
				</tr>
			<?php 
      }
      endforeach; ?>
		</tbody>
	</table>
	<?php //Load the batch processing form. ?>
	<?php //echo $this->loadTemplate('batch'); ?>

	<div>
		<input type="hidden" name="extension" value="<?php echo $extension;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

<script>
var $mcats = jQuery.noConflict();
function uncheck(){
  $mcats("form input:checkbox.cats").attr('checked', false);
  checkCategory(0,0,0,false);
}
$mcats('<div>', {id:"catmask" } ).prependTo("form.catform");  //recreate catmask
</script>