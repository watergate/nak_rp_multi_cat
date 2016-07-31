<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * This models supports retrieving lists of article categories.
 *
 * @since  1.6
 */
class ContentModelCategories extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_content.categories';

	/**
	 * The category context (allows other extensions to derived from this model).
	 *
	 * @var		string
	 */
	protected $_extension = 'com_content';

	private $_parent = null;
  
  // Mcats
  /**
   * Items total
   * @var integer
   */
  var $_total = null;
 
  /**
   * Pagination object
   * @var object
   */
  var $_pagination = null;
  // END MCats 
  
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   The field to order on.
	 * @param   string  $direction  The direction to order on.
	 *
	 * @return  void.
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$this->setState('filter.extension', $this->_extension);

		// Get the parent id if defined.
		$parentId = $app->input->getInt('id');
		$this->setState('filter.parentId', $parentId);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
    // Multicats
    // Get pagination request variables
  	$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
  	$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
   
    $session = JFactory::getSession();
    $registry    = $session->get('registry');
    
    
    $limitstart = $registry->set('global.list.start', JRequest::getVar('limitstart', 0, '', 'int'));
    
    //$this->setState('list.start', JRequest::getVar('limitstart', 0, '', 'int'));
  	// In case limit has been changed, adjust it
  	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
    //$limitstart = 3;
  	$this->setState('limit', $limit);
  	$this->setState('limitstart', $limitstart);
	  // End Multicats
	} 

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('filter.extension');
		$id	.= ':' . $this->getState('filter.published');
		$id	.= ':' . $this->getState('filter.access');
		$id	.= ':' . $this->getState('filter.parentId');

		return parent::getStoreId($id);
	}

	/**
	 * Redefine the function an add some properties to make the styling more easy
	 *
	 * @param   bool  $recursive  True if you want to return children recursively.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems($recursive = false)
	{
		$store = $this->getStoreId();

		if (!isset($this->cache[$store]))
		{
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new Registry;

			if ($active)
			{
				$params->loadString($active->params);
			}

			$options = array();
			$options['countItems'] = $params->get('show_cat_num_articles_cat', 1) || !$params->get('show_empty_categories_cat', 0);
			$categories = JCategories::getInstance('Content', $options);
			$this->_parent = $categories->get($this->getState('filter.parentId', 'root'));

			if (is_object($this->_parent))
			{
				$this->_items = $this->_parent->getChildren($recursive);
			}
			else
			{
				$this->_items = false;
			}
		}

		return $this->_items;
	}

	/**
	 * Get the parent.
	 *
	 * @return  object  An array of data items on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getParent()
	{
		if (!is_object($this->_parent))
		{
			$this->getItems();
		}

		return $this->_parent;
	}
  
  /**
   * Multicats override functions
   */     
  function getData() 
  {
 	// if data hasn't already been obtained, load it
 	if (empty($this->_data)) {
 	    //$query = $this->_buildQuery();
 	    $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));	
 	}
 	return $this->_data;
  }
  
  
  function getTotal()
  {
 	// Load the content if it doesn't already exist
 	if (empty($this->_total)) {
 	    //$query = $this->_buildQuery();
       //$this->_total = $this->_getListCount($query);	
 	}
    $user  = JFactory::getUser();

    $aid = $user->getAuthorisedViewLevels();
    $levels = implode(',',$aid);
    
    $db		= JFactory::getDbo();
    $query = "SELECT count(id) FROM #__categories WHERE extension = 'com_content' AND access IN(".$levels.") ";
    $db->setQuery($query);
    $this->_total = $db->loadResult();
    
    return $this->_total;
  }
  
  function getPagination()
  {
 	// Load the content if it doesn't already exist
 	if (empty($this->_pagination)) {
 	    jimport('joomla.html.pagination');
 	    $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
 	}
 	return $this->_pagination;
  }       
}