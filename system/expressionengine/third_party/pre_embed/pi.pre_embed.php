<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'Pre Embed',
	'pi_version' => '1.0.0',
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
		$this->EE = get_instance();
		
		$this->return_data = $this->EE->TMPL->tagdata;
		
		if (preg_match_all('/'.LD.'pre_embed\s*=\s*([\042\047]?)(.*)\\1(.*)'.RD.'/', $this->return_data, $matches))
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
	
	private function embed($template, $vars = FALSE)
	{
		$template = explode('/', $template);
		
		$group_name = $template[0];
		
		$template_name = (isset($template[1])) ? $template[1] : 'index';
		
		$query = $this->EE->db->select('template_data')
				      ->where('group_name', $group_name)
				      ->where('template_name', $template_name)
				      ->join('template_groups', 'template_groups.group_id = templates.group_id')
				      ->get('templates');
				      
		if ($query->num_rows() === 0)
		{
			return '';
		}
		
		$embed = $query->row('template_data');
		
		//for some reason this was throwing errors, when I had template debugging on
		if (@preg_match_all('/'.LD.'embed:.(*)'.RD.'/', $embed, $matches))
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
		
		return $embed;
	}
	
	public static function usage()
	{
		ob_start(); 
?>
## The old way.
	{!--template--}
	{exp:channel:entries channel="your_channel"}
		{pre_embed="site/embed" title="{title}" your_custom_field="{your_custom_field}"}
	{/exp:channel:entries}

	{!--embed--}
	<p>{embed:title}: {embed:your_custom_field}</p>


## The new way.
	{!--template--}
	{exp:pre_embed parse="inward"}
	{exp:channel:entries channel="your_channel"}
		{embed="site/something"}
	{/exp:channel:entries}
	{exp:pre_embed parse="embed"}

	{!--embed--}
	<p>{title}: {your_custom_field}</p>

## Tada!
Now you can re-use the same embed more easily.
<?php
		$buffer = ob_get_contents();
		      
		ob_end_clean(); 
	      
		return $buffer;
	}
}
/* End of file pi.pre_emned.php */ 
/* Location: ./system/expressionengine/third_party/pre_embed/pi.pre_embed.php */ 