<?php
/**
 * CPanel CSF main controller
 * 
 * @package nirays
 * @subpackage nirays.plugins.cpanel_csf.controllers
 * @copyright Copyright (c) 2013, Nirays Technologies.
 * @license License Agreement
 * @link http://nirays.com/ Nirays
 */
class AdminMain extends CpanelCsfController {

    /**
     * Pre-action
     */
    public function preAction() {
        parent::preAction();
        // Restore structure view location of the client portal
        $this->structure->setDefaultView(APPDIR);
        $this->structure->setView(null, $this->orig_structure_view);


        $this->requireLogin();

        $this->uses(array("cpanel_csf.CpanelCsfDataHandler","cpanel_csf.CpanelCsfSettings"));

        Language::loadLang("main", null, PLUGINDIR . "cpanel_csf" . DS . "language" . DS);
    }

    /**
     * On load for the link
     */
    public function index() {
        // Set some variables to the view
        $this->set("isModuleAvailable", $this->CpanelCsfDataHandler->isCPanelInstalled());

        $this->set("servers",$this->CpanelCsfDataHandler->getAvailableServer());
        $readOnly = $this->CpanelCsfSettings->getReadOnlySetting(true,Configure::get("Blesta.company_id"));
        $server= 0;
		$result= null ;
        if (!empty($this->post)) {
            if($readOnly)
                $this->post['ip'] = $_SERVER['REMOTE_ADDR'];
            $varsRes = $this->CpanelCsfDataHandler->unblock($this->post);
            if($varsRes){
                $server = $this->post['server'];
                if($varsRes["status"]) {
                    $result=$varsRes;
                    $this->setMessage("message", Language::_("CpanelCsfManagePlugin.!success.unblock_ip", true),false,null,false);
                }
                else{
                    $this->setMessage("error", $varsRes["msg"],false,null,false);
                }
            }
            if($readOnly)
                $ip = $_SERVER['REMOTE_ADDR'];
            else
                $ip = "";
        }
        else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
         $vars = array('ip' =>  $ip,
             'server' => $server);
        $this->set("isReadonly",$readOnly);
        $this->set("vars",$vars);
        $this->set("serv",$result);
        //$this->setMessage("message", "sdda");
        //$this->set("serv", $this->CpanelCsfDataHandler->getAvailableServer());
        // Automatically renders the view in /plugins/cpanel_csf/views/default/admin_main.pdt
    }

    /**
     * unlock a IP
     */
    public function unblock() {


    }
}
?>