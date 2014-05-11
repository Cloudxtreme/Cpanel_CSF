<?php
/**
 * CPanel CSF parent controller.
 *
 * @package nirays
 * @subpackage nirays.plugins.cpanel_csf
 * @copyright Copyright (c) 2013, Nirays Technologies.
 * @license License Agreement
 * @link http://nirays.com/ Nirays
 */
class CpanelCsfController extends AppController {

	/**
	 * Setup
	 */
    public function preAction() {
        parent::preAction();

        // Override default view directory
        $this->view->view = "default";
        $this->orig_structure_view = $this->structure->view;
        $this->structure->view = "default";
    }
}
?>