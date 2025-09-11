<?php

session_start();
if ($_SESSION["user"]) {

	include_once('connect.php');
	include_once('helper.php');

	$mysqli = connect_db();


	// Get the parameters from the query string
	$start = $_GET['start'] ?? null;
	$end   = $_GET['end']   ?? null;

	// Basic validation: make sure they look like dates
	if ($start && $end) {
		$startDate = date('Y-m-d', strtotime($start));
		$endDate   = date('Y-m-d', strtotime($end));
	} else {
		die("Missing start or end");
	}

	// 2. Get filters from GET
	$q      = $_GET['q']     ?? '';
	$status = $_GET['status'] ?? '';
	$from   = $_GET['from']  ?? '';

	// 3. Build query dynamically
	$$sql = "
    SELECT s.*
    FROM signup s
    INNER JOIN (
        SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(user_data, '$.email')) AS email,
            MAX(LastUpdated) AS max_updated
        FROM signup
        WHERE status < 100
";

	$params = [];
	$types  = "";

	// Add date filter
	if ($start && $end) {
		$sql .= " AND LastUpdated BETWEEN ? AND ? ";
		$params[] = $start;
		$params[] = $end;
		$types   .= "ss"; // assuming $start and $end are strings (like '2025-01-01 00:00:00')
	}

	$sql .= "
        GROUP BY JSON_UNQUOTE(JSON_EXTRACT(user_data, '$.email'))
    ) latest
      ON JSON_UNQUOTE(JSON_EXTRACT(s.user_data, '$.email')) = latest.email
     AND s.LastUpdated = latest.max_updated
    LIMIT 1000
";

	$stmt = $mysqli->prepare($sql);
	if ($stmt == false) {
		error_log(" Error in sql $sql");
		error_log("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
		exit(0);
	}
	if ($params) {
		$stmt->bind_param($types, ...$params);
	}

	$stmt->execute();
	$result = $stmt->get_result();

	// 4. CSV headers
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="export.csv"');

	// 5. Output CSV
	$output = fopen('php://output', 'w');

	$fields = ['date', 'first_name', 'last_name', 'email', 'phone', 'street_number', 'street_name', 'postal_code', 'ccd', 'status', 'selected_modem_plan_name', 'selected_Internet Plan_plan', 'selected_Internet Plan_plan_name'];
	// header row
	fputcsv($output, $fields);

	// data rows
	while ($row = $result->fetch_assoc()) {
		$unique_id = $row['unique_id'];
		$user_data = GetUserData($unique_id);

		error_log("$unique_id");
		$line = [];
		foreach ($fields as $f) {
			if ($f == 'date') {
				$line['date'] = $row['LastUpdated'];
				continue;
			}
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
