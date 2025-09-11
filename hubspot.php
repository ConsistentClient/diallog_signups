<?PHP

function hubspot_api_contact_properties ( ) {
        return array(
                "source" => "source",
                "first_name" => "firstname",
                "last_name" => "lastname",
                "phone" => "phone",
                "email"         => 'email',
                "street_address" => "address",
                "unit_number" => "unit_number",
                "building_access_instructions" => "buzzer_code",
                "city" => "city",
                "state" => "province",
                "postcode" => "zip",
                "monthly_bill_payment_option" => "payment_method",
                "selected_internet_plan" => "package_selected",
                "selected_modem_plan" => "modem_selected",
                "selected_phone_plan" => "phone_selected",
                "BYO_ModemName" => "byo_modem_model",
                "install_timeslot_1" => "install_timeslot_1",
                "install_timeslot_2" => "install_timeslot_2",
                "install_timeslot_3" => "install_timeslot_3",
                "available_speeds" => "available_speeds",
                "lead_status" => "status",
                "how_did_you_hear_about_us" => "coupon_code",
                "referrer_name" => "referring_ecn",
                "alternate_modem_delivery_address" => "alternate_modem_delivery_address",
                "lifecyclestage" => "lifecyclestage",
                "hubspot_owner_id" => "hubspot_owner_id",
                "order_step" => "order_step",
        );
}

function hubspot_sent_info() {

	$data['hubspot_owner_id'] = 33417648; //Diallog
	$data['source'] = "Website availability checker";

	$HUBSPOT_API_KEY = "74e72998-e3cc-4c81-8d91-884453fafbc9";
	$url = HUPSPOT_CONTACT_API_ENDPOINT."createOrUpdate/email/".$email."/?hapikey=".HUBSPOT_API_KEY;
	foreach ($data as $key=>$value) {
		if ($ky = hubspot_api_contact_properties()[$key]) {
			$data_array['properties'][] = array("property"=>$ky,"value"=>$value);
		}
	}

	$payload = json_encode($data_array);

	$res = wp_remote_post( $url, array(
		'method' => 'POST',
		'timeout' => 100,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		/*'headers' => array(),*/
		'body' => $payload,
		'data_format' => 'body'
	)
	);

	if ( is_wp_error( $res ) ) {

		$response['error'] = true;
		$response['msg'] = $res->get_error_message();

	} elseif ($res['response']['code']==200) {

		$response['success'] = true;
		$response['body'] = $res['body'];

	} else {

		$response['error'] = true;
		$response['body'] = $res['body'];

	}

	return $response;

}


?>

