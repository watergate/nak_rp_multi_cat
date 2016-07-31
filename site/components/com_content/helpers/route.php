<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content Component Route Helper.
 *
 * @since  1.5
 */
abstract class ContentHelperRoute
{
	protected static $lookup = array();

	/**
	 * Get the article route.
	 *
	 * @param   integer  $id        The route of the content item.
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getArticleRoute($id, $catid = 0, $language = 0)
	{
    // CW multicats override
    if(!is_numeric($catid) AND JRequest::getVar('view') != 'article') { $catid = ContentHelperRoute::getCategory($catid); }
		$needles = array(
			'article'  => array((int) $id)
		);

		// Create the link
		$link = 'index.php?option=com_content&view=article&id=' . $id;

		if ((int) $catid > 1)
		{
			$categories = JCategories::getInstance('Content');
			$category   = $categories->get((int) $catid);

			if ($category)
			{
				$needles['category']   = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid=' . $catid;
			}
		}

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang=' . $language;
			$needles['language'] = $language;
		}
		//Mcats Note - first check current category itemid - sometimes seem that _finditem has trouble with that 
		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Get the category route.
	 *
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getCategoryRoute($catid, $language = 0)
	{
		if ($catid instanceof JCategoryNode)
		{
			$id       = $catid->id;
			$category = $catid;
		}
		else
		{
			$id       = (int) $catid;
			$category = JCategories::getInstance('Content')->get($id);
		}

		if ($id < 1 || !($category instanceof JCategoryNode))
		{
			$link = '';
		}
		else
		{
			$needles               = array();
			$link                  = 'index.php?option=com_content&view=category&id=' . $id;
			$catids                = array_reverse($category->getPath());
			$needles['category']   = $catids;
			$needles['categories'] = $catids;

			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				$link .= '&lang=' . $language;
				$needles['language'] = $language;
			}

			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}

	/**
	 * Get the form route.
	 *
	 * @param   integer  $id  The form ID.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getFormRoute($id)
	{
		// Create the link
		if ($id)
		{
			$link = 'index.php?option=com_content&task=article.edit&a_id=' . $id;
		}
		else
		{
			$link = 'index.php?option=com_content&task=article.edit&a_id=0';
		}

		return $link;
	}

	/**
	 * Find an item ID.
	 *
	 * @param   array  $needles  An array of language codes.
	 *
	 * @return  mixed  The ID found or null otherwise.
	 *
	 * @since   1.5
	 */
	protected static function _findItem($needles = null)
	{
		$app      = JFactory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();

			$component  = JComponentHelper::getComponent('com_content');

			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}

					if (isset($item->query['id']))
					{
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();

		if ($active
			&& $active->component == 'com_content'
			&& ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}

  /** CW multicats
   * finds the category id depending on if it is category or article view 
   */     
  
  static function getCategory($catid) {

    $view = JRequest::getCmd('view');
    if($view == 'category'){
      $catid = JRequest::getCmd('id');  
    }
    elseif($view == 'article') {
      //can be multicategories like 84:category,85:category2 ...but this should handle it anyway, just the first part before first ":"
      if(JRequest::getCmd('catid')) {
        $catid = JRequest::getCmd('catid');  
      }
      $catarray = explode(':',$catid);
      $catarray = explode(',',$catarray[0]);
      $catid = $catarray[0];
    }
    elseif($view == 'featured') {
      $catarray = explode(',',$catid);
      $catid = $catarray[0];  
    }
    
    return $catid; 
  }
    
  static function getMCat($id, $cid){
    $db = JFactory::getDbo();
    //$catid = JRequest::getVar('catid');
    if(isset($catid)){
      $query = 'SELECT id,alias FROM #__categories WHERE id = '.$catid;
      $db->setQuery($query);
      $cat = $db->loadObject();
      return $cat;      
    }else {
      $cats = explode(',',$cid);
      if(is_numeric($cats[0])){
        $query = 'SELECT id,alias FROM #__categories WHERE id = '.$cats[0];
        $db->setQuery($query);
        $cat = $db->loadObject();
        return $cat;      
      } else {
        return;
      }
    }
    
  }


  static function getCatTree($cid){ 
    //inicialize parent tree node
    $parent = new stdClass();
    $parent->id = $cid;
    $tree = array();
    $tree[] = $parent;
    
    //loads childrens
    $tree = array_merge($tree, ContentHelperRoute::getCatChildren($cid));
    //loads subchildrens recursively
    foreach($tree as $child){
      $sub = ContentHelperRoute::getCatChildren($child->id);
      $tree = array_merge($tree, $sub);  
    }
    return $tree;
  }

  static function getCatChildren($cid){
    $db = JFactory::getDbo();
    if(is_numeric($cid)){
      $query = 'SELECT id FROM #__categories WHERE parent_id = '.$cid;
      $db->setQuery($query);
      $cat = $db->loadObjectList();
      return $cat;      
    }
  }

  static function getNumItems($cid){
    $db = JFactory::getDbo();
    // loads tree structure of parent + subcategories
    $tree = ContentHelperRoute::getCatTree($cid);

    $items = array();
    foreach($tree as $cat){
      if(is_numeric($cat->id)){
        $query = 'SELECT id FROM #__content WHERE state=1 AND FIND_IN_SET('.$cat->id.',catid)';
        $db->setQuery($query);
        $cat = $db->loadObjectList();
        $items = array_merge($items, $cat);  
  
      }
    }
    // we get the article IDs simple array to loop through and count only unique ones
    $itemlist = array();
    foreach($items as $item){
      if(!in_array($item->id,$itemlist)){
        $itemlist[] = $item->id;
      }
    }

    // we return result count
    $total = count($itemlist);
    return $total;
  }  
} 