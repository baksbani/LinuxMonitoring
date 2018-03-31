<?php
class PersistenceManager{

  protected $pdo;

  public function __construct($params){
    try{
      $this->pdo = new PDO("mysql:host=".$params['host'].";dbname=".$params['schema'].";charset=utf8", $params['username'], $params['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    }catch(PDOException  $e ){
      echo "Error: ".$e;
    }
  }

  private function execute($query, $params){
    $prepared_statement = $this->pdo->prepare($query);
    if ($params){
      foreach($params as $key => $param){
        $prepared_statement->bindValue($key, $param);
      }
    }
    $prepared_statement->execute();
    return $prepared_statement;
  }
  private function execute_insert($table, $record){
    $insert = 'INSERT INTO '.$table.' ('.implode(", ", array_keys($record)).')VALUES (:'.implode(", :",  array_keys($record)).')';
    $prepared_statement = $this->execute($insert, $record);
    return $this->pdo->lastInsertId();
  }
  public function query($query, $params){
    $prepared_statement = $this->execute($query, $params);
    return $prepared_statement->fetchAll();
  }
  public function query_single($query, $params){
    $result = $this->query($query, $params);
    return reset($result);
  }
  public function update($query, $params){
    $prepared_statement = $this->execute($query, $params);
  }

  public function get_user_by_email($email){
    return $this->query_single('SELECT * FROM users WHERE email = :email', [':email' => $email]);
  }

  public function get_user_by_id($email){
    return $this->query_single('SELECT id FROM users WHERE email = :email', [':email' => $email]);
  }

  public function update_user_by_email($email, $image, $google_id, $name){
    $this->execute('UPDATE users SET image=:image, google_id=:google_id, name = :name WHERE email = :email', [
      ':email' => $email,
      ':image' => $image,
      ':google_id' => $google_id,
      ':name' => $name
    ]);
  }

  public function get_valid_auth($auth){
    return $this->query_single('SELECT * FROM servers WHERE auth_code = :auth', [':auth' => $auth]);
  }

  public function insert_monitor_data($auth_code, $os_name, $os_version, $cpu_model, $cpu_architecture, $cpu_cores, $cpu_threads, $cpu_percentage, $hostname, $internal_ip, $external_ip, $ram_total, $ram_used, $ram_free, $ram_shared, $ram_available, $ram_buff, $swap_total, $swap_used, $swap_free, $total_hdd, $used_hdd, $available_hdd, $pid_running, $uptime, $timesubmited){
    return $this->execute('INSERT INTO Monitoring (auth_code, os_name, os_version, cpu_model, cpu_architecture, cpu_cores, cpu_threads, cpu_percentage, hostname, internal_ip, external_ip, ram_total, ram_used, ram_free, ram_shared, ram_available, ram_buff, swap_total, swap_used, swap_free, total_hdd, used_hdd, available_hdd, pid_running, uptime, timesubmited) VALUES (:authcode, :osname, :osversion, :cpumodel, :cpuarch, :cpucores, :cputhread, :cpupercentage, :hostname, :internalip, :externalip, :ramtotal, :ramused, :ramfree, :ramshared, :ramavailable, :rambuff, :swaptotal, :swapused, :swapfree, :totalhdd, :usedhdd, :availablehdd, :pidrunning, :uptime, :timesubmited)', [
      ':authcode' => $auth_code,
      ':osname' => $os_name,
      ':osversion' => $os_version,
      ':cpumodel' => $cpu_model,
      ':cpuarch' => $cpu_architecture,
      ':cpucores' => $cpu_cores,
      ':cputhread' => $cpu_threads,
      ':cpupercentage' => $cpu_percentage,
      ':hostname' => $hostname,
      ':internalip' => $internal_ip,
      ':externalip' => $external_ip,
      ':ramtotal' => $ram_total,
      ':ramused' => $ram_used,
      ':ramfree' => $ram_free,
      ':ramshared' => $ram_shared,
      ':ramavailable' => $ram_available,
      ':rambuff' => $ram_buff,
      ':swaptotal' => $swap_total,
      ':swapused' => $swap_used,
      ':swapfree' => $swap_free,
      ':totalhdd' => $total_hdd,
      ':usedhdd' => $used_hdd,
      ':availablehdd' => $available_hdd,
      ':pidrunning' => $pid_running,
      ':uptime' => $uptime,
      ':timesubmited' => $timesubmited
    ]);
  }
}

?>
