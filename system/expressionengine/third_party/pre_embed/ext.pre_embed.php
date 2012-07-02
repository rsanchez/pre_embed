<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pre_embed_ext
{
	public $settings = array();
	public $name = 'Pre Embed';
	public $version = '1.0.3';
	public $description = 'Use templates like snippets, parsed early';
	public $settings_exist = 'n';
	public $docs_url = 'https://github.com/rsanchez/pre_embed';
	
	/**
	 * constructor
	 * 
	 * @access	public
	 * @param	mixed $settings = ''
	 * @return	void
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		
		$this->EE->load->library('pre_embedder');
		
		$this->settings = $settings;
	}
	
	/**
	 * activate_extension
	 * 
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$hook_defaults = array(
			'class' => __CLASS__,
			'settings' => '',
			'version' => $this->version,
			'enabled' => 'y',
			'priority' => 10
		);
		
		$hooks[] = array(
			'method' => 'template_fetch_template',
			'hook' => 'template_fetch_template'
		);
		
		foreach ($hooks as $hook)
		{
			$this->EE->db->insert('extensions', array_merge($hook_defaults, $hook));
		}
	}
	
	/**
	 * update_extension
	 * 
	 * @access	public
	 * @param	mixed $current = ''
	 * @return	void
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		$this->EE->db->update('extensions', array('version' => $this->version), array('class' => __CLASS__));
	}
	
	/**
	 * disable_extension
	 * 
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		$this->EE->db->delete('extensions', array('class' => __CLASS__));
	}
	
	/**
	 * settings
	 * 
	 * @access	public
	 * @return	void
	 */
	public function settings()
	{
		$settings = array();
		
		return $settings;
	}
	
	public function template_fetch_template($row)
	{
		$this->EE->config->_global_vars = array_merge($this->EE->config->_global_vars, $this->EE->pre_embedder->variables($row['template_data']));
	}
}

/* End of file ext.pre_embed.php */
/* Location: ./system/expressionengine/third_party/pre_embed/ext.pre_embed.php */