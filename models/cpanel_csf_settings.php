<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kirant400
 * Date: 13/11/13
 * Time: 16:53
 * To change this template use File | Settings | File Templates.
 */

class CpanelCsfSettings extends CpanelCsfModel {

    /**
     * Initialize
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Fetches config for the given company
     *
     * @param int $company_id The ID of the company the page belongs to
     * @return mixed An stdClass object representing the Configuration, or false if none exist
     */
    public function get($company_id) {
        return $this->Record->select()->from("cpanel_csf")->
            where("company_id", "=", $company_id)->
            fetch();
    }

    /**
     * To check if we need to make
     * @param $is_admin
     * @param $company_id
     * @return mixed
     */
    public function getReadOnlySetting($is_admin, $company_id) {
        $data = $this->get($company_id);
        if($is_admin) {
            if($data->allow_admin == 0)
                return true;
            else
                return false;
        }
        else {
            if($data->allow_client == 0)
                return true;
            else
                return false;
        }
        return false;
    }
    /**
     * Adds a new CMS page or updates an existing one
     *
     * @param array $vars A list of input vars for creating a Configuring, including:
     * 	- allow_admin If admin is allowed to use multiple ip
     * 	- company_id The ID of the company this page belongs to
     * 	- allow_client If client is allowed to use multiple ip
     */
    public function merge(array $vars) {
			$company_id = Configure::get("Blesta.company_id") ;
			if(!empty($vars['allow_admin'])) 
				$vars['allow_admin'] = 1;
			else  
				$vars['allow_admin'] = 0;
				
			if(!empty($vars['allow_client'])) 
				$vars['allow_client'] = 1;
			else  
				$vars['allow_client'] = 0;
					
            $fields = array("allow_admin", "company_id", "allow_client");
			
            $this->Record->duplicate("allow_admin", "=", $vars['allow_admin'])->
                duplicate("allow_client", "=", $vars['allow_client'])->
                insert("cpanel_csf", $vars, $fields);			
    }

}