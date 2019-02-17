<?php
/**
 * Generate Random Booking Plugin file.
 *
 * @author Zeina Zada <zeinazada@yahoo.com>
 *
 * @version Jomres 9.16.1
 *
 *
**/
defined( '_JOMRES_INITCHECK' ) or die( '' );

class plugin_info_random_bookings
    {
    function __construct()
        {
        $this->data=array(
            "name"=>"random_bookings",
            "category"=>"Misc",
            "marketing"=>"Creates Random bookings with specific date range and on a minimum Occupancy.",
            "version"=>(float)"1.0",
            "description"=> "Creates Random bookings with specific date range and on a minimum Occupancy.",
            "lastupdate"=>"2019/02/02",
            "min_jomres_ver"=>"9.9.4",
            "manual_link"=>'',
            'change_log'=>'',
            'highlight'=>'',
            'image'=>'',
            'demo_url'=>'',
            'third_party_plugin_latest_available_version' => "",
            'developer_page'=>''
            );
        }
    }

	