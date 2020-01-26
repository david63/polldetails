<?php
/**
*
* @package Poll Details Extension
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\polldetails\controller;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\user;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\pagination;
use phpbb\language\language;
use david63\polldetails\core\functions;

/**
* Admin controller
*/
class admin_controller implements admin_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $phpEx;

	/** @var \david63\polldetails\core\functions */
	protected $functions;

	/** @var string phpBB tables */
	protected $tables;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin controller
	*
	* @param \phpbb\config\config					$config			Config object
	* @param \phpbb\db\driver\driver_interface		$db				Database object
	* @param \phpbb\user							$user			User object
	* @param \phpbb\request\request					$request		Request object
	* @param \phpbb\template\template				$template		Template object
	* @param \phpbb\pagination						$pagination		Pagination object
	* @param \phpbb\language\language				$language		Language object
	* @param string 								$root_path		phpBB&nbsp;root path
	* @param string 								$php_ext		phpBB file extension
	* @param \david63\polldetails\core\functions	$functions		Functions for the extension
	* @param array									$tables			phpBB db tables
	*
	* @return \david63\polldetails\controller\admin_controller
	* @access public
	*/
	public function __construct(config $config, driver_interface $db, user $user, request $request, template $template, pagination $pagination, language $language, $root_path, $php_ext, functions $functions, $tables)
	{
		$this->config		= $config;
		$this->db  			= $db;
		$this->user			= $user;
		$this->request		= $request;
		$this->template		= $template;
		$this->pagination	= $pagination;
		$this->language		= $language;
		$this->root_path	= $root_path;
		$this->phpEx		= $php_ext;
		$this->functions	= $functions;
		$this->tables		= $tables;
	}

	/**
	* Display the output for this extension
	*
	* @return null
	* @access public
	*/
	public function display_output()
	{
		// Add the language files
		$this->language->add_lang('acp_polldetails',  $this->functions->get_ext_namespace());
		$this->language->add_lang('acp_common',  $this->functions->get_ext_namespace());

		// Start initial var setup
		$action			= $this->request->variable('action', '');
		$sid 			= $this->request->variable('sid', '');
		$start			= $this->request->variable('start', 0);
		$per_page		= $this->request->variable('users_per_page', (int) $this->config['topics_per_page']);

		$back = false;

		// Get a list of the polls
		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'f.forum_name, f.forum_id, o.topic_id, o.poll_option_id, t.topic_time, t.topic_title, t.topic_poster',
			'FROM'		=> array(
				POLL_OPTIONS_TABLE	=> 'o',
				POLL_VOTES_TABLE	=> 'v',
				TOPICS_TABLE		=> 't',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(FORUMS_TABLE => 'f'),
					'ON'	=> 't.forum_id = f.forum_id'
				)
			),

			'WHERE'		=> 'o.topic_id = t.topic_id',
			'GROUP_BY'	=> 't.topic_title',
			'ORDER_BY'	=> 't.topic_time DESC',
		));

		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Get the poll starter
			$sql1 = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'u.user_id, u.username, u.user_colour',
				'FROM'		=> array(
					USERS_TABLE	=> 'u',
				),

				'WHERE' => 'u.user_id = ' . $row['topic_poster'],
			));

			$result1	= $this->db->sql_query($sql1);
			$row1		= $this->db->sql_fetchrow($result1);

			$this->template->assign_block_vars('poll_details', array(
				'FORUM'			=> $row['forum_name'],
				'TOPIC'			=> $row['topic_title'],
				'TOPIC_STARTER'	=> get_username_string('full', $row1['user_id'], $row1['username'], $row1['user_colour']),
				'TOPIC_TIME'	=> $this->user->format_date($row['topic_time']),
				'TOPIC_URL'		=> $this->root_path . '/viewtopic.' . $this->phpEx . '?f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '&amp;sid=' . $sid,

				// Create a unique key for the js script
				'POLL_KEY'		=> $row['topic_id'] . $row['topic_poster'],
			));
			$this->db->sql_freeresult($result1);

			// Get the poll options
			$sql1 = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'o.*',
				'FROM'		=> array(
					POLL_OPTIONS_TABLE	=> 'o',
				),

				'WHERE'		=> 'o.topic_id = ' . $row['topic_id'],
				'ORDER_BY'	=> 'o.poll_option_id',
			));

			$result1 = $this->db->sql_query($sql1);

			while ($row1 = $this->db->sql_fetchrow($result1))
			{
				// Get the poll voters
				$sql2 = $this->db->sql_build_query('SELECT', array(
					'SELECT'	=> 'u.user_id, u.username, u.username_clean, u.user_colour',
					'FROM'		=> array(
						USERS_TABLE	=> 'u',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(POLL_VOTES_TABLE	=> 'v'),
							'ON'	=> 'v.vote_user_id = u.user_id',
						)
					),

					'WHERE'		=> 'v.poll_option_id = ' . $row1['poll_option_id'] . '
						AND v.topic_id = ' . $row1['topic_id'],
					'ORDER_BY'	=> 'u.username_clean',
				));

				$result2	= $this->db->sql_query($sql2);
				$vote_users = '';

				while ($row2 = $this->db->sql_fetchrow($result2))
				{
					$vote_users .= get_username_string('full', $row2['user_id'], $row2['username'], $row2['user_colour']) . '<br>';
				}
				$this->db->sql_freeresult($result2);

				$this->template->assign_block_vars('poll_details.vote_details', array(
					'POLL_TEXT'		=> $row1['poll_option_text'],
					'POLL_TOTAL' 	=> $row1['poll_option_total'],
					'USERS'			=> $vote_users,
				));
			}
			$this->db->sql_freeresult($result1);
		}
		$this->db->sql_freeresult($result);

		// Get total poll count for pagination
		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'COUNT(o.topic_id) AS total_polls',
			'FROM'		=> array(
				POLL_OPTIONS_TABLE	=> 'o',
			),
		));

		$result		= $this->db->sql_query($sql);
		$poll_count	= (int) $this->db->sql_fetchfield('total_polls');

		$this->db->sql_freeresult($result);

		if ($poll_count == 0)
		{
			trigger_error($this->language->lang('NO_POLL_DATA'));
		}

		$action = $this->u_action;

		$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $poll_count);
		$this->pagination->generate_template_pagination($action, 'pagination', 'start', $poll_count, $this->config['topics_per_page'], $start);

		// Template vars for header panel
		$version_data	= $this->functions->version_check();

		$this->template->assign_vars(array(
			'DOWNLOAD'			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

			'HEAD_TITLE'		=> $this->language->lang('ACP_POLL_DETAILS'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('ACP_POLL_DETAILS_EXPLAIN'),

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'S_BACK'			=> $back,
			'S_VERSION_CHECK'	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER'	=> $this->functions->get_meta('version'),
		));

		$this->template->assign_vars(array(
			'S_ON_PAGE'     => $per_page,

			'U_ACTION'		=> $action,

			'TOTAL_POLLS'	=> $poll_count,
		));
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
