<?php
/**
*
* @package Poll Details Extension
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACP_POLL_DETAILS_EXPLAIN'	=> 'This shows you the details of who has voted, and for which option, in a poll.',

	'NO_POLL_DATA'				=> 'There is no poll data to display',

	'POLL_OPTION'				=> 'Poll option',
	'POLL_VOTER'				=> 'Voted for by',
	'POLL_VOTES'				=> 'Option votes',

	'SELECT_VOTES'				=> 'Click on the forum to expand/collapse the data and view the votes',

	'TOPIC_STARTED'				=> 'Poll created on',
	'TOPIC_STARTER'				=> 'Poll started by',

	'VISIT_TOPIC'				=> 'Click on the topic title to visit the topic',
));
