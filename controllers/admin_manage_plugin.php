<?php
/**
 * CPanel CSF Settings controller
 *
 * @package nirays
 * @subpackage nirays.plugins.cpanel_csf.controllers
 * @copyright Copyright (c) 2013, Nirays Technologies.
 * @license License Agreement
 * @link http://nirays.com/ Nirays
 */
class AdminManagePlugin extends AppController {

    /**
     * Performs necessary initialization
     */
    private function init() {
        // Require login
        $this->parent->requireLogin();

        Language::loadLang("cpanel_csf_plugin", null, PLUGINDIR . "cpanel_csf" . DS . "language" . DS);

        // Set the plugin ID
        $this->plugin_id = (isset($this->get[0]) ? $this->get[0] : null);

        // Set the company ID
        $this->company_id = Configure::get("Blesta.company_id");

        // Set the view to render for all actions under this controller
        $this->view->setView(null, "CpanelCsf.default");
    }

    /**
     * Returns the view to be rendered when managing this plugin
     */
    public function index() {
        $this->init();
        $this->uses(array("cpanel_csf.CpanelCsfSettings"));
        if (!empty($this->post)) {
            $data = $this->post;
            $this->post['company_id'] = $this->company_id;
			
            $this->CpanelCsfSettings->merge($this->post);
			
            if (($errors = $this->CpanelCsfSettings->errors())) {
                // Error,	
                $this->parent->setMessage("error", $errors);
            }
            else {
                // Success
                $this->parent->flashMessage("message", Language::_("CpanelCsfPlugin.!success.plugin_updated", true));
                $this->redirect($this->base_uri . "settings/company/plugins/manage/" . $this->plugin_id);
            }
        }

        if (!isset($vars))
            $vars = $this->CpanelCsfSettings->get($this->company_id);

        // Set the view to render
        return $this->partial("admin_manage_plugin", array('meta' => $vars));
    }
}
?>