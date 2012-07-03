<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pre_embedder {
	
	public $parse_globals = '';
	
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	public function variables($tagdata)
	{
		$variables = array();
		
		if (preg_match_all('/'.LD.'(pre_embed\s*=\s*(\042|\047)([^\2]*?)\2)((\s*\w+\s*=\s*(\042|\047)[^\6]*?\6)+)?\s*'.RD.'/ms', $tagdata, $matches))
		{
			foreach ($matches[0] as $i => $full_match)
			{
				//template_group/template, embed vars
				$variables[substr($full_match, 1, -1)] = $this->embed($matches[3][$i], $this->EE->functions->assign_parameters($matches[4][$i]));
			}
		}
		
		return $variables;
	}
	
	public function parse($tagdata)
	{
		foreach ($this->variables($tagdata) as $key => $value)
		{
			$tagdata = str_replace(LD.$key.RD, $value, $tagdata);
		}
		
		return $tagdata;
	}
	
	protected function fetch_template($template_group, $template)
	{
		$query = $this->EE->db->select('template_data, save_template_file, template_name, template_groups.group_name, template_type')
				      ->where('group_name', $template_group)
				      ->where('template_name', $template_name)
				      ->join('template_groups', 'template_groups.group_id = templates.group_id')
				      ->get('templates');
				      
		if ($query->num_rows() === 0)
		{
			return '';
		}
		
		$output = $query->row('template_data');
		
		if ($this->EE->config->item('save_tmpl_files') === 'y' && $this->EE->config->item('tmpl_file_basepath')  && $query->row('save_template_file') === 'y')
		{
			$this->EE->load->library('api');
			$this->EE->api->instantiate('template_structure');
			
			$basepath = rtrim($this->EE->config->item('tmpl_file_basepath'), '/').'/';
			$basepath .= $this->EE->config->item('site_short_name').'/'.$query->row('group_name').'.group/'.$query->row('template_name').$this->EE->api_template_structure->file_extensions($query->row('template_type'));
			
			if (file_exists($basepath))
			{
				$output = file_get_contents($basepath);
			}
		}
		
		$query->free_result();
		
		return $output;
	}
	
	protected function embed($template_string, $vars = FALSE)
	{
		$parts = explode('/', $template_string);
		
		$template_group = $parts[0];
		
		$template = (isset($parts[1])) ? $parts[1] : 'index';
		
		$embed = $this->fetch_template($template_group, $template);
		
		//for some reason this was throwing errors, when I had template debugging on
		if (@preg_match_all('/'.LD.'embed:(\w+)'.RD.'/', $embed, $matches))
		{
			foreach ($matches[0] as $i => $full_match)
			{
				$embed = str_replace(
					$full_match,
					(isset($vars[$matches[1][$i]])) ? $vars[$matches[1][$i]] : '',
					$embed
				);
			}
		}
		
		// strip comments and parse segment_x vars
		$embed = preg_replace("/\{!--.*?--\}/s", '', $embed);

		for ($i = 1; $i < 10; $i++)
		{
			$embed = str_replace(LD.'segment_'.$i.RD, $this->EE->uri->segment($i), $embed);
		}

		// swap config global vars
		foreach ($this->EE->config->_global_vars as $key => $value)
		{
			$embed = $this->EE->TMPL->swap_var_single($key, $value, $embed);
		}
		
		// parse late globals (expensive)
		if ($this->parse_globals === 'all')
		{
			$embed = $this->EE->TMPL->parse_globals($embed);
		}
		elseif ($this->parse_globals === 'member')
		{
			// member vars
			foreach(array(
					'member_id', 'group_id', 'member_group', 'username', 'screen_name',
					//'group_title', 'group_description', 
					//'email', 'ip_address', 'location', 'total_entries', 
					//'total_comments', 'private_messages', 'total_forum_posts', 
					//'total_forum_topics', 'total_forum_replies'
				) as $val)
			{
				if (isset($this->EE->session->userdata[$val]) AND ($val == 'group_description' OR strval($this->EE->session->userdata[$val]) != ''))
				{
					//$embed = str_replace(LD.$val.RD, $this->EE->session->userdata[$val], $embed);				 
					//$embed = str_replace('{out_'.$val.'}', $this->EE->session->userdata[$val], $embed);
					//$embed = str_replace('{global->'.$val.'}', $this->EE->session->userdata[$val], $embed);
					$embed = str_replace('{logged_in_'.$val.'}', $this->EE->session->userdata[$val], $embed);
				}
			}	
		}

		return $embed;
	}
}

/* End of file Pre_embedder.php */ 
/* Location: ./system/expressionengine/third_party/pre_embed/libraries/Pre_embedder.php */ 