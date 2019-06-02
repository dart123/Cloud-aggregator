<?
session_start();
$conn = null;

switch($_GET['f']) {
case 'getfiles':
    get_files($_GET['cloud']);
    break;
case 'get_file_cloud':
    get_file_cloud($_GET['filename'], $_GET['modified']);
    break;
case 'save_user':
    if (isset($_POST['btn-signup']))
        save_user($_POST['email'], $_POST['username'], $_POST['password']);
    break;
case 'get_is_folder':
    is_folder($_GET['name'], $_GET['modified']);
    break;
case 'get_token_clouds':
    get_token(0, true);
    break;
case 'delete_token':
    delete_token($_GET['cloud_id']);
    break;
case 'delete_files':
    delete_file(null, null, $_GET['cloud_id']);
    break;
case 'check_login':
    //$file = fopen(__DIR__."/response.json", 'w');
    //fwrite($file, "-1");
    //fclose($file);
    if (isset($_POST['btn-login']))
        check_login($_POST['email'], $_POST['password']);
    break;
case 'logout':
    log_out();
    break;
}

//function debug_to_console( $data ) {
//    $output = $data;
//    if ( is_array( $output ) )
//        $output = implode( ',', $output);
//
//    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
//}
function debug_to_file($data) {
    $file = fopen(__DIR__."/response.json", 'a');
    fwrite($file, $data."\r\n");
    fclose($file);
}
function connect()
{
	global $conn;
	$servername = "localhost";
	$username = "mysql";
	$password = "mysql";
	$dbname = "cloud-aggregator";
		
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn == null)
		//echo "CONN=NULL\n";
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
}
function close_connection()
{
	global $conn;
	$conn->close();
}
//LOGIN///////////////////////////////////////
function log_out()
{
    if ( isset( $_COOKIE[session_name()] ) )
        setcookie( session_name(), "", time()-3600, "/" );
    $_SESSION = array();
    session_destroy();
    $redirect_url = "../login.php";
    $redirect_url = filter_var($redirect_url, FILTER_SANITIZE_URL);
    echo $redirect_url;
    exit;
}
function check_login($email, $password)
{
    global $conn;
    connect();
    $sql = "SELECT user_id, email, username, password FROM users WHERE email='$email'";
	$result = $conn->query($sql) or die(mysqli_error($conn));
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password']) && $result->num_rows==1)
    {
        $user_id = $row['user_id'];
        $sql = "SELECT session_id FROM sessions WHERE user_id='$user_id'";
        $result = $conn->query($sql) or die(mysqli_error($conn));
        $row = $result->fetch_assoc();
        if ($result->num_rows==1)
        {
            $_SESSION['session_id'] = $row['session_id'];
            //debug_to_file("login: session_id (num_rows==1): ".$_SESSION['session_id']);
        }
        else
        {
            $_SESSION['session_id'] = create_session($conn, $user_id, create_settings($conn, 0, 0));
            //debug_to_file("login: session_id (num_rows==1 else): ".$_SESSION['session_id']);
        }
        $redirect_url = "../main_view.php";
    }
    else
    {
        $_SESSION['session_id'] = -1;
        //debug_to_file("login: session_id (!password_verify): ".$_SESSION['session_id']);
        $redirect_url = "../login.php";
    }
    close_connection();
    header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
    exit;
}
//LOGIN///////////////////////////////////////
function save_file_signatures($files, $cloud)
{
	global $conn;
	connect();
	foreach($files as $value):
		switch ($cloud)
		{
			case "yandex":
				$name = $value->name;
				$modified = $value->modified;
                if ($modified)
                {
                    //меняем дату на формат MySQL
                    fix_datetime($modified, 6);
                    $modified = "'$modified'";
                }
                else
                    $modified = "null";
				$size = ($value->size) ? $value->size : 0;
				$isFolder = $value->type == "dir" ? 1 : 0;
				$ext = end(explode(".", $name));
				$ext = $ext ? ($isFolder ? "folder": $ext) :"";
				$cloud_id = 1;
				break;
			case "dropbox":
				$name = $value->name;
				$modified = $value->server_modified;
                if ($modified)
                {
                    fix_datetime($modified, 1);
                    $modified = "'$modified'";
                }
                else
                    $modified = "null";
				$size = ($value->size) ? $value->size : 0;
				$isFolder = $value->{'.tag'} == "folder" ? 1 : 0;
				$ext = end(explode(".", $name));
				$ext = $ext ? ($isFolder ? "folder": $ext) :"";
				$cloud_id = 2;
				break;
			case "box":
				$name = $value->name;
				$modified = $value->modified_at;
                 if ($modified)
                {
                    fix_datetime($modified, 6);
                    $modified = "'$modified'";
                }
                else
                    $modified = "null";
				$size = $value->size;
				$isFolder = $value->type == "folder" ? 1 : 0;
				$ext = end(explode(".", $name));
				$ext = $ext ? ($isFolder ? "folder": $ext) :"";
				$cloud_id = 3;
				break;
		}
        $session_id = $_SESSION['session_id'];
        //Проверяем есть ли этот файл уже в БД
        $sql = "SELECT * FROM filesignatures WHERE filename='$name' AND lastupdate=$modified AND filesize='$size' AND isSplit='0' ".
        "AND isFolder='$isFolder' AND filetype='$ext' AND cloud_id=$cloud_id AND session_id=$session_id";
        $result = $conn->query($sql) or die(mysqli_error($conn));
        if ($result->num_rows == 0)
        {
            $sql = "INSERT INTO filesignatures (filename, lastupdate, filesize, isSplit, isFolder, filetype, cloud_id, session_id)".
            " VALUES ('$name', $modified, '$size', '0', '$isFolder', '$ext', '$cloud_id' ,'$session_id')";
            $result = $conn->query($sql) or die(mysqli_error($conn));
        }
	endforeach;
	close_connection();
}
function create_settings($connection, $viewmode, $sortmode)
{
    $sql = "INSERT INTO settings (viewmode, sortmode)".
		" VALUES ('$viewmode', '$sortmode')";
	$result = $connection->query($sql) or die(mysqli_error($connection));
    return $connection->insert_id;
}
function create_session($connection, $user_id, $setting_id)
{
    $sql = "INSERT INTO sessions (user_id, setting_id)".
		" VALUES ('$user_id', '$setting_id')";
	$result = $connection->query($sql) or die(mysqli_error($connection));
    return $connection->insert_id;
}
function get_current_username($user_id)
{
    global $conn;
    connect();
    if ($user_id)
    {
        $sql = "SELECT username FROM users WHERE user_id='$user_id'";
        $result = $conn->query($sql) or die(mysqli_error($conn));
        $username = array();
        if ($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc())
            {
                array_push($username, $row);
            }
            close_connection();
            return $username[0]["username"];
        }
        else
        {
            close_connection();
            return false;
        }
    }
}
function get_current_user1($connection=null)
{
    if (!$connection)
    {
        global $conn;
        connect();
        $need_to_disconnect = true;
        $connection = $conn;
    }  
    if (isset($_SESSION['session_id']) && $_SESSION['session_id'] != -1)
    {
        $session_id = $_SESSION['session_id'];
        $sql = "SELECT user_id FROM sessions WHERE session_id='$session_id'";
        $result = $connection->query($sql) or die(mysqli_error($connection));
        $user_id = array();
        if ($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc())
            {
                array_push($user_id, $row);
            }
            if ($need_to_disconnect)
                close_connection();
            return $user_id[0]["user_id"];
        }
        else
        {
            if ($need_to_disconnect)
                close_connection();
            return false;
        }
    }
}
function save_user($email, $username, $password)
{
    global $conn;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
	connect();
    $sql = "INSERT INTO users (email, username, password, disk_limit)".
		" VALUES ('$email', '$username', '$hashed_password', '50000000')";
	$result = $conn->query($sql) or die(mysqli_error($conn));
    
    $user_id = $conn->insert_id;
    
    $setting_id = create_settings($conn, 0, 0);
    
    $session_id = create_session($conn, $user_id, $setting_id);
    close_connection();
    $_SESSION['session_id'] = $session_id;
    $redirect_url = "../main_view.php";
    header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
    exit;
}
function save_token($token, $current_folder, $cloud)
{
	global $conn;
    connect();
    $user_id = get_current_user1($conn);
    if ($user_id)
    {
        $sql = "INSERT INTO tokens (token, current_folder, cloud_id, user_id)".
            " VALUES ('$token', '$current_folder', '$cloud', '$user_id')";
        $result = $conn->query($sql) or die(mysqli_error($conn));
        close_connection();
    }
    else
    {
        close_connection();
        return false;
    }
}
function get_token($cloud, $all = false)
{
    global $conn;
    connect();
    $user_id = get_current_user1($conn);
    if ($user_id)
    {
        if (!$all)
        {
            $sql = "SELECT token FROM tokens WHERE cloud_id=$cloud AND user_id=$user_id";
        }
        else
        {
            $sql = "SELECT cloud_id FROM tokens WHERE user_id=$user_id ORDER BY cloud_id ASC";
        }
        $result = $conn->query($sql) or die(mysqli_error($conn));
        $result_token = array();
        close_connection();
        if ($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc())
            {
                array_push($result_token, $row);
            }
            if (!$all)
                return $result_token[0]["token"];
            else
            {
                echo json_encode($result_token);
                return;
            }
        }
        else
        {
            return false;
        }
    }
    else
        return false;
}
function update_token($new_token, $cloud, $current_folder = null)
{
    global $conn;
    connect();
    $user_id = get_current_user1($conn);
    if ($user_id)
    {
        if (!$current_folder && $new_token)
            $sql = "UPDATE tokens SET token='$new_token' WHERE cloud_id='$cloud' AND user_id='$user_id'";
        else
            if ($current_folder && !$new_token)
                $sql = "UPDATE tokens SET current_folder='$current_folder' WHERE cloud_id='$cloud' AND user_id='$user_id'";
        $result = $conn->query($sql) or die(mysqli_error($conn));
        close_connection();
        return true;
    }
    else
        return false;
}
function delete_token($cloud_id)
{
    global $conn;
	connect();
    $user_id = get_current_user1($conn);
    if ($user_id)
    {
        $sql = "DELETE FROM tokens WHERE cloud_id='$cloud_id' AND user_id='$user_id'";
        $result = $conn->query($sql) or die(mysqli_error($conn));
        close_connection();
        echo 1;
    }
    else
        echo 0;
}
function delete_file($filename, $modified, $cloud_id = null)
{
    global $conn;
	connect();
    //if (!$modified)
    //    $modified = "null";
    $session_id = $_SESSION['session_id'];
	if ($filename && !$cloud_id)
    {
        if ($modified != "null")
            $sql = "DELETE FROM filesignatures WHERE filename='$filename' AND lastupdate='$modified' AND session_id='$session_id'";
        else
            $sql = "DELETE FROM filesignatures WHERE filename='$filename' AND lastupdate is null AND session_id='$session_id'";
    }
	else
        if ($cloud_id)
            $sql = "DELETE FROM filesignatures WHERE session_id='$session_id' AND cloud_id='$cloud_id'";
        else
            return false;
	$result = $conn->query($sql) or die(mysqli_error($conn));
    close_connection();
    return true;
}
function get_file_cloud($filename, $modified)
{
    global $conn;
	connect();
	$cloud = array();
    //if (!$modified)
    //    $modified = "null";
	if ($filename)
    {
        $session_id = $_SESSION['session_id'];
        if ($modified != "null")
            $sql = "SELECT cloud_id FROM filesignatures WHERE filename='$filename' AND lastupdate='$modified' AND session_id='$session_id'";
        else
            $sql = "SELECT cloud_id FROM filesignatures WHERE filename='$filename' AND lastupdate is null AND session_id='$session_id'";
    }
	else
		return false;
	$result = $conn->query($sql) or die(mysqli_error($conn));
	if ($result->num_rows > 0)
	{
		while($row = $result->fetch_assoc())
		{
			array_push($cloud, $row);
		}
		echo  $cloud[0]["cloud_id"];
	}
	else
	{
		echo false;
	}
	close_connection();
}
function get_current_folder($cloud)
{
    global $conn;
    connect();
    $user_id = get_current_user1($conn);
    if ($user_id)
    {
        $sql = "SELECT current_folder FROM tokens WHERE user_id=$user_id AND cloud_id=$cloud";
        $result = $conn->query($sql) or die(mysqli_error($conn));
        $current_folder = array();
        close_connection();
        if ($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc())
            {
                array_push($current_folder, $row);
            }
            return $current_folder[0]["current_folder"];
        }
        else
        {
            return false;
        }
    }
    else
        return false;
}
function is_folder($name, $modified)
{
    global $conn;
	connect();
	$is_folder = array();
    //if (!$modified)
    //    $modified = "null";
	if ($name)
    {
        $session_id = $_SESSION['session_id'];
        if ($modified != "null")
            $sql = "SELECT isFolder FROM filesignatures WHERE filename='$name' AND lastupdate='$modified' AND session_id='$session_id'";
        else
            $sql = "SELECT isFolder FROM filesignatures WHERE filename='$name' AND lastupdate is null AND session_id='$session_id'";
    }
	else
		return false;
	$result = $conn->query($sql) or die(mysqli_error($conn));
	if ($result->num_rows > 0)
	{
		while($row = $result->fetch_assoc())
		{
			array_push($is_folder, $row);
		}
		echo  $is_folder[0]["isFolder"];
	}
	else
	{
		echo false;
	}
	close_connection();
}
function get_files($cloud)
{
	global $conn;
	connect();
	$files = array();
    $session_id = $_SESSION['session_id'];
	if ($cloud!=0)
		$sql = "SELECT filename, lastupdate, filesize, isSplit, isFolder FROM filesignatures WHERE cloud_id=$cloud AND session_id=$session_id";
	else
		$sql = "SELECT filename, lastupdate, filesize, isSplit, isFolder FROM filesignatures WHERE session_id=$session_id";
	$result = $conn->query($sql) or die(mysqli_error($conn));
	if ($result->num_rows > 0)
	{
		// output data of each row
		while($row = $result->fetch_assoc())
		{
			array_push($files, $row);
		}
		echo  json_encode($files);
	}
	else
	{
		echo false;
	}
	close_connection();
}
function fix_datetime(&$datetime, $num) //num - сколько символов с конца удалить
{
	$datetime = str_replace("T"," ", $datetime);
	$datetime = str_replace(substr($datetime, - $num), "", $datetime);
}
?>