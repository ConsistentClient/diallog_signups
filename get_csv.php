<?php

session_start();
if ($_SESSION["user"]) {

	include_once('connect.php');
	include_once('helper.php');

	$mysqli = connect_db();

	// Get the parameters from the query string
	$start = $_GET['start'] ?? null;
	$end = $_GET['end'] ?? null;

	// Basic validation: make sure they look like dates
	if ($start && $end) {
		$startDate = date('Y-m-d', strtotime($start));
		$endDate = date('Y-m-d', strtotime($end));
	} else {
		die("Missing start or end");
	}

	// 2. Get filters from GET
	$q = $_GET['q'] ?? '';
	$status = $_GET['status'] ?? '';
	$from = $_GET['from'] ?? '';
	$limit = 10000;

	// 4. CSV headers
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="export.csv"');

	// 3. Build query dynamically
	$stmt = $mysqli->prepare("
    SELECT s.*, s.LastUpdated
    FROM signup s
    WHERE s.LastUpdated BETWEEN ? AND ?
    LIMIT $limit
");
	//      AND s.status < 100

	$stmt->bind_param("ss", $start, $end);
	$stmt->execute();
	$result = $stmt->get_result();

	/*
	$users = [];
	$latestPerEmail = [];

	while ($row = $result->fetch_assoc()) {
		$unique_id = $row['unique_id'];
		$user_data = GetUserData($unique_id);

		if( isset( $user_data['email']) == false )
			continue;

		$user_data['date'] = $row['LastUpdated'];
		$user_data['status'] = $row['status'];

		$user_data['email'] = strtolower(trim($user_data['email']));
		$email = $user_data['email'];

		if (!isset($latestPerEmail[$email]) || $row['LastUpdated'] > $latestPerEmail[$email]['date']) {
			$latestPerEmail[$email] = $user_data;
			$latestPerEmail[$email]['email'] = $email; // add email for convenience
		}
	}

	$users = array_slice($latestPerEmail, 0, $limit);
	*/

	$latestPerEmail = [];
	$latestPerPostal = [];

	while ($row = $result->fetch_assoc()) {
		$unique_id = $row['unique_id'];
		$user_data = GetUserData($unique_id);

		// Require email, skip if missing
		if (empty($user_data['email'])) {
			continue;
		}

		$user_data['email'] = strtolower(trim($user_data['email']));
		$user_data['postal_code'] = isset($user_data['postal_code']) ? trim($user_data['postal_code']) : '';
		$user_data['date'] = $row['LastUpdated'];
		$user_data['status'] = $row['status'];

		$email = $user_data['email'];
		$postal = $user_data['postal_code'];

		// --- Always enforce uniqueness by email ---
		if (
			!isset($latestPerEmail[$email]) ||
			strtotime($row['LastUpdated']) > strtotime($latestPerEmail[$email]['date'])
		) {
			$latestPerEmail[$email] = $user_data;
		}

		// --- Enforce uniqueness by postal code only if postal is not empty ---
		if ($postal !== '') {
			if (
				!isset($latestPerPostal[$postal]) ||
				strtotime($row['LastUpdated']) > strtotime($latestPerPostal[$postal]['date'])
			) {
				$latestPerPostal[$postal] = $user_data;
			}
		}
	}

	// Merge results
	$latestCombined = array_merge($latestPerEmail, $latestPerPostal);

	// Deduplicate (in case same record is in both lists)
	$latestCombined = array_values(array_unique($latestCombined, SORT_REGULAR));

	// Sort by date descending
	usort($latestCombined, function ($a, $b) {
		return strtotime($b['date']) <=> strtotime($a['date']);
	});

	// Apply limit
	$users = array_slice($latestCombined, 0, $limit);

	// 5. Output CSV
	$output = fopen('php://output', 'w');
	$fields = ['date', 'first_name', 'last_name', 'email', 'phone', 'street_number', 'street_name', 'postal_code', 'ccd', 'status', 'selected_modem_plan_name', 'selected_Internet Plan_plan', 'selected_Internet Plan_plan_name'];
	fputcsv($output, $fields);
	foreach ($users as $user_data) {
		$line = [];
		if ($user_data['status'] >= 100)
			continue;
		foreach ($fields as $f) {
			if ($f == 'ccd') {
				if (isset($user_data[$f])) {
					if (substr($user_data[$f], -2) === '==') {
						$line['ccd'] = base64_decode($user_data[$f]);
						continue;
					}
				}
			}

			if (isset($user_data[$f]))
				$line[$f] = $user_data[$f];
			else
				$line[$f] = "";
		}

		fputcsv($output, $line);
	}

	fclose($output);
	$stmt->close();
	$mysqli->close();
	exit;
}
