<?php
/**
 * CPanel for Clients to unblock the blocked IP.
 * 
 * @package nirays
 * @subpackage nirays.plugins.cpanel_csf
 * @copyright Copyright (c) 2013, Nirays Technologies.
 * @license License Agreement
 * @link http://nirays.com/ Nirays
 */
class CpanelCsfPlugin extends Plugin {

	public function __construct() {
		Language::loadLang("cpanel_csf_plugin", null, dirname(__FILE__) . DS . "language" . DS);
		
		// Load components required by this plugin
		Loader::loadComponents($this, array("Input","Record"));
		
        // Load modules for this plugun
        Loader::loadModels($this, array("ModuleManager"));
		$this->loadConfig(dirname(__FILE__) . DS . "config.json");
	}
	
    /**
     * Performs any necessary bootstraping actions
     *
     * @param int $plugin_id The ID of the plugin being installed
     */
    public function install($plugin_id) {

        // Get the company ID
        $company_id = Configure::get("Blesta.company_id");
        // Check if Cpanel is installed if not installation fail.
        if (!$this->ModuleManager->isInstalled("Cpanel",$company_id)) {
        $this->Input->setErrors(array(
            'cpanel' => array(
                'invalid' => Language::_("CpanelCsfPlugin.!error.install", true)
            )
        ));
        return;
        }
        if (!isset($this->Record))
            Loader::loadComponents($this, array("Record"));
        // Add the CMS tables, *IFF* not already added
        try {
            $this->Record->
                setField("allow_admin", array('type'=>"int", 'size'=>1))->
                setField("company_id", array('type'=>"int", 'size'=>10, 'unsigned'=>true))->
                setField("allow_client", array('type'=>"int", 'size'=>1))->
                setKey(array("company_id"), "primary")->
                create("cpanel_csf", true);

            // Install default index page
            $vars = array(
                'allow_admin' => 1,
                'company_id' => $company_id,
                'allow_client' => 0
            );
            // Add the index page
            $fields = array("allow_admin", "company_id", "allow_client");
            try {
                // Attempt to add the page
                $this->Record->insert("cpanel_csf", $vars, $fields);
            }
            catch (Exception $e) {
                // Do nothing; re-use the existing entry
            }
        }
        catch(Exception $e) {
            // Error adding... no permission?
            $this->Input->setErrors(array('db'=> array('create'=>$e->getMessage())));
            return;
        }
    }
    /**
     * Performs migration of data from $current_version (the current installed version)
     * to the given file set version
     *
     * @param string $current_version The current installed version of this plugin
     * @param int $plugin_id The ID of the plugin being upgraded
     */
    public function upgrade($current_version, $plugin_id) {

        // Upgrade if possible
        if ($current_version == "1.0.0") {
            // Handle the upgrade, set errors using $this->Input->setErrors() if any errors encountered
            $this->install(0);
        }
    }
    /**
     * Performs any necessary cleanup actions
     *
     * @param int $plugin_id The ID of the plugin being uninstalled
     * @param boolean $last_instance True if $plugin_id is the last instance across all companies for this plugin, false otherwise
     */
    public function uninstall($plugin_id, $last_instance) {
        if (!isset($this->Record))
            Loader::loadComponents($this, array("Record"));

        // Remove all tables *IFF* no other company in the system is using this plugin
        if ($last_instance) {
            try {
                $this->Record->drop("cpanel_csf");
            }
            catch (Exception $e) {
                // Error dropping... no permission?
                $this->Input->setErrors(array('db'=> array('create'=>$e->getMessage())));
                return;
            }
        }
    }

    /**
     * Returns all actions to be configured for this widget (invoked after install() or upgrade(), overwrites all existing actions)
     *
     * @return array A numerically indexed array containing:
     * 	- action The action to register for
     * 	- uri The URI to be invoked for the given action
     * 	- name The name to represent the action (can be language definition)
     */
    public function getActions() {
        return array(
            array(
                'action' => "nav_secondary_staff",
                'uri' => "plugin/cpanel_csf/admin_main/",
                'name' => Language::_("CpanelCsfPlugin.unblock", true),
                'options' => array('parent' => "tools/")
            ),
            array(
                'action' => "nav_primary_client",
                'uri' => "plugin/cpanel_csf/client_main/",
                'name' => Language::_("CpanelCsfPlugin.unblock", true)
            )
        );
    }
}
?>