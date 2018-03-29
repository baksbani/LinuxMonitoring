<?php
function returnJSON(){
        header('Content-type: application/json');
        try{
            $conn = Flight::db();
            $result = $conn->query("SELECT timesubmint, usedcpu, ramusage, hdd, pids FROM data ORDER BY id DESC LIMIT 36");
            $data = array();
                foreach($result as $row) {
                    $data[] = $row;
                }
                echo json_encode($data);
        }catch(Exception $e){
            echo $e;
        }
}

function insertDATA(){
    //Load input from python script
    Flight::set('data', file_get_contents("php://input"));

    $data = json_decode(Flight::get('data'));

    //Set variables
    Flight::set('auth_code', $data->{'auth_code'});
    Flight::set('os_name', $data->{'os_name'});
    Flight::set('os_version', $data->{'os_version'});
    Flight::set('cpu_model', $data->{'cpu_model'});
    Flight::set('cpu_architecture', $data->{'cpu_architecture'});
    Flight::set('cpu_cores', $data->{'cpu_cores'});
    Flight::set('cpu_threads', $data->{'cpu_threads'});
    Flight::set('cpu_percentage', $data->{'cpu_percentage'});
    Flight::set('hostname', $data->{'hostname'});
    Flight::set('internal_ip', $data->{'internal_ip'});
    Flight::set('external_ip', $data->{'external_ip'});
    Flight::set('ram_total', $data->{'ram_total'});
    Flight::set('ram_used', $data->{'ram_used'});
    Flight::set('ram_free', $data->{'ram_free'});
    Flight::set('ram_shared', $data->{'ram_shared'});
    Flight::set('ram_available', $data->{'ram_available'});
    Flight::set('ram_buff', $data->{'ram_buff'});
    Flight::set('swap_total', $data->{'swap_total'});
    Flight::set('swap_used', $data->{'swap_used'});
    Flight::set('swap_free', $data->{'swap_free'});
    Flight::set('total_hdd', $data->{'total_hdd'});
    Flight::set('used_hdd', $data->{'used_hdd'});
    Flight::set('available_hdd', $data->{'available_hdd'});
    Flight::set('pid_running', $data->{'pid_running'});
    Flight::set('uptime', $data->{'uptime'});
    Flight::set('timesubmited', date('Y-m-d H:i:s'));

    //Query
        try{
            $stmt = $pdo->prepare('INSERT INTO data (auth_code, os_name, os_version, cpu_model, cpu_architecture, cpu_cores, cpu_threads, cpu_percentage, hostname, internal_ip, external_ip, ram_total, ram_used, ram_free, ram_shared, ram_available, ram_buff, swap_total, swap_used, swap_free, total_hdd, used_hdd, available_hdd, pid_running, uptime, timesubmited) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([Flight::get('auth_code'), Flight::get('os_name'), Flight::get('os_version'), Flight::get('cpu_model'), Flight::get('cpu_architecture'), Flight::get('cpu_cores'), Flight::get('cpu_threads'), Flight::get('cpu_percentage')]);
            print_r("Successfully sent to DB");
        }catch(Exception $e){
            print_r($e);
        }
    }
?>