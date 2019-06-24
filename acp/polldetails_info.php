<?php
/**
*
* @package Poll Details Extension
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\polldetails\acp;

class polldetails_info
{
	function module()
	{
		return array(
			'filename'	=> '\david63\polldetails\acp\polldetails_module',
			'title'		=> 'ACP_POLL_DETAILS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array('title' => 'ACP_POLL_DETAILS', 'auth' => 'ext_david63/polldetails && acl_a_user', 'cat' => array('ACP_CAT_USERS')),
			),
		);
	}
}
