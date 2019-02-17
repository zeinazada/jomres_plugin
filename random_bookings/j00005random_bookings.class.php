<?php
/**
 * Core file.
 *
 * @author Vince Wooll <sales@jomres.net>
 *
 * @version Jomres 9.16.1
 *
 * @copyright	2005-2019 Vince Wooll
 * Jomres (tm) PHP, CSS & Javascript files are released under both MIT and GPL2 licenses. This means that you can choose the license that best suits your project, and use it accordingly
 **/

// ################################################################
defined('_JOMRES_INITCHECK') or die('');
// ################################################################
// 00005 This trigger point is used by most plugins primarily to load their own language files and frontend/admin menu items.
class j00005random_bookings
{
	public function __construct()
	{
		// Must be in all minicomponents. Minicomponents with templates that can contain editable text should run $this->template_touch() else just return
		$MiniComponents = jomres_singleton_abstract::getInstance('mcHandler');
		if ($MiniComponents->template_touch) {
			$this->template_touchable = false;

			return;
		}
		
		
		$property_uid = getDefaultProperty();
		
		$mrConfig = getPropertySpecificSettings($property_uid);
		
		$thisJRUser = jomres_singleton_abstract::getInstance('jr_user');
		
		$jomres_menu = jomres_singleton_abstract::getInstance('jomres_menu');

		
		if ($thisJRUser->accesslevel >= 50 && $mrConfig[ 'is_real_estate_listing' ] != '1') 
		{
			//add_item takes the following parameters.
			// parmameter1 will show under which section should the item added.
				//  1 : Home; 10 : My account; 20 : Properties; 30 : Bookings; 40 : Guests; 50 : Invoices; 60 : Reports; 70 : Misc; 80 : Settings;90 : Help
			// paramter2 is for the label that will be displayed on the screen.
			// parameter3 is for the task, what to call when user clicks on the new item.
				// on my case it is calling 
				// http://localhost/urban/index.php?lang=en&task=create_random_bookings
			// parameter4 is what icon to show next to label.
			//booking section menus
			$jomres_menu->add_item(30, 'Create Random Bookings', 'create_random_bookings', 'fa-calendar-o');
		}
	}
	
	
	
	// This must be included in every Event/Mini-component
	public function getRetVals()
	{
		return null;
	}
}
