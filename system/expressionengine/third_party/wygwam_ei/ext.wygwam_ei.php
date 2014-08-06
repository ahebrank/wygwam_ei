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
    private $plugins_with_icons = array(
        'image2');
    private $js_added = false;

    private $_hooks = array(
        'wygwam_config',
        'wygwam_tb_groups',
    );

    private function _include_resources() {
        // Is this the first time we've been called?
        if (!$this->js_added)
        {
            // Tell CKEditor where to find our plugin
            foreach ($this->plugins as $p) {
                $plugin_url = URL_THIRD_THEMES.$this->realname.'/'.$p.'/plugin.js';
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


    // hooks

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

    public function wygwam_tb_groups($tb_groups) {
        if (($last_call = $this->EE->extensions->last_call) !== FALSE) {
            $tb_groups = $last_call;
        }

        $tb_groups[] = $this->plugins_with_icons;

        // Is this the toolbar editor?
        if ($this->EE->input->get('M') == 'show_module_cp')
        {
            foreach ($this->plugins_with_icons as $p) {
                // Give our toolbar button an icon
                $icon_url = URL_THIRD_THEMES.$this->realname.'/'.$p.'/icons/image.png';
                $this->EE->cp->add_to_head('<style type="text/css">.cke_button__'.$p.'_icon { background-image: url('.$icon_url.'); }</style>');
            }
        }

        return $tb_groups;
    }

}