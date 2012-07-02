<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'Pre Embed',
	'pi_version' => '1.0.2',
	'pi_author' => 'Rob Sanchez',
	'pi_author_url' => 'http://github.com/rsanchez',
	'pi_description' => 'Embed a template before other tag parsing, so you can re-use templates more easily.',
	'pi_usage' => '## See the included README

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
Now you can re-use the same embed more easily.',
);

class Pre_embed
{
	public $return_data = '';

	public function Pre_embed()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->library('pre_embedder');
		
		$this->EE->pre_embedder->parse_globals = $this->EE->TMPL->fetch_param('globals');
		
		$this->return_data = $this->EE->pre_embedder->parse($this->EE->TMPL->tagdata);
	}
}
/* End of file pi.pre_embed.php */ 
/* Location: ./system/expressionengine/third_party/pre_embed/pi.pre_embed.php */ 