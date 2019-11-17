<?
session_start();

if (isset($_GET['f'])) {
    switch ($_GET['f']) {
        case 'getfiles':
            DBManager::get_files($_GET['cloud']);
            break;
        case 'get_file_cloud':
            DBManager::get_file_cloud($_GET['filename'], $_GET['modified']);
            break;
        case 'save_user':
            if (isset($_POST['btn-signup']))
                DBManager::save_user($_POST['email'], $_POST['username'], $_POST['password']);
            break;
        case 'get_is_folder':
            DBManager::is_folder($_GET['name'], $_GET['modified']);
            break;
        case 'get_current_folder':
            DBManager::get_current_folder($_GET['cloud'], true);
            break;
        case 'get_token_clouds':
            DBManager::get_token(0, true);
            break;
        case 'delete_token':
            DBManager::delete_token($_GET['cloud_id']);
            break;
        case 'delete_files':
            DBManager::delete_file(null, null, $_GET['cloud_id']);
            break;
        case 'check_login':
            if (isset($_POST['btn-login']))
                DBManager::check_login($_POST['email'], $_POST['password']);
            break;
        case 'logout':
            DBManager::log_out();
            break;
    }
}
//DB MANAGER CLASS
class DBManager
{
    private static $conn = null;
    private static $credentials = array(
        "servername" => "localhost",
        "username" => "dart1",
        "password" => "Lasdzvnt.h9,7%=g",
        "dbname" => "cloud_aggregator",
    );

    protected static function debug_to_file($data)
    {
        $file = fopen(__DIR__ . "/response.json", 'a');
        fwrite($file, $data . "\r\n");
        fclose($file);
    }

    private static function connect()
    {
//        global $conn;
        $servername = self::$credentials["servername"];
        $username = self::$credentials["username"];
        $password = self::$credentials["password"];
        $dbname = self::$credentials["dbname"];

        // Create connection
        self::$conn = new mysqli($servername, $username, $password, $dbname);
        if (self::$conn == null)
            echo "CONN=NULL\n";
        // Check connection
        if (self::$conn->connect_error) {
            die("Connection failed: " . self::$conn->connect_error);
        }
    }

    private static function close_connection()
    {
        //global $conn;
        self::$conn->close();
    }

//LOGIN///////////////////////////////////////
    public static function log_out()
    {
        if (isset($_COOKIE[session_name()]))
            setcookie(session_name(), "", time() - 3600, "/");
        $_SESSION = array();
        session_unset();
        session_destroy();
        $redirect_url = "/cloud_aggregator/login.php";
        $redirect_url = filter_var($redirect_url, FILTER_SANITIZE_URL);
        echo $redirect_url;
        exit;
    }

    public static function check_login($email, $password)
    {
        self::connect();
        $sql = "SELECT user_id, email, username, password FROM users WHERE email='$email'";
        $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password']) && $result->num_rows == 1) {
            $user_id = $row['user_id'];
            $sql = "SELECT session_id FROM sessions WHERE user_id='$user_id'";
            $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
            $row = $result->fetch_assoc();
            if ($result->num_rows == 1) {
                $_SESSION['session_id'] = $row['session_id'];
                //debug_to_file("login: session_id (num_rows==1): ".$_SESSION['session_id']);
            } else {
                $_SESSION['session_id'] = self::create_session(/*self::$conn, */$user_id, self::create_settings(/*self::$conn, */0, 0));
                //debug_to_file("login: session_id (num_rows==1 else): ".$_SESSION['session_id']);
            }
            $redirect_url = "/cloud_aggregator/main_view.php";
        } else {
            $_SESSION['session_id'] = -1;
            //debug_to_file("login: session_id (!password_verify): ".$_SESSION['session_id']);
            $redirect_url = "/cloud_aggregator/login.php";
        }
        self::close_connection();
        header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
        exit;
    }

/////////////////////////////////////////
    public static function save_file_signatures($files, $cloud)
    {
        self::connect();
        foreach ($files as $value):
            switch ($cloud) {
                case "yandex":
                    $name = $value->name;
                    $modified = $value->modified;
                    if ($modified) {
                        //меняем дату на формат MySQL
                        self::fix_datetime($modified, 6);
                        $modified = "'$modified'";
                    } else
                        $modified = "null";
                    $size = ($value->size) ? $value->size : 0;
                    $isFolder = $value->type == "dir" ? 1 : 0;
                    $ext = end(explode(".", $name));
                    $ext = $ext ? ($isFolder ? "folder" : $ext) : "";
                    $cloud_id = 1;
                    break;
                case "dropbox":
                    $name = $value->name;
                    $modified = $value->server_modified;
                    if ($modified) {
                        self::fix_datetime($modified, 1);
                        $modified = "'$modified'";
                    } else
                        $modified = "null";
                    $size = ($value->size) ? $value->size : 0;
                    $isFolder = $value->{'.tag'} == "folder" ? 1 : 0;
                    $ext = end(explode(".", $name));
                    $ext = $ext ? ($isFolder ? "folder" : $ext) : "";
                    $cloud_id = 2;
                    break;
                case "box":
                    $name = $value->name;
                    $modified = $value->modified_at;
                    if ($modified) {
                        self::fix_datetime($modified, 6);
                        $modified = "'$modified'";
                    } else
                        $modified = "null";
                    $size = $value->size;
                    $isFolder = $value->type == "folder" ? 1 : 0;
                    $ext = end(explode(".", $name));
                    $ext = $ext ? ($isFolder ? "folder" : $ext) : "";
                    $cloud_id = 3;
                    break;
            }
            $session_id = $_SESSION['session_id'];
            //Проверяем есть ли этот файл уже в БД
            $sql = "SELECT * FROM filesignatures WHERE filename='$name' AND lastupdate=$modified AND filesize='$size' AND isSplit='0' " .
                "AND isFolder='$isFolder' AND filetype='$ext' AND cloud_id=$cloud_id AND session_id=$session_id";
            $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
            if ($result->num_rows == 0) {
                $sql = "INSERT INTO filesignatures (filename, lastupdate, filesize, isSplit, isFolder, filetype, cloud_id, session_id)" .
                    " VALUES ('$name', $modified, '$size', '0', '$isFolder', '$ext', '$cloud_id' ,'$session_id')";
                $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
            }
        endforeach;
        self::close_connection();
    }

    private static function create_settings(/*$connection, */$viewmode, $sortmode)
    {
        self::debug_to_file("viewmode: $viewmode, sortmode: $sortmode");
        $sql = "INSERT INTO settings (viewmode, sortmode)" .
            " VALUES ('$viewmode', '$sortmode')";
        $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
        return self::$conn->insert_id;
    }

    private static function create_session(/*$connection, */$user_id, $setting_id)
    {
        $sql = "INSERT INTO sessions (user_id, setting_id)" .
            " VALUES ('$user_id', '$setting_id')";
        $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
        return self::$conn->insert_id;
    }

    public static function get_current_username($user_id)
    {
        //global $conn;
        self::connect();
        if ($user_id) {
            $sql = "SELECT username FROM users WHERE user_id='$user_id'";
            $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
            $username = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($username, $row);
                }
                self::close_connection();
                return $username[0]["username"];
            } else {
                self::close_connection();
                return false;
            }
        }
    }

    //get_current_user already exists in php thats why '1' is added to the name of the function
    public static function get_current_user1($connection = null)
    {
        $need_to_disconnect = false;
        if (!$connection) {
            //global $conn;
            self::connect();
            $need_to_disconnect = true;
            $connection = self::$conn;
        }
        if (isset($_SESSION['session_id']) && $_SESSION['session_id'] != -1) {
            $session_id = $_SESSION['session_id'];
            $sql = "SELECT user_id FROM sessions WHERE session_id='$session_id'";
            $result = $connection->query($sql) or die(mysqli_error($connection));
            $user_id = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($user_id, $row);
                }
                if ($need_to_disconnect)
                    self::close_connection();
                return $user_id[0]["user_id"];
            } else {
                if ($need_to_disconnect)
                    self::close_connection();
                return false;
            }
        }
    }

    public static function save_user($email, $username, $password)
    {
        //global $conn;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        self::connect();
        self::debug_to_file("email: $email, username: $username, password: $password");
        $sql = "INSERT INTO users (email, username, password, disk_limit)" .
            " VALUES ('$email', '$username', '$hashed_password', '50000000')";
        $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));

        $user_id = self::$conn->insert_id;

        $setting_id = self::create_settings(/*self::$conn,*/ 0, 0);

        $session_id = self::create_session(/*self::$conn, */$user_id, $setting_id);
        self::debug_to_file("user_id: $user_id, setting_id: $setting_id, session_id: $session_id");
        self::close_connection();
        $_SESSION['session_id'] = $session_id;
        $redirect_url = "../main_view.php";
        header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
        exit;
    }

    public static function save_token($token, $current_folder, $cloud)
    {
        //global $conn;
        self::connect();
        $user_id = self::get_current_user1(self::$conn);
        if ($user_id) {
            $sql = "INSERT INTO tokens (token, current_folder, cloud_id, user_id)" .
                " VALUES ('$token', '$current_folder', '$cloud', '$user_id')";
            $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
            self::close_connection();
        } else {
            self::close_connection();
            return false;
        }
    }

    public static function get_token($cloud, $all = false)
    {
        //global $conn;
        self::connect();
        $user_id = self::get_current_user1(self::$conn);
        if ($user_id) {
            if (!$all) {
                $sql = "SELECT token FROM tokens WHERE cloud_id=$cloud AND user_id=$user_id";
            } else {
                $sql = "SELECT cloud_id FROM tokens WHERE user_id=$user_id ORDER BY cloud_id ASC";
            }
            $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
            self::debug_to_file("result: " . print_r($result, true));
            $result_token = array();
            self::close_connection();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($result_token, $row);
                }
                if (!$all)
                    return $result_token[0]["token"];
                else {
                    echo json_encode($result_token);
                    return;
                }
            } else {
                return false;
            }
        } else
            return false;
    }

    public static function update_token($new_token, $cloud, $current_folder = null)
    {
        //global $conn;
        self::connect();
        $user_id = self::get_current_user1(self::$conn);
        if ($user_id) {
            if (!$current_folder && $new_token)
                $sql = "UPDATE tokens SET token='$new_token' WHERE cloud_id='$cloud' AND user_id='$user_id'";
            else
                if ($current_folder && !$new_token)
                    $sql = "UPDATE tokens SET current_folder='$current_folder' WHERE cloud_id='$cloud' AND user_id='$user_id'";
            $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
            self::close_connection();
            return true;
        } else
            return false;
    }

    public static function delete_token($cloud_id)
    {
        //global $conn;
        self::connect();
        $user_id = self::get_current_user1(self::$conn);
        if ($user_id) {
            $sql = "DELETE FROM tokens WHERE cloud_id='$cloud_id' AND user_id='$user_id'";
            $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
            self::close_connection();
            echo 1;
        } else
            echo 0;
    }

    public static function delete_file($filename, $modified, $cloud_id = null)
    {
        //global $conn;
        self::connect();
        //if (!$modified)
        //    $modified = "null";
        $session_id = $_SESSION['session_id'];
        if ($filename && !$cloud_id) {
            if ($modified != "null")
                $sql = "DELETE FROM filesignatures WHERE filename='$filename' AND lastupdate='$modified' AND session_id='$session_id'";
            else
                $sql = "DELETE FROM filesignatures WHERE filename='$filename' AND lastupdate is null AND session_id='$session_id'";
        } else
            if ($cloud_id)
                $sql = "DELETE FROM filesignatures WHERE session_id='$session_id' AND cloud_id='$cloud_id'";
            else
                return false;
        $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
        self::close_connection();
        return true;
    }

    public static function get_file_cloud($filename, $modified)
    {
        //global $conn;
        self::connect();
        $cloud = array();
        //if (!$modified)
        //    $modified = "null";
        if ($filename) {
            $session_id = $_SESSION['session_id'];
            if ($modified != "null")
                $sql = "SELECT cloud_id FROM filesignatures WHERE filename='$filename' AND lastupdate='$modified' AND session_id='$session_id'";
            else
                $sql = "SELECT cloud_id FROM filesignatures WHERE filename='$filename' AND lastupdate is null AND session_id='$session_id'";
        } else
            return false;
        $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($cloud, $row);
            }
            echo $cloud[0]["cloud_id"];
        } else {
            echo false;
        }
        self::close_connection();
    }

    public static function get_current_folder($cloud, $ajax)
    {
        //global $conn;
        self::connect();
        $user_id = self::get_current_user1(self::$conn);
        if ($user_id) {
            $sql = "SELECT current_folder FROM tokens WHERE user_id=$user_id AND cloud_id=$cloud";
            $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
            $current_folder = array();
            self::close_connection();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($current_folder, $row);
                }
                if ($ajax)
                    echo $current_folder[0]["current_folder"];
                else
                    return $current_folder[0]["current_folder"];
            } else {
                if ($ajax)
                    echo "false";
                else
                    return false;
            }
        } else
            return false;
    }

    public static function is_folder($name, $modified)
    {
        //global $conn;
        self::connect();
        $is_folder = array();
        //if (!$modified)
        //    $modified = "null";
        if ($name) {
            $session_id = $_SESSION['session_id'];
            if ($modified != "null")
                $sql = "SELECT isFolder FROM filesignatures WHERE filename='$name' AND lastupdate='$modified' AND session_id='$session_id'";
            else
                $sql = "SELECT isFolder FROM filesignatures WHERE filename='$name' AND lastupdate is null AND session_id='$session_id'";
        } else
            return false;
        $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($is_folder, $row);
            }
            echo $is_folder[0]["isFolder"];
        } else {
            echo false;
        }
        self::close_connection();
    }

    public static function get_files($cloud)
    {
        //global $conn;
        self::connect();
        $files = array();
        $session_id = $_SESSION['session_id'];
        if ($cloud != 0)
            $sql = "SELECT filename, lastupdate, filesize, isSplit, isFolder FROM filesignatures WHERE cloud_id=$cloud AND session_id=$session_id";
        else
            $sql = "SELECT filename, lastupdate, filesize, isSplit, isFolder FROM filesignatures WHERE session_id=$session_id";
        $result = self::$conn->query($sql) or die(mysqli_error(self::$conn));
        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                array_push($files, $row);
            }
            echo json_encode($files);
        } else {
            echo false;
        }
        self::close_connection();
    }

    protected static function fix_datetime(&$datetime, $num) //num - сколько символов с конца удалить
    {
        $datetime = str_replace("T", " ", $datetime);
        $datetime = str_replace(substr($datetime, -$num), "", $datetime);
    }
}
?>