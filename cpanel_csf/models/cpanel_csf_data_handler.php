<?php
/**
 * Import Manager Importer
 *
 * @package blesta
 * @subpackage blesta.plugins.importer_manager.models
 * @copyright Copyright (c) 2010, Phillips Data, Inc.
 * @license http://www.blesta.com/license/ The Blesta License Agreement
 * @link http://www.blesta.com/ Blesta
 */
require_once PLUGINDIR . "cpanel_csf" . DS . "lib" . DS . "csf.php";
class CpanelCsfDataHandler extends CpanelCsfModel {

    /**
     * @var string The amount of time to wait between feed updates (a suitable string for strtotime())
     */
    private static $ip = "NC";
    /**
     * @var int The number of articles to keep per feed (only the latest articles are kept)
     */
    private static $server = 0;

    /**
     * Initialize
     */
    public function __construct() {
        parent::__construct();
        // Load modules for this plugun
        Loader::loadModels($this, array("ModuleManager","Clients", "Services"));
        // Load components required by this plugin
        Loader::loadComponents($this, array("Input","Record"));
        $this->Csf = new Csf();
    }

    /**
     * To Get the selected ip
     */
    public function getSelectedIP(){
        if(self::$ip == "NC"){
            self::$ip = $_SERVER['REMOTE_ADDR'];
        }
        return self::$ip;
    }

    /**
     * To Get the selected ip
     */
    public function getSelectedServer(){
        return self::$server;
    }

    /**
     * To Check if the CPanel module is installed
     * @return bool
     */
    public function isCPanelInstalled(){
        // Get the company ID
        $company_id = Configure::get("Blesta.company_id");
        if (!$this->ModuleManager->isInstalled("Cpanel",$company_id)) {
            return false;
        }
        return true;
    }

    /**
     * To Get all the available server list
     * @return array|bool
     */
    public function getAvailableServer(){
        $module_id = $this->getModuleId();
        if($module_id){
            $servers = array();
            $rows = $this->ModuleManager->getRows($module_id);
            foreach ($rows as $row) {
                $servers[$row->id] = $row->meta->server_name;
            }
            return $servers;
        }
        return false;
    }

    /***
     * To unblock the given ip in given server.
     * @param $ip
     * @param $server
     */
    public function unblock($vars) {
        $result = array();
        $result["status"] = false;
        $result["msg"] = "";
        $ip= trim($vars['ip']);
        $server= trim($vars['server']);
        if("" != $ip) {
            if($this->validateIP($ip)) {
                if(is_numeric($server)) {
                $result["status"] = true; 
                       $row = $this->ModuleManager->getRow( $server );
                            if($row) {
                                if(!empty($row->meta->key)){
                                    $res = $this->Csf->unBlockIpUsingKey(
                                        $row->meta->host_name,
                                        $ip,
                                        $row->meta->user_name,
                                        $row->meta->key,
                                        $row->meta->use_ssl);
                                    if($res){
                                        if($res["status"]){
                                            $result["msg"] = $res["msg"];
                                            $result["msgCode"] = $res["msgCode"];
                                            if( !($res["msgCode"]>=200 &&  $res["msgCode"]<300))
                                            {
                                                $result["status"] = false;
                                                $result["msg"] =  $this->_("CpanelCsfManagePlugin.!error.unauthorized");
                                            }
                                        }
                                        else {
                                            $result["status"] = false;
                                            $result["msg"] = $res["msg"];
                                            $result["msgCode"] = $res["msgCode"];
                                        }
                                    }
                                    else {
                                        $result["status"] = false;
                                        $result["msg"] = $this->_("CpanelCsfManagePlugin.!error.general");
                                    }                                
                            }
                            else {
                                   $result["status"] = false;
                                    $result["msg"] = $this->_("CpanelCsfManagePlugin.!error.general");
                            }
                        }
                        else {
                                   $result["status"] = false;
                                    $result["msg"] = $this->_("CpanelCsfManagePlugin.!error.general");
                            }
                }
            }
            else {
                $result["msg"] =  $this->_("CpanelCsfManagePlugin.!error.ip.valid");
            }
        }
        else {
            $result["msg"] =  $this->_("CpanelCsfManagePlugin.!error.ip.empty");
        }

        return $result;
    }

    /**
     * To get Available Server For Client
     * @param $clientid
     */
    public function getAvailableServerForClient($clientId){
        if (!isset($clientId) || !($client = $this->Clients->get((int)$clientId)))
            return;

        $services = $this->Services->getList($client->id);
        $servers = array();
        foreach ($services as $service) {
            if($service->package) {
                $module_id = $service->package->module_id;
                  //module_row module_id
                if($module_id && $service->package->company_id == Configure::get("Blesta.company_id")){
                    $mod = $this->ModuleManager-> get($module_id,false,flase);
                   if($mod->class=="cpanel"){
                       $row = $this->ModuleManager->getRow( $service->package->module_row );
                       $servers[$row->id] = $row->meta->server_name;
                   }
                }
            }
        }
        return $servers;
    }
    private function getModuleId()
    {
        try {
            // Get the company ID
            $company_id = Configure::get("Blesta.company_id");
            $module = $this->Record->select()->from("modules")->
                where("class", "=", "Cpanel")->
                where("company_id", "=",$company_id)->fetch();
            return $module->id;
        }
        catch (Exception $e) {
            $this->Record->reset();
            //Log here
        }
        return false;
    }
    /**
     * To check if it is a valid IP.
     * @param $ip
     * @return bool
     */
    public function validateIP($ip){
       // return filter_var($ip, FILTER_VALIDATE_IP);
        return  inet_pton($ip);
    }
}
?>