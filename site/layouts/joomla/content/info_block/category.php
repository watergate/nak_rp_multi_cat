<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

?>
			<dd class="category-name">
        <?php
        //multicats
        require_once JPATH_SITE . '/components/com_multicats/helpers/content.php';
        echo MulticatsContentHelper::getCategoryNames($displayData['item']->id,$displayData['params']->get('link_category'));
        ?>    
			</dd>