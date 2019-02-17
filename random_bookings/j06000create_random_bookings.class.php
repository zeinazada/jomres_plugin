<?php

/**
 * Generate Random Booking Plugin file.
 *
 * @author Zeina Zada <zeinazada@yahoo.com>
 *
 * @version Jomres 9.16.1
 * this file is to fill the html templates.
 *
**/

// ################################################################
defined('_JOMRES_INITCHECK') or die('');
// ################################################################

class j06000create_random_bookings
{
	public function __construct($componentArgs)
	{
		// Must be in all minicomponents. Minicomponents with templates that can contain editable text should run $this->template_touch() else just return
		$MiniComponents = jomres_singleton_abstract::getInstance('mcHandler');
		if ($MiniComponents->template_touch) {
			$this->template_touchable = true;

			return;
		}
		
		$this->retVals = '';

		$mrConfig = getPropertySpecificSettings();
		$defaultProperty = getDefaultProperty();
		
		$no_html = jomresGetParam($_REQUEST, 'no_html', '');
		
		if ($no_html ==1)
		{ 
			
			include 'random_bookings_functions.php';
			
			//fixed variables.
			$max_allowed_booking_days = 90;
			
			//variables posted from the form.
			$start = jomresGetParam($_REQUEST, 'start_date_input', '');
			$end = jomresGetParam($_REQUEST, 'end_date_input', '');
			$occupancy = jomresGetParam($_REQUEST, 'occupancy', '');
			$delete_old = jomresGetParam($_REQUEST, 'delete_old', '');
			$formated_end =  str_replace('/', '-', $end);
			$formated_start =  str_replace('/', '-', $start);
			
					
			$check_in_period = new DatePeriod(
				 new DateTime($formated_start), 
				 new DateInterval('P1D'),
				 new DateTime($formated_end)
			);
			
			//delete old bookings if triggered from front end.
			if ($delete_old == "1")
			{
				delete_old_bookings($start,$end,$defaultProperty);
			}
			
						
			foreach ($check_in_period as $key => $value)
			{
				$check_in_date = $value->format('Y/m/d');
					
				$formated_start =  str_replace('/', '-', $check_in_date);
				$roomsArr = getRoomsToReachOccupancy($check_in_date, $defaultProperty,$occupancy);
				$number_room_needed=  $roomsArr['remaining_room'];
				$total_room_booked =  $roomsArr['total_booked'];
			
				while ($total_room_booked<$number_room_needed)
				{
					
					$formated_check_in_date = str_replace('/', '-', $check_in_date);
					
					// Get max difference allowed (90 or less depending on start-end dates)
					$max_difference =  round((strtotime($formated_end) - strtotime($formated_check_in_date))/ (60 * 60 * 24)); 
					if ($max_difference < $max_allowed_booking_days) { $max_allowed_booking_days = $max_difference;}
					
					
					// Get random number of how much days the guest will stay.
					$random_day_stay = rand(1,$max_difference);
					
					//Checkout date
					$check_out_date =  date('Y/m/d', strtotime($check_in_date. ' + ' . $random_day_stay .' days'));
					
				
					$available_rooms = getEmptyRooms($defaultProperty, $check_in_date,$check_out_date);
					
					if (!(empty($available_rooms)))
					{
						$possible_rooms = array();
						$room_prices = array();
						$room_count =0;
					
						foreach ($available_rooms as $r ) 
						{	
							$possible_rooms[] = $r->room_uid;
							$room_prices[] = $r->roomrateperday;
							$room_count++;
						}
				
						//get a random room number			
						$random_index = rand(0,$room_count-1 );
						$rmuid = $possible_rooms[$random_index];
						$total_price = $room_prices[$random_index] * $random_day_stay;
						
						$requestedRoom = $rmuid .'^1';
						
						$guests_uid = createRandomGuest($defaultProperty);
						
					
						$contract_uid =  createContract($guests_uid,$check_in_date,$check_out_date,$requestedRoom,$total_price,$defaultProperty);
						
						
						createBooking($rmuid,$check_in_date,$check_out_date,$contract_uid, $defaultProperty);
						createInvoice($total_price, $contract_uid, $defaultProperty,$check_in_date,$check_out_date);
						
					}
					$total_room_booked++;
				}
				
			}
			jomresRedirect( jomresURL(JOMRES_SITEPAGE_URL."&task=dashboard"), 'Bookings Created!' );
		
		
		}
		else
		{
			$pageoutput = array();
			$output = array();
			
			$output['RANDOM_BOOKING_TITLE'] ="Create Random Bookings";
			$output['START_DATE_LABEL'] ="Start date";
			$output['END_DATE_LABEL'] ="End date";
			$output['MINIMUM_OCCUPANCY'] = "Minimum Occupancy (%)";
			$output['DELETE_OLD_BOOKING_LABEL'] = "Delete old bookings";
			$output['GENERATE_LABEL'] = "Generate";
			$start_date = date('Y/m/d', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
			
			$end_date = date('Y/m/d', strtotime($start_date.' +1 days'));
			$output['START_DATE_INPUT'] = generateDateInput("start_date_input",$start_date);
			$output['END_DATE_INPUT'] = generateDateInput("end_date_input",$end_date);
			$output['RANDOM_BOOKING_URL'] = generateDateInput("end_date_input",$end_date);
			$output['PROPERTY_ID'] = $defaultProperty;
			
			
			$yesno = array();
			$yesno[] = jomresHTML::makeOption('0', jr_gettext('_JOMRES_COM_MR_NO', '_JOMRES_COM_MR_NO', false));
			$yesno[] = jomresHTML::makeOption('1', jr_gettext('_JOMRES_COM_MR_YES', '_JOMRES_COM_MR_YES', false));
			
			
			$output[ 'DELETE_OLD_BOOKING_INPUT' ] = jomresHTML::selectList($yesno, 'delete_old', 'class="form-control" size="1"', 'value', 'text',0 );

				
			$ePointFilepath = get_showtime('ePointFilepath');
		
			$pageoutput[] = $output;
			$tmpl = new patTemplate();
			$tmpl->setRoot( $ePointFilepath.JRDS.'templates'.JRDS.find_plugin_template_directory().JRDS);
			$tmpl->readTemplatesFromInput( 'create_random_bookings.html'); 
			$tmpl->addRows('pageoutput', $pageoutput);
			$tmpl->displayParsedTemplate();
		
		}
     
	}


	// This must be included in every Event/Mini-component
	public function getRetVals()
	{
		return $this->retVals;
	}
	
}
//========================================================================

	


