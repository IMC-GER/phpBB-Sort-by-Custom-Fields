<?php
/**
 * Sort by custom fields
 * An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, Thorsten Ahlers
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace imcger\sortbycustomfields\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	protected object $pagination;
	protected object $db;
	protected object $config;
	protected object $pf_manager;
	protected object $template;
	protected object $request;

	protected int $total_users;
	protected string $sortby;
	protected string $sort_dir;
	protected string $first_char;

	public function __construct
	(
		\phpbb\pagination $pagination,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\profilefields\manager $pf_manager,
		\phpbb\template\template $template,
		\phpbb\request\request $request
	)
	{
		$this->pagination = $pagination;
		$this->db		  = $db;
		$this->config	  = $config;
		$this->pf_manager = $pf_manager;
		$this->template	  = $template;
		$this->request	  = $request;

		$this->sortby	  = $this->request->variable('imcsort', '');
		$this->sort_dir	  = $this->request->variable('sd', 'a');
		$this->first_char = $this->request->variable('first_char', '');
	}

	public static function getSubscribedEvents(): array
	{
		return [
			'core.memberlist_modify_sort_pagination_params' => 'memberlist_modify_sort_pagination_params',
			'core.memberlist_modify_memberrow_sql'			=> 'memberlist_modify_memberrow_sql',
			'core.memberlist_memberrow_before'				=> 'memberlist_memberrow_before',
		];
	}

	public function memberlist_modify_sort_pagination_params(object $event): void
	{
		$this->total_users = $event['total_users'];

		$params = $event['params'];
		$params[] = "imcsort=" . $this->request->variable('imcsort', '');
		$event['params'] = $params;
	}

	public function memberlist_modify_memberrow_sql(object $event): void
	{
		$start		= $this->request->variable('start', 0);
		$sort_dir	= ($this->sort_dir == 'a') ? 'ASC' : 'DESC';
		$user_types = [USER_NORMAL, USER_FOUNDER];
		$sql_where  = '';

		if ($this->sortby)
		{
			$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $this->total_users);

			if ($this->first_char == 'other')
			{
				for ($i = 97; $i < 123; $i++)
				{
					$sql_where .= ' AND u.username_clean NOT ' . $this->db->sql_like_expression(chr($i) . $this->db->get_any_char());
				}
			}
			else if ($this->first_char)
			{
				$sql_where .= ' AND u.username_clean ' . $this->db->sql_like_expression(substr($this->first_char, 0, 1) . $this->db->get_any_char());
			}

			$sql_array = [
				'SELECT'    => 'u.user_id',
				'FROM'      => [USERS_TABLE => 'u'],
				'LEFT_JOIN' => [
					[
						'FROM' => [PROFILE_FIELDS_DATA_TABLE => 'pf', ],
						'ON'   => 'u.user_id = pf.user_id',
					],
				],
				'WHERE'     => $this->db->sql_in_set('u.user_type', $user_types) . $sql_where,
				'ORDER_BY'  => 'pf.pf_' . $this->sortby . ' ' . $sort_dir . ', u.username_clean ASC',
			];

			$sql    = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

			$rowset = $this->db->sql_fetchrowset($result);
			$user_list = array_column($rowset, 'user_id');

			$sql_array = [
				'SELECT'	=> 'u.*',
				'FROM'		=> [
					USERS_TABLE => 'u'
				],
				'WHERE' 	=> $this->db->sql_in_set('u.user_id', $user_list),
			];

			$event['sql_array'] = $sql_array;
			$event['user_list'] = $user_list;
		}
	}

	public function memberlist_memberrow_before(): void
	{
		$first_char_para = $this->first_char ? "&amp;first_char={$this->first_char}" : '';

		// Set custom profile fields
		if ($this->config['load_cpf_memberlist'])
		{
			$this->template->destroy_block_vars('custom_fields');

			$cpf_rows = $this->pf_manager->generate_profile_fields_template_headlines('field_show_on_ml');
			foreach ($cpf_rows as $cpf)
			{
				$sort_dir = '&amp;sd=' . (($this->sortby == $cpf['PROFILE_FIELD_IDENT'] && $this->sort_dir == 'a') ? 'd' : 'a');
				$cpf_sort = '&amp;imcsort=' . $cpf['PROFILE_FIELD_IDENT'];

				$cpf['PROFILE_FIELD_NAME'] = '<a href="./memberlist.php?mode=&amp;sk=a' . $sort_dir . $cpf_sort . $first_char_para . '">' . $cpf["PROFILE_FIELD_NAME"] . '</a>';
				$this->template->assign_block_vars('custom_fields', $cpf);
			}
		}
	}
}
