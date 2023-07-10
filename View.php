<?php

namespace Kalinger;

use Smarty;
use Retrinko\Ini\IniFile;
use Kalinger\SmartyExtensions;

class View {

    public $path;
    public $route;
    public $config;
    public $user_info;
    public $languageWordList;

    private $smarty;
    protected $debug = false;
    protected $cache = [];
    protected $version;

    public function __construct($route, $config, $user_info) {

        $this->route = $route;
        $this->path = $route['controller'] . '/' . $route['action'];
        $this->config = $config;
        $this->user_info = $user_info;

        // init Smarty
        $this->smarty = new Smarty();

        // config Smarty
        $this->smarty->setTemplateDir(WWW . '/' . $this->config['templatePrefix']);
        $this->smarty->setCompileDir(APPLICATION . '/tmp/smarty/templates_c');
        $this->smarty->setCacheDir(APPLICATION . '/tmp/smarty/cache');

        try {

            $iniFile = IniFile::load((APPLICATION . '/config/languages/' . $config['site_language'] . '.ini'));

            $this->languageWordList = $iniFile->toArray();

        } catch (\Exception $e) {

            printf('Exception! %s'.PHP_EOL, $e->getMessage());

        }

        // global vars page
        $this->smarty->assign('page', $this->route['name']);
        $this->smarty->assign('config', $this->config);
        $this->smarty->assign('user_info', $user_info);
        $this->smarty->assign('LANG', $this->languageWordList);

        $inputGlobalCssArray = [];
        $inputGlobalJsArray = [];

        foreach ($config['global_fonts'] as $global_font) {

            $inputGlobalCssArray[] = $this->config['templateWebPath'].$global_font;

        }

        foreach ($config['global_css'] as $global_css) {

            $inputGlobalCssArray[] = $this->config['templateWebPath'].$global_css;

        }

        foreach ($this->route['css'] as $css) {

            $inputGlobalCssArray[] = $this->config['templateWebPath'].$css;

        }

//        $inputGlobalCssArray[] = $this->config['templateWebPath'].'styles/languages/'.$config['site_language'].'.css';

        foreach ($config['global_js'] as $global_js) {

            $inputGlobalJsArray[] = $this->config['templateWebPath'].$global_js;

        }

        foreach ($this->route['js'] as $js) {

            $inputGlobalJsArray[] = $this->config['templateWebPath'].$js;

        }

        $this->smarty->assign('inputGlobalCssArray', $inputGlobalCssArray);
  
        $this->smarty->assign('outputGlobalCssPath', $config['templateWebFileCssCachePrefix'].md5($this->route['name'].'-'.$config['site_language']).$config['templateWebFileCssCachePostfix']);

        $this->smarty->assign('inputGlobalJsArray', $inputGlobalJsArray);
        $this->smarty->assign('outputGlobalJsPath', $config['templateWebFileJsCachePrefix'].md5($this->route['name']).$config['templateWebFileJsCachePostfix']);

        $this->smarty->assign('templateWebPath', $this->config['templateWebPath']);

        $smartyExtensions = new SmartyExtensions($this->config['uri_prefix']);

        $this->smarty->registerObject("SmartyExtensions", $smartyExtensions);

    }

    public function render($templateName, $vars = [], $dir = false) {

        $this->smarty->assign('debug', $this->debug);

        if ($this->cache) {

            if ($this->cache['trigger']) {

                $this->smarty->setCaching($this->smarty::CACHING_LIFETIME_CURRENT);
                $this->smarty->setCacheLifetime($this->cache['cache_lifetime']);
                $this->smarty->setCompileCheck($this->cache['compile_check']);

            }

        }

        foreach ($vars as $key => $val) {

            $this->smarty->assign($key, $val);

        }

        if (!$dir) {

            $this->smarty->display(WWW . '/' . $this->config['templatePrefix'] . $this->route['controller'] . '/' . $templateName . $this->config['templatePostfix']);

        } else {

            $this->smarty->display(WWW . '/' . $this->config['templatePrefix'] . $dir . '/' . $templateName . $this->config['templatePostfix']);

        }

    }
//
//    public static function error($code) {
//
//        http_response_code($code);
//
//        // init Smarty
//        $smarty = new Smarty();
//
//        // config Smarty
//        $smarty->setTemplateDir(WWW . '/' . $self->config['templatePrefix']);
//        $smarty->setCompileDir(APPLICATION . '/tmp/smarty/templates_c');
//        $smarty->setCacheDir(APPLICATION . '/tmp/smarty/cache');
//
//        $smarty->display(WWW.'/'.$this->config['templatePrefix'].$this->route['controller'].'/'.$code. $this->config['templatePostfix']);
//
//        exit();
//
//    }

    public function redirect($url) {

        http_response_code(301);
        header('location: ' . $url);

        exit();

    }

    public function debug() {

        $this->debug = true;

    }

    public function cache($trigger = true, $cache_lifetime = 3600, $compile_check = false) {

        $this->cache['trigger'] = $trigger;
        $this->cache['cache_lifetime'] = $cache_lifetime;
        $this->cache['compile_check'] = $compile_check;

    }

    public function isCache($template) {

        $this->smarty->setCaching($this->smarty::CACHING_LIFETIME_CURRENT);

        return $this->smarty->isCached($template. $this->config['templatePostfix']);

    }

    public function clearCache($template = false) {

        $this->smarty->setCaching($this->smarty::CACHING_LIFETIME_CURRENT);

        if ($template) {

            $this->smarty->clearCache($template.$this->config['templatePostfix']);

        } else {

            $this->smarty->clearAllCache();

        }

//        $this->version = '?v='.md5(date("H-i-s_m-d-Y"));

    }

}