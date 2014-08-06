<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

// based on http://docs.pixelandtonic.com/wygwam/developers/ckeditor_plugins.html
class Wygwam_ei_ext {
    var $name           = 'Wygwam EI';
    var $version        = '0.1';
    var $description    = '';
    var $docs_url       = '';
    var $settings_exist = 'n';

    private $EE;
    private $realname = 'wygwam_ei';
    private $plugins   = array(
        'widget',
        'lineutils',
        'image2'
    );
    private $js_added = false;

    private $_hooks = array(
        'wygwam_config',
    );

    private function _include_resources() {
        // Is this the first time we've been called?
        if (!$this->js_added)
        {
            // Tell CKEditor where to find our plugin
            foreach ($this->plugins as $p) {
                $plugin_url = URL_THIRD_THEMES.$this->realname.'/'.$p;
                $this->EE->cp->add_to_foot('<script type="text/javascript">CKEDITOR.plugins.addExternal("'.$p.'", "'.$plugin_url.'");</script>');
            }
            // Don't do that again
            $this->js_added = true;
        }
    }

    public function __construct() {
        $this->EE =& get_instance();
    }

    public function activate_extension() {
        foreach ($this->_hooks as $hook) {
            $this->EE->db->insert('extensions', array(
                'class'    => get_class($this),
                'method'   => $hook,
                'hook'     => $hook,
                'settings' => '',
                'priority' => 10,
                'version'  => $this->version,
                'enabled'  => 'y'
            ));
        }
    }

    public function update_extension($current = NULL) {
        return FALSE;
    }

    public function disable_extension() {
        $this->EE->db->where('class', get_class($this))->delete('extensions');
    }

    public function wygwam_config($config, $settings) {
        if (($last_call = $this->EE->extensions->last_call) !== FALSE) {
            $config = $last_call;
        }

        // add plugin to ckeditor
        foreach ($this->plugins as $p) {
            $config['extraPlugins'] = empty($config['extraPlugins'])? $p : $config['extraPlugins'].",".$p;
        }
        $this->_include_resources();

        return $config;
    }
}