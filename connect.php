<?PHP

function connect_db() {
        $servername = "localhost";
        $username = "login";
        $password = "lMumq2XSYzyNxDHb";

        $mysqli = mysqli_init();
        if (!$mysqli) {
                die('mysqli_init failed');
        }

        if (!$mysqli->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 1')) {
                die('Setting MYSQLI_INIT_COMMAND failed');
        }

        if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
                die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
        }

        if (!$mysqli->real_connect($servername, $username, $password, 'signups')) {
                die('Connect Error (' . mysqli_connect_errno() . ') '
                        . mysqli_connect_error());
        }
        return $mysqli;
}

function CleanUpDesc($desc) {
   return preg_replace("/[^a-zA-Z0-9 ]/", "", $desc);
}


?>

