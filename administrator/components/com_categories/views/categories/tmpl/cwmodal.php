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

$list = JRequest::getVar( 'list', array(), '', 'array' );
$limit = $this->state->get('list.limit');
//if(isset($list['limit'])) { $limit = $list['limit']; }

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$extension	= $this->escape($this->state->get('filter.extension'));
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$ordering 	= ($listOrder == 'a.lft');
//$saveOrder 	= ($listOrder == 'a.lft' && $listDirn == 'asc');

$language = JFactory::getLanguage();
$extension = 'com_content';
$language_tag = $language->getTag(); // loads the current language-tag
$language->load('com_multicats', JPATH_SITE, $language_tag, true);
 
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
        var rand = 'rand='+Math.random();
        $mcats.ajax({
          type: "GET",                                                                             
          //url:"<?php echo JUri::base();?>components/com_content/helpers/ajax.php",
          //url:"<?php echo JUri::root();?>components/com_multicats/mcats.php",
          url:"index.php?option=com_multicats&task=multicats&format=raw",
          //data: <?php echo '"item="+item+"&title="+title+"&chck="+chck';?>,
          data: rand+"&item="+item+"&chck="+chck+"&client=administrator&rand2="+Math.random(),
          //data: rand+"&item="+item+"r="+Math.random(),
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
//$document->addScript('http://code.jquery.com/jquery-latest.js');
//$document->addScript(JUri::base().'components/com_content/helpers/jquery1.7.2.js');
$document->addStyleDeclaration('
.conf { display: inline-block; padding: 5px; margin: 3px; -webkit-border-radius: 5px; border-radius: 5px; color: #555; border: 2px solid #AADE66; }
.conf:hover { border: 2px solid;}
#filter-bar { height: auto; }
#filter-bar select { width: auto; }

#catmask {width: 100%; height: 100%; position: fixed; display: none; background: rgba(255,255,255,0.7) url('.JURI::root().'administrator/components/com_multicats/assets/images/loading.gif) center 0px no-repeat; }
.catform { position: relative; }
');
?>

<form class="catform" action="<?php echo JRoute::_('index.php?option=com_categories&view=categories&layout=cwmodal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm">
<?php 
//echo "<pre>"; print_r($_SESSION['__default']['catz']); echo "</pre>";
//print_r($_SESSION['mcatz']);

$i = 1;
/*
$db = JFactory::getDbo();
$query = "SELECT data FROM #__session WHERE session_id ='".$session->getId()."'";
$db->setQuery($query);
$result = $db->loadObject();
*///echo $result->data;

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
}
*/  
?>
	<span><?php echo JText::_('COM_CONTENT_SELECTED_CATEGORIES'); ?></span>
  <input type="inputbox" id="catz" name="catz" value="<?php echo $catz; ?>" readonly="readonly"/>
  <span><?php echo JText::_('COM_CONTENT_SELECTED_CATEGORIES_TITLES'); ?></span>
  <input type="inputbox" id="catztitles" name="catztitles" value="<?php echo htmlentities($catztitles, ENT_QUOTES, 'UTF-8'); ?>" size="100" readonly="readonly"/>

  <button class="conf" type="button" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>(document.id('catz').value, document.id('catztitles').value);"><?php echo JText::_('COM_CONTENT_CAT_SUBMIT'); ?></button>
  
  

  <fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search" style="float: left"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CATEGORIES_ITEMS_SEARCH_FILTER'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class="filter-select fltrt">
			<select name="filter_level" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_MAX_LEVELS');?></option>
				<?php echo JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level'));?>
			</select>

			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

      <select name="filter_access" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<select name="filter_language" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
			</select>
      
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php //echo $this->pagination->getLimitBox(); ?>
        <select id="list_limit" name="list[limit]" class="input-mini chzn-done" onchange="this.form.submit();">
        	<option value="5" <?php if($limit == 5) { echo "selected='selected'"; }?>>5</option>
        	<option value="10" <?php if($limit == 10) { echo "selected='selected'"; }?>>10</option>
        	<option value="15" <?php if($limit == 15) { echo "selected='selected'"; }?>>15</option>
        	<option value="20" <?php if($limit == 20) { echo "selected='selected'"; }?>>20</option>
        	<option value="25" <?php if($limit == 25) { echo "selected='selected'"; }?>>25</option>
        	<option value="30" <?php if($limit == 30) { echo "selected='selected'"; }?>>30</option>
        	<option value="50" <?php if($limit == 50) { echo "selected='selected'"; }?>>50</option>
        	<option value="100" <?php if($limit == 100) { echo "selected='selected'"; }?>>100</option>
        	<option value="0" <?php if($limit == 0) { echo "selected='selected'"; }?>>All</option>
        </select>        
			</div>
      

		</div>
	</fieldset>
	<div class="clr" style="clear: both"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="13%">
          <button type="button" style="font-size: 90%" name="checkall-togglef" title="<?php echo JText::_('COM_CONTENT_CAT_CANCEL'); ?>" onclick="uncheck();" /><?php echo JText::_('COM_CONTENT_CAT_CANCEL'); ?></button>
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			
      if(isset($catz)) {$catzarray = explode(',',$catz);}
      else {$catzarray = array();}
      
      $originalOrders = array();
			foreach ($this->items as $i => $item) :
				$orderkey	= array_search($item->id, $this->ordering[$item->parent_id]);
				$canEdit	= $user->authorise('core.edit',			$extension.'.category.'.$item->id);
				$canCheckin	= $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				$canEditOwn	= $user->authorise('core.edit.own',		$extension.'.category.'.$item->id) && $item->created_user_id == $userId;
				$canChange	= $user->authorise('core.edit.state',	$extension.'.category.'.$item->id) && $canCheckin;
        
        $canChange = false;
			?>
      						<?php if ($canEdit || $canEditOwn) { ?>

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
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'categories.', $canCheckin); ?>
						<?php endif; ?>
						<?php //if ($canEdit || $canEditOwn) :
            $klik = false;?>
						<?php if ($klik == true) : ?>
              <a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>');">
                <?php echo $this->escape($item->title); ?></a>
                               
						<?php else : ?>
							<?php echo $this->escape($item->title); ?>
						<?php endif; ?>
						<p class="smallsub" title="<?php echo $this->escape($item->path);?>">
							<?php echo str_repeat('<span class="gtr">|&mdash;</span>', $item->level-1) ?>
							<?php if (empty($item->note)) : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
							<?php else : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note));?>
							<?php endif; ?></p>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange);?>
					</td>
					<td class="center">
						<?php echo $this->escape($item->access_level); ?>
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