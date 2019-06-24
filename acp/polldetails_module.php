<?php
/**
*
* @package Poll Details Extension
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\polldetails\acp;

class polldetails_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name		= 'poll_details';
		$this->page_title	= $phpbb_container->get('language')->lang('ACP_POLL_DETAILS');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('david63.polldetails.admin.controller');

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		$admin_controller->display_output();
	}
}
