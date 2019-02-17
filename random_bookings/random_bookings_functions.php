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
ini_set('display_errors', 'On');

//======================================
function random_first_name() 
{
    $firstname = array(
        'Johnathon',
        'Anthony',
        'Erasmo',
        'Raleigh',
        'Nancie',
        'Tama',
        'Camellia',
        'Augustine',
        'Christeen',
        'Luz',
        'Diego',
        'Lyndia',
        'Thomas',
        'Georgianna',
        'Leigha',
        'Alejandro',
        'Marquis',
        'Joan',
        'Stephania',
        'Elroy',
        'Zonia',
        'Buffy',
        'Sharie',
        'Blythe',
        'Gaylene',
        'Elida',
        'Randy',
        'Margarete',
        'Margarett',
        'Dion',
        'Tomi',
        'Arden',
        'Clora',
        'Laine',
        'Becki',
        'Margherita',
        'Bong',
        'Jeanice',
        'Qiana',
        'Lawanda',
        'Rebecka',
        'Maribel',
        'Tami',
        'Yuri',
        'Michele',
        'Rubi',
        'Larisa',
        'Lloyd',
        'Tyisha',
        'Samatha',
    );

  
    return $firstname[rand ( 0 , count($firstname) -1)];
}
//===========================================================================

function random_last_name() 
{
     $lastname = array(
        'Mischke',
        'Serna',
        'Pingree',
        'Mcnaught',
        'Pepper',
        'Schildgen',
        'Mongold',
        'Wrona',
        'Geddes',
        'Lanz',
        'Fetzer',
        'Schroeder',
        'Block',
        'Mayoral',
        'Fleishman',
        'Roberie',
        'Latson',
        'Lupo',
        'Motsinger',
        'Drews',
        'Coby',
        'Redner',
        'Culton',
        'Howe',
        'Stoval',
        'Michaud',
        'Mote',
        'Menjivar',
        'Wiers',
        'Paris',
        'Grisby',
        'Noren',
        'Damron',
        'Kazmierczak',
        'Haslett',
        'Guillemette',
        'Buresh',
        'Center',
        'Kucera',
        'Catt',
        'Badon',
        'Grumbles',
        'Antes',
        'Byron',
        'Volkman',
        'Klemp',
        'Pekar',
        'Pecora',
        'Schewe',
        'Ramage',
    );
	
    return $lastname[rand ( 0 , count($lastname) -1)];
}

// Find a randomDate between $start_date and $end_date
function randomDate($sStartDate, $sEndDate, $sFormat = 'Y/m/d')
{
	
	// Convert the supplied date to timestamp
	$sStartDate = str_replace('/', '-', $sStartDate);
	$sEndDate = str_replace('/', '-', $sEndDate);

    $fMin = strtotime($sStartDate);
    $fMax = strtotime($sEndDate);
	
	// Generate a random number from the start and end dates
    $fVal = mt_rand($fMin, $fMax);
	
    // Convert back to the specified date format
    return date($sFormat, $fVal);
}
//===========================================================================

function getRoomsToReachOccupancy($selected_day, $property_uid,$desired_occupancy)
{
	$total_bookings = 0;
	$current_property_details = jomres_singleton_abstract::getInstance('basic_property_details');
	$current_property_details->gather_data($property_uid);
	$number_of_rooms = (int) count($current_property_details->multi_query_result[$property_uid]['rooms']);
	
	$period = new DatePeriod(
		 new DateTime($oStart), 
		 new DateInterval('P1D'),
		 new DateTime($oEnd)
	);
	# There are two ways to get the occupancey, either by checking jomres_contract table using a like, 
	# or by checking #__jomres_room_bookings table using an or for each date.
	
	$query = "SELECT room_bookings_uid
			 FROM #__jomres_room_bookings 
			 WHERE  
				property_uid='$property_uid'
				AND `date` = '" . $selected_day . "' ";  
				
	$results = doSelectSql($query);
	if (!empty($results))
	{
		foreach ($results as $res) {
			$total_bookings ++;
		}
	}
	
	$percentage_booked = ($total_bookings*100)/$number_of_rooms;
	$number_room_needed = ceil(($number_of_rooms * $desired_occupancy)/100-$percentage_booked);
	
	
	return array('remaining_room' => $number_room_needed,
				'total_booked' => $total_bookings);
}
//====================================================================================

function delete_old_bookings($start, $end, $property_uid)
{
	$start = str_replace('/', '-', $start);
	$end = str_replace('/', '-', $end);
	
	$delete_dates_str = '';
	
	$period = new DatePeriod(
		 new DateTime($start), 
		 new DateInterval('P1D'),
		 new DateTime($end)
	);
	
	# Delete fom __jomres_room_bookings
	$query = "	DELETE FROM #__jomres_room_bookings 
				WHERE
				
				property_uid='$property_uid' AND (
			";
				
			
	foreach ($period as $key => $value)
	{
		$query .= "`date` = '".$value->format('Y/m/d')."' OR ";
		$delete_dates_str .= "`date_range_string` like '%".$value->format('Y/m/d')."%' OR ";
	 
	}	
	$query = rtrim($query, " OR ") .")";
	doInsertSql($query, '');
	
	# Also delete from __jomres_contracts
	$query = "DELETE FROM #__jomres_contracts
			  WHERE  
			  property_uid='$property_uid'
				AND (" . $delete_dates_str;  
	$query = rtrim($query, " OR ") .")";
	doInsertSql($query, '');
	
}
//===========================================================================================

function getEmptyRooms($property_uid, $start, $end)
{
	$period = new DatePeriod(
		 new DateTime($start), 
		 new DateInterval('P1D'),
		 new DateTime($end)
	);
			
	$query = "	SELECT jr.room_uid,
					  ra.roomrateperday
				FROM #__jomres_rooms jr
				JOIN  #__jomres_rates ra on ra.roomclass_uid = jr.room_classes_uid
				WHERE
				jr.propertys_uid='$property_uid'
				AND 
				jr.room_uid NOT IN
				(
					SELECT  jrb.room_uid
					FROM #__jomres_room_bookings jrb
					WHERE  
						jrb.property_uid='$property_uid'
						AND ( ";
						
	foreach ($period as $key => $value)
	{
		$query .= "jrb.date = '".$value->format('Y/m/d')."' OR ";
	}
	
	$query = rtrim($query, " OR ");
	$query .= "
								
								
							) 
				)
				ORDER BY jr.room_uid
			";
		
	
	$available_rooms = doSelectSql($query);

	return $available_rooms;
	
}
//======================================================================================

function createRandomGuest($property_uid,$fname,$lname)
{
	//Create a random Guest.
	jr_import('jomres_encryption');
			$jomres_encryption = new jomres_encryption();
								
	$query = "INSERT INTO #__jomres_guests
					(
						`enc_firstname`,
						`enc_surname`,
						`property_uid`
					
					)
				VALUES 
					(
					'".$jomres_encryption->encrypt(random_first_name())."',
					'".$jomres_encryption->encrypt(random_last_name())."',
					'".$property_uid."'
					)";
	 
	$guests_uid = doInsertSql($query, '');
	return $guests_uid;
}

//======================================================================================

function createContract($guests_uid,$check_in_date,$check_out_date,$requestedRoom,$total_price,$property_uid)
{
	// get dateRangeString
	$period = new DatePeriod(
		 new DateTime($check_in_date), 
		 new DateInterval('P1D'),
		 new DateTime($check_out_date)
	);
	$dateRangeString = '';
	foreach ($period as $key => $value)
	{
		$dateRangeString .= $value->format('Y/m/d')  .",";   
	}
	
	$dateRangeString = rtrim($dateRangeString, ','); // remove last comma
	$booking_number = mt_rand(10000000, 99999999);
									
	// Create a contract
	$query = "INSERT INTO #__jomres_contracts (
			`arrival`, 
			`departure`, 
			`guest_uid`, 
			`rooms_tariffs`, 
			`contract_total`,
			`date_range_string`,
			`property_uid`,
			`tag`
			
			)
		VALUES (
			'$check_in_date',
			'$check_out_date',
			".(int) $guests_uid.",
			'".(string) $requestedRoom."',
			".(float) $total_price.",
			'".(string)	$dateRangeString ."',
			'".$property_uid."',
			'" . $booking_number ."'
			)";
	
	$contract_uid = doInsertSql($query, '');
	
	return $contract_uid;
}
//=======================================================================================

function createInvoice($total_price, $contract_uid, $property_uid,$check_in_date,$check_out_date)
{
	$line_items[] = array('tax_code_id' => (int) $mrConfig[ 'accommodation_tax_code' ],
										'name' => '_JOMRES_AJAXFORM_BILLING_ROOM_TOTAL',
										'description' => '('.outputDate($check_in_date).' - '.outputDate($check_out_date).')',
										'init_price' => $total_price,
										'init_qty' => 1,
										'init_discount' => 0);
	$invoice_data = array();
	$new_booking_user_id = get_showtime("new_booking_user_id");
			
	if ( $new_booking_user_id > 0 )
		$invoice_data[ 'cms_user_id' ] = $new_booking_user_id;
	else 
		$invoice_data[ 'cms_user_id' ] = $tmpBookingHandler->tmpguest[ 'mos_userid' ];

	$invoice_data[ 'subscription' ] = false;

	if ($jrConfig[ 'useGlobalCurrency' ] == '1') {
		$invoice_data[ 'currencycode' ] = $jrConfig[ 'globalCurrencyCode' ];
	} else {
		$invoice_data[ 'currencycode' ] = $mrConfig[ 'property_currencycode' ];
	}
	jr_import('jrportal_invoice');
	$invoice = new jrportal_invoice();
	$invoice->contract_id = $contract_uid;
	$invoice->property_uid = $property_uid;
	
	$invoice->create_new_invoice($invoice_data, $line_items);
	$query = 'UPDATE #__jomres_contracts SET invoice_uid = '.$invoice->id.' WHERE contract_uid = '.$contract_uid;
	
	doInsertSql($query, '');
				
					
}
//============================================================================================

function createBooking($rmuid,$check_in_date,$check_out_date,$contract_uid, $property_uid)
{
	$insert_str = '';
	$booking_id_str = '';
	$period = new DatePeriod(
		 new DateTime($check_in_date), 
		 new DateInterval('P1D'),
		 new DateTime($check_out_date)
	);
	$dateRangeString = '';
	foreach ($period as $key => $value)
	{
		$insert_str .= "('".(int) $rmuid."',DATE_FORMAT('" . $value->format('Y/m/d')  ."', '%Y/%m/%d'),'".(int) $contract_uid."','".(int) $property_uid."'), ";
	}
	$insert_str = rtrim($insert_str, ", ");
	
	if ($insert_str != '')
	{
		$query = "INSERT INTO #__jomres_room_bookings (`room_uid`,`date`,`contract_uid`,`property_uid`) 
						VALUES " . $insert_str;
		$booking_id = doInsertSql($query, '');
		
	}
	
}
//============================================================================================

?>