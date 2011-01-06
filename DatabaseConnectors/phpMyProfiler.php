<?php
/**
 * Class to grab query profiling data from MySQL and log it to a file
 * for later examination.  Modified by Steve Kamerman for better
 * operation in evaluating Tera-WURFL performance.
 * 
 * Originally downloaded from http://www.php-trivandrum.org/open-php-myprofiler/
 */
class phpMyProfiler{
	
    private $link;
    private $error;
    private $log;
    
    function __construct(MySQLi &$link, $log = false){
        $this->log = $log;
        if(!$this->log) return;
        $this->link =& $link;
        $this->startProfiling();
    }

    function setLink(&$link){
		$this->link = $link;
		$this->stopProfiling();
		$this->startProfiling();
    }

    function __destruct(){
        $this->log();
    }
    private function startProfiling(){
        $this->link->query('set profiling_history_size=100') or die($this->link->error);
        $this->link->query('set profiling=1') or die($this->link->error);
        $res = $this->link->query("show variables like 'profiling'");
        $row = $res->fetch_assoc();
        if($row['Value'] == "OFF"){
        	throw new Exception("Cannot enable profiling in MySQL!");
        }
    }
    private function stopProfiling(){
        $this->link->query('set profiling=0') or die($this->link->error);
    }
    
    private function collectData(){
        $rv = array();
        $rs = $this->link->query('show profiles') or die("Error: ".$this->link->error);
   		if($rs->num_rows == 0) return;
        while($rd = $rs->fetch_assoc()){
            if($rd['Query_ID'] == 0) continue;
            if($detail = $this->getDetails($rd['Query_ID']))
                $rd['detail'] = $detail;
            $rv[] = $rd;
        }
		$rs->free_result();
        return $rv;
    }
    
    private function getDetails($qid){
            $rsd = $this->link->query('select min(seq) seq,state,count(*) numb_ops, '
                . 'round(sum(duration),5) sum_dur, round(avg(duration),5) avg_dur, '
                . 'round(sum(cpu_user),5) sum_cpu, round(avg(cpu_user),5) avg_cpu '
                . 'from information_schema.profiling '
                . 'where query_id = ' . $qid
                . ' group by state order by seq') or die($this->link->error);
		    if($rsd->num_rows == 0) return;
		    $rsv = array();
            while($rdd = $rsd->fetch_assoc()){
                $rsv[] = $rdd;
            }
            return $rsv;
    }
    
    public function log(){
        if(!$this->link or !$this->log){
        	return;
        }
        $this->stopProfiling();
        $logFile = $this->log . $_SERVER['HTTP_HOST'] .'-' . date('Ymd-G') . '.log';
    	if(!file_exists($logFile)){
            file_put_contents($logFile, '#PhpMyProfiler' . "\n");
        }
        $data['instance'] = array('timestamp' => time(), 'request' => $_SERVER['REQUEST_URI' ]);
	//ob_start();
        $data['profiles'] = $this->collectData();
        //die(var_export($data,true));
	//ob_get_clean();
        if(empty($data['profiles']) or count($data['profiles']) == 0){
        	touch($this->log.'NOPROFILE');
        	return;
        }
        $logData = base64_encode(gzcompress(serialize($data))) . "\n";
        file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);
        $this->log = false; // dont want to call a second time
    }
}
?>
