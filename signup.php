<?PHP

header('Content-type: application/json');

$sJson = file_get_contents('php://input');
if($sJson == NULL || strlen($sJson) <= 0) {
   die();
}

$request = json_decode($sJson);
if ($request == NULL || $request->api == NULL ||
      strcmp($request->magic, "Sl2soDSpLAsHqetS") || strcmp($request->api, "1.00") ) {
   die();
}

include_once "connect.php";

function GenerateCompletedOrderID($unique_id) {

        $unique_information = $unique_id . time();

	error_log(" Generating new COMPLETE ORDER ID based on $unique_information " );

        $unique_id = sha1( $unique_information, false );  // md5 in b64

	return $unique_id;

}

function GenerateNewID($user_data) {
	$field1 = $user_data["first_name"];
	$field2 = $user_data["last_name"];
	$field3 = $user_data["email"];
	$field4 = $user_data["date_created"];

        $unique_information = $field1 . $field2 . $field3 . $field4;

	error_log(" Generating new ID based on $unique_information " );

        $unique_id = md5( $unique_information, false );  // md5 in b64

	error_log(" Generated new ID $unique_id");
	return $unique_id;
}

function GetUniqueID( $json_user_data ) {
	$unique_id = "";
	$user_data = json_decode( $json_user_data, true);

        if( isset( $user_data["session_tokens"] ) ) {
                $tokens = $user_data["session_tokens"];
                foreach ( $tokens as $key => $value ) {
                        if(strlen( trim($key) ) > 0 ) {
                                $unique_id = CleanUpDesc( trim($key) );
                                break;
                        }
		}
	} else if( isset( $user_data["signup_id"] ) ) {
		$tokens = trim( $user_data["signup_id"] );
		if( strlen( $tokens ) > 0 ) {
			return $tokens;
		}
        } 

	return GenerateNewID($user_data);
}

if( strcmp($request->method, "addresscheck") == 0 ) {


	$user_data_b64 = $request->user_data;
	if( isset( $request->state ) == false ) {
		error_log( " In newsignup, state is null " );
		exit(0);
	}

	$state = $request->state;
	$user_data = base64_decode( $user_data_b64 ); 

	error_log("Got new addresscheck $user_data ");

	$user_data_decoded = json_decode( $user_data, false );

	if( $user_data_decoded == false )
	{
		error_log("User data is empty");
		return;
	}

	$LastUpdated = "NOW()";
	$timestamp = "NOW()";
	$address = base64_encode ( json_encode( $user_data_decoded->_api_response ) );
	$success = 0;
	$_api_response = $user_data_decoded->_api_response;
	if( $_api_response->error == false ) {
		$success = 1;
	}

	$sql = "INSERT INTO avail_checks (LastUpdated, success, address, user_data, timestamp) VALUES( $LastUpdated, $success, '$address', '$user_data_b64', $timestamp ); ";

	$db = connect_db();
	if( $db == false) {
		error_log("Error connecting to db");
		return;
	}

	$res = $db->query( $sql );
	if( $res == false ) {
		error_log("Error adding entry to db ");
	}

	$response["magic"] = "NJKfRjd6VbbWnjw9";
	$response["status"] = "success";

	$db->close();
	echo json_encode( $response );

	exit(0);
}


if( strcmp($request->method, "newsignup") == 0 ) {

	$user_data_b64 = $request->user_data;
	$cart_b64 = $request->cart;
	$upfront_b64 = "";
	if( isset( $request->upfront_summary ) ) {
		$upfront_b64 = $request->upfront_summary;
	}
	$monthly_b64 = "";
	if( isset( $request->monthly_summary ) ) {
		$monthly_b64 = $request->monthly_summary;
	}

	if( isset( $request->state ) == false ) {
		error_log( " In newsignup, state is null " );
		exit(0);
	}

	$state = $request->state;

	$user_data = base64_decode( $user_data_b64 ); 

	file_put_contents("/var/log/signupsystem.txt", "New Order\n", FILE_APPEND);
	file_put_contents("/var/log/signupsystem.txt", base64_decode( $user_data_b64 ), FILE_APPEND);

	$unique_id = GetUniqueID($user_data);
	if( strlen($unique_id) <= 0 ) {
		error_log("Info from user_data has no valid key in the session tokens");
		$response["magic"] = "NJKfRjd6VbbWnjw9";
		$response["status"] = "error";
		$response["msg"] = "No Valid Session Tokens";
		echo json_encode( $response );
		exit(0);
	}

	error_log( "Got newsignup command, $unique_id, state=$state" );
	$db = connect_db();
	if( $db == false) {
		error_log("Error connecting to db");
		return;
	}

	$sql = "SELECT * FROM signup WHERE unique_id='$unique_id'";
	$res = $db->query($sql);
	if( $res == false ) {
		error_log("sql error in $sql  $db->errno, $db->error");
		$response["magic"] = "NJKfRjd6VbbWnjw9";
		$response["status"] = "error";
		$response["msg"] = "Unable to insert into db";
		$db->close();
		echo json_encode( $response );
		exit(0);
	}

	error_log(" Finding if entry exists $sql");
	if( $res->num_rows > 0 ) {
		$item = $res->fetch_object();
		error_log(" entry unique id = $unique_id, exists id: " . $item->id);
		if( $item->status > 100 ) { // already paid and in process, create new id

			// TODO: does not work because generates the same ID
			error_log(" The entry status is over 100, so we will create a new unique id " . $unique_id );
			$unique_id = GenerateNewID( json_decode( $user_data, true ) );
			error_log(" New unique id " . $unique_id );
			goto addnew;

		} else {

			if( $state == 100 ) {
				// order complete .... 
				// setting the uniqueid to new id
				$new_unique_id = GenerateCompletedOrderID($unique_id);
				$sql = "UPDATE signup SET status=$state, monthly='$monthly_b64', upfront='$upfront_b64', " .
					" unique_id='$new_unique_id', user_data='', cart='', lastupdate=NOW(), signup_update=NOW() WHERE unique_id='$unique_id' ";

				error_log( $sql );

				$res = $db->query($sql);
				if( $res == false ) {
					error_log("sql error in $sql  $db->errno, $db->error");
					$response["magic"] = "NJKfRjd6VbbWnjw9";
					$response["status"] = "error";
					$response["msg"] = "Unable to insert into db";

					$db->close();
					echo json_encode( $response );
					exit(0);
				}

				$path = "/home/ubuntu/signups";
				$file1 = "$path/$new_unique_id.user_data";
				file_put_contents( $file1, $user_data_b64 );
				$file1 = "$path/$new_unique_id.cart";
				file_put_contents( $file1, $cart_b64 );

				error_log(" Updated the user data, $new_unique_id");

				$response["magic"] = "NJKfRjd6VbbWnjw9";
				$response["status"] = "success";
				$response["sid"] = "$unique_id";
				$response["msg"] = "Updated Entry";

				$db->close();
				echo json_encode( $response );

			} else { 

				$sql = "UPDATE signup SET status=$state, monthly='$monthly_b64', upfront='$upfront_b64', " .
					" user_data='', cart='', lastupdate=NOW(), signup_update=NOW() WHERE unique_id='$unique_id' ";

				error_log( $sql );

				$res = $db->query($sql);
				if( $res == false ) {
					error_log("sql error in $sql  $db->errno, $db->error");
					$response["magic"] = "NJKfRjd6VbbWnjw9";
					$response["status"] = "error";
					$response["msg"] = "Unable to insert into db";

					$db->close();
					echo json_encode( $response );
					exit(0);
				}

				$path = "/home/ubuntu/signups";
				$file1 = "$path/$unique_id.user_data";
				file_put_contents( $file1, $user_data_b64 );
				$file1 = "$path/$unique_id.cart";
				file_put_contents( $file1, $cart_b64 );

				error_log(" Updated the user data, $unique_id");

				$response["magic"] = "NJKfRjd6VbbWnjw9";
				$response["status"] = "success";
				$response["sid"] = "$unique_id";
				$response["msg"] = "Updated Entry";

				$db->close();
				echo json_encode( $response );
			}

			exit(0);
		}
	}

addnew:

	$sql = "INSERT INTO signup (status, unique_id, lastupdate, LastUpdated, signup_update, hupspot_update, user_data, cart, monthly, upfront) VALUES " . 
			"($state, '$unique_id', NOW(), NOW(), NOW(), SUBDATE(CURDATE(), INTERVAL 1 DAY), '', '', '$monthly_b64', '$upfront_b64' )";
	$res = $db->query($sql);
	if( $res == false ) {
		error_log("sql error in $sql  $db->errno, $db->error");
		$response["magic"] = "NJKfRjd6VbbWnjw9";
		$response["status"] = "error";
		$response["msg"] = "Unable to insert into db";
		$db->close();
		echo json_encode( $response );
		exit(0);
	}

	$path = "/home/ubuntu/signups";
	$file1 = "$path/$unique_id.user_data";
	file_put_contents( $file1, $user_data_b64 );
	$file1 = "$path/$unique_id.cart";
	file_put_contents( $file1, $cart_b64 );

	error_log(" Inserted the user data, $unique_id");

	$response["magic"] = "NJKfRjd6VbbWnjw9";
	$response["status"] = "success";
	$response["sid"] = "$unique_id";
	$response["msg"] = "Added new entry";

	$db->close();
	echo json_encode( $response );

	exit(0);
}

?>


