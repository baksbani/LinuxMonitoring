<?php
use \Firebase\JWT\JWT;

require_once '../../vendor/autoload.php';
require_once '../PersistenceManager.class.php';
require_once '../../../Config.class.php';

Flight::register('pm', 'PersistenceManager', [Config::DB]);
Flight::register('google', 'League\OAuth2\Client\Provider\Google', [Config::GOOGLE]);

Flight::set('flight.base_url', '/');

Flight::route('/', function () {
    echo 'Hello';
});

// Function return all active servers that belong to specific user
Flight::route('GET /server/@id', function ($id) {
    $data = Flight::pm()->query("SELECT s.server_id, s.server_name, m.os_name, m.os_version, m.external_ip, m.auth_code FROM servers s INNER JOIN Monitoring m ON s.auth_code = m.auth_code INNER JOIN ( SELECT max(id) max_id, os_name, os_version, auth_code FROM Monitoring GROUP BY os_name, os_version, auth_code ) t ON t.max_id = m.id WHERE s.user_id = :id ", [':id' => $id]);
    Flight::json($data);
});

// Function return all servers that belong to specific user to Get auth codes page
Flight::route('GET /getauthcodes/@id', function ($id) {
    $data = Flight::pm()->query("SELECT server_id, server_name, auth_code FROM servers WHERE user_id = :id ", [':id' => $id]);
    Flight::json($data);
});

//Function that return data for selected servers
Flight::route('GET /getmonitordata/@auth', function ($auth) {
    $data = Flight::pm()->query("SELECT * FROM Monitoring WHERE auth_code = :auth ORDER BY id DESC LIMIT 1", [':auth' => $auth]);
    Flight::json($data);
});

//Function that return data for selected servers
Flight::route('GET /getdataforedit/@auth', function ($auth) {
    $data = Flight::pm()->query("SELECT * FROM servers WHERE auth_code = :auth", [':auth' => $auth]);
    Flight::json($data);
});

// Function that create new server and return auth to sweetalert
Flight::route('GET /crateserver/@servername/@userid', function ($servername, $userid) {
    $rand_auth = uniqid();
    $status = "greskica";
    $unos = Flight::pm()->add_new_server($rand_auth, $userid, $servername);
    if ($unos) {
        $status = $rand_auth;
    } else {
        $status = "greskica";
    }
    Flight::json($status);
});

// Function return number of active servers by that user
Flight::route('GET /serverbynum/@id', function ($id) {
    $data = Flight::pm()->query("SELECT * FROM servers WHERE user_id = :id ", [':id' => $id]);
    $row_cnt = count($data);
    Flight::json($row_cnt);
});

//Post data from python
Flight::route('POST /endpoint', function () {
    $auth_code = Flight::request()->data->auth_code;
    $os_name = Flight::request()->data->os_name;
    $os_version = Flight::request()->data->os_version;
    $cpu_model = Flight::request()->data->cpu_model;
    $cpu_architecture = Flight::request()->data->cpu_architecture;
    $cpu_cores = Flight::request()->data->cpu_cores;
    $cpu_threads = Flight::request()->data->cpu_threads;
    $cpu_percentage = Flight::request()->data->cpu_percentage;
    $hostname = Flight::request()->data->hostname;
    $internal_ip = Flight::request()->data->internal_ip;
    $external_ip = Flight::request()->data->external_ip;
    $ram_total = Flight::request()->data->ram_total;
    $ram_used = Flight::request()->data->ram_used;
    $ram_free = Flight::request()->data->ram_free;
    $ram_shared = Flight::request()->data->ram_shared;
    $ram_available = Flight::request()->data->ram_available;
    $ram_buff = Flight::request()->data->ram_buff;
    $swap_total = Flight::request()->data->swap_total;
    $swap_used = Flight::request()->data->swap_used;
    $swap_free = Flight::request()->data->swap_free;
    $total_hdd = Flight::request()->data->total_hdd;
    $used_hdd = Flight::request()->data->used_hdd;
    $available_hdd = Flight::request()->data->available_hdd;
    $pid_running = Flight::request()->data->pid_running;
    $uptime = Flight::request()->data->uptime;
    $timesubmited = date('Y-m-d H:i:s');

    $unos = Flight::pm()->insert_monitor_data($auth_code, $os_name, $os_version, $cpu_model, $cpu_architecture, $cpu_cores, $cpu_threads, $cpu_percentage, $hostname, $internal_ip, $external_ip, $ram_total, $ram_used, $ram_free, $ram_shared, $ram_available, $ram_buff, $swap_total, $swap_used, $swap_free, $total_hdd, $used_hdd, $available_hdd, $pid_running, $uptime, $timesubmited);
    if ($unos) {
        print_r("Successfully sent to DB");
    } else {
        print_r("Error");
    }
});

//Check if user exists
Flight::route('POST /checkemail', function () {
    $email = Flight::request()->data->email;
    $result = Flight::pm()->get_user_by_email($email);
    if ($result) {
        print_r("emailcheck_working");
    } else {
        print_r("No user");
    }
});

//Check if auth file is valid
Flight::route('POST /checkauth', function () {
    $auth = Flight::request()->data->auth;
    $auth_result = Flight::pm()->get_valid_auth($auth);
    if ($auth_result) {
        print_r("auth_result_working");
    } else {
        print_r("nula");
    }
});

Flight::route('POST /login', function () {
    $email = Flight::request()->data->email;
    $user = Flight::pm()->get_user_by_email($email);
    if ($user) {
        $url = Flight::google()->getAuthorizationUrl();
        $redirect_uri = $url.'&login_hint='.$email;
        $user['redirect_uri'] = $redirect_uri;
        Flight::json($user);
    } else {
        Flight::halt(404, Flight::json(['error' => 'Email does not exist']));
    }
});

Flight::route('GET /redirect', function () {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    try {
        $code = Flight::request()->query->code;
        $access_token = Flight::google()->getAccessToken('authorization_code', ['code' => $code]);
        $owner = Flight::google()->getResourceOwner($access_token);
        Flight::pm()->update_user_by_email($owner->getEmail(), str_replace('?sz=50', '?sz=250', $owner->getAvatar()), $owner->getId(), $owner->getName());
        $user = Flight::pm()->get_user_by_email($owner->getEmail());
        $token = ["user" => $user, "iat" => time(), "exp" => time() + 2592000 /*30 days*/];
        $jwt = JWT::encode($token, Config::JWT_SECRET);
        Flight::redirect('/redirect.html?t='.$jwt);
    } catch (Exception $e) {
        print_r($e);
    }
});

Flight::route('POST /decode', function () {
    try {
        $token = Flight::request()->data->token;
        $user = (array)JWT::decode($token, Config::JWT_SECRET, ['HS256'])->user;
        Flight::json($user);
    } catch (Exception $e) {
        Flight::halt(500, Flight::json(['error' => $e->getMessage()]));
    }
});

Flight::route('GET /user/@email', function ($email) {
    $email = Flight::request()->data->email;
    $user = Flight::pm()->get_user_by_email($email);
    if ($user) {
        Flight::json($user);
    } else {
        Flight::halt(404, Flight::json(['error' => 'Email does not exist']));
    }
});

Flight::route('GET|POST /new_webhook_page', function () {
    $request = file_get_contents("php://input");
    $file = "file.txt";
    file_put_contents($file, $request, FILE_APPEND | LOCK_EX);
    $input = json_decode($request, true);

    if ($input['event'] == 'webhook') {
        $webhook_response['status']=0;
        $webhook_response['status_message']="ok";
        $webhook_response['event_types']='delivered';
        echo json_encode($webhook_response);
        die;
    } elseif ($input['event'] == "subscribed") {
        // when a user subscribes to the public account
    } elseif ($input['event'] == "conversation_started") {
        // when a conversation is started
    } elseif ($input['event'] == "message") {
        /* when a user message is received */
        $type = $input['message']['type']; //type of message received (text/picture)
        $text = $input['message']['text']; //actual message the user has sent
        $text_message = explode(' ', $text);
        $command = $text_message[0];
        $function = $text_message[1];
        $param = $text_message[2];
        $sender_id = $input['sender']['id']; //unique viber id of user who sent the message
        $sender_name = $input['sender']['name']; //name of the user who sent the message

    if ($command == "#log" and $function == "server") {
        $logdata = Flight::pm()->get_last_log($param);
        if ($logdata) {
            $cpu_percentage = $logdata['cpu_percentage'];
            $ram_used = $logdata['ram_used'];
            $swap_used = $logdata['swap_used'];
            $used_hdd = $logdata['used_hdd'];
            $timesubmited = $logdata['timesubmited'];

            // here goes the data to send message back to the user
            $data['auth_token'] = "47aae3f0eb27d575-ceccfc2bcc198821-18a39192cfe80625";
            $data['receiver'] = $sender_id;
            $data['text'] = "Last log: ".$timesubmited."\n--------------------\nCPU Usage: ".$cpu_percentage." %\nRAM Usage: ".$ram_used." GB\nSWAP Used: ".$swap_used." GB\nHDD Used: ".$used_hdd." GB";
            $data['type'] = 'text';

            //here goes the curl to send data to user
            $ch = curl_init("https://chatapi.viber.com/pa/send_message");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
        } else {
            Flight::halt(404, Flight::json(['error' => 'Error']));
        }
    } else {
        // here goes the data to send message back to the user
        $data['auth_token'] = "47aae3f0eb27d575-ceccfc2bcc198821-18a39192cfe80625";
        $data['receiver'] = $sender_id;
        $data['text'] = "I can not recognize that command human.";
        $data['type'] = 'text';

        //here goes the curl to send data to user
        $ch = curl_init("https://chatapi.viber.com/pa/send_message");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
    }
    }
});

Flight::route('GET|POST /set_webhook', function () {
    $url = 'https://chatapi.viber.com/pa/set_webhook';
    $jsonData='{ "auth_token": "47aae3f0eb27d575-ceccfc2bcc198821-18a39192cfe80625", "url": "https://monitor.biznet.ba/rest/v1/new_webhook_page" }';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $result = curl_exec($ch);
    curl_close($ch);
});

Flight::start();
