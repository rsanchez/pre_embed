<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'Pre Embed',
	'pi_version' => '1.0.2',
	'pi_author' => 'Rob Sanchez',
	'pi_author_url' => 'http://github.com/rsanchez',
	'pi_description' => 'Embed a template before other tag parsing, so you can re-use templates more easily.',
	'pi_usage' => Pre_embed::usage()
);

class Pre_embed
{
	public $return_data = '';

	public function Pre_embed()
	{
		$this->EE =& get_instance();
		
		$this->return_data = $this->EE->TMPL->tagdata;

		$this->extra = explode('|',$this->EE->TMPL->fetch_param('extra'));
		
		if (preg_match_all('/'.LD.'pre_embed\s*=\s*([\042\047]?)([^\\1]*?)\\1(.*)'.RD.'/s', $this->return_data, $matches))
		{
			foreach ($matches[0] as $i => $full_match)
			{
				//template_group/template, embed vars
				$embed = $this->embed($matches[2][$i], $this->EE->functions->assign_parameters($matches[3][$i]));
				
				$this->return_data = str_replace(
					$full_match,
					$embed,
					$this->return_data
				);
			}
		}
	}
	
	protected function embed($template, $vars = FALSE)
	{
		$template = explode('/', $template);
		
		$group_name = $template[0];
		
		$template_name = (isset($template[1])) ? $template[1] : 'index';
		
		$query = $this->EE->db->select('template_data, save_template_file, template_name, template_groups.group_name, template_type')
				      ->where('group_name', $group_name)
				      ->where('template_name', $template_name)
				      ->join('template_groups', 'template_groups.group_id = templates.group_id')
				      ->get('templates');
				      
		if ($query->num_rows() === 0)
		{
			return '';
		}
		
		$embed = $query->row('template_data');
		
		if ($this->EE->config->item('save_tmpl_files') === 'y' && $this->EE->config->item('tmpl_file_basepath')  && $query->row('save_template_file') === 'y')
		{
			$this->EE->load->library('api');
			$this->EE->api->instantiate('template_structure');
			
			$basepath = rtrim($this->EE->config->item('tmpl_file_basepath'), '/').'/';
			$basepath .= $this->EE->config->item('site_short_name').'/'.$query->row('group_name').'.group/'.$query->row('template_name').$this->EE->api_template_structure->file_extensions($query->row('template_type'));
			
			if (file_exists($basepath))
			{
				$embed = file_get_contents($basepath);
			}
		}
		
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
		
		// strip comments
		$embed = preg_replace("/\{!--.*?--\}/s", '', $embed);

		// swap config global vars
		foreach ($this->EE->config->_global_vars as $key => $value)
		{
			$embed = $this->EE->TMPL->swap_var_single($key, $value, $embed);
		}
		
		// extra parsing: segments
		if(in_array('segment', $this->extra))
		{
			for ($i = 1; $i < 10; $i++)
			{
				$embed = str_replace(LD.'segment_'.$i.RD, $this->EE->uri->segment($i), $embed);
			}
		}

		// extra parsing: globals (expensive)
		if(in_array('globals', $this->extra))
		{
			$embed = $this->EE->TMPL->parse_globals($embed);
		}
		else // extra parsing: only member vars instead of globals
		if(in_array('member', $this->extra)) 
		{
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
	
	public static function usage()
	{
		ob_start(); 
?>

## See the included readme

### The old way.
	{!--template--}
	{exp:channel:entries channel="your_channel"}
		{embed="site/embed" title="{title}" your_custom_field="{your_custom_field}"}
	{/exp:channel:entries}

	{!--embed--}
	<p>{embed:title}: {embed:your_custom_field}</p>


### The new way.
	{!--template--}
	{exp:pre_embed parse="inward"}{!--yes, parse="inward" is necessary--}
	{exp:channel:entries channel="your_channel"}
		{pre_embed="site/something"}
	{/exp:channel:entries}
	{/exp:pre_embed}

	{!--embed--}
	<p>{title}: {your_custom_field}</p>

### Tada!
Now you can re-use the same embed more easily.
<?php
		$buffer = ob_get_contents();
		      
		ob_end_clean(); 
	      
		return $buffer;
	}
}
/* End of file pi.pre_emned.php */ 
/* Location: ./system/expressionengine/third_party/pre_embed/pi.pre_embed.php */ 