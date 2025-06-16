<?php

namespace Kalinger;

use Mobile_Detect;
use GeoIp2\Database\Reader;
use donatj\UserAgent\UserAgentParser;
use Whoops\Run;
use csrfProtector;

class Router
{

    protected $routes = [];
    protected $params = [];
    protected $config = [];
    protected $ip_config = [];
    protected $ip_user = [];
    public $user_info = [];
    public $redirects = [];

    public function __construct()
    {

        $routes = require APPLICATION . '/config/routes.php';
        $this->redirects = require APPLICATION . '/config/redirects.php';
        $this->config = require APPLICATION . '/config/config.php';
        $this->config['host'] = $this->getHost();
        $this->ip_config = require APPLICATION . '/config/ip_config.php';
        $this->ip_user = $this->getIpUser();
        $this->user_info = $this->getIpInfo($this->ip_user);

        $this->normalizeUri();

        if ($this->config['whoops']['enable']) {

            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            $whoops = new Run();
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();

        } else {

            ini_set('display_errors', 0);

        }

        if ($this->config['csrfProtector']['enable']) {

            if ($this->config['csrfProtector']['api']['enable']) {

                if ((strpos($this->getUri(), $this->config['csrfProtector']['api']['url']) === false) && (strpos($this->getUri(), 'admin/ajax/') === false)) {

                    csrfProtector::init();

                }

            } else {

                csrfProtector::init();

            }

        }

        foreach ($routes as $key => $val) {

            $this->add($key, $val);

        }

//        print_r($this->routes);
    }

    protected function normalizeUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Признак AJAX-запроса через заголовок
        $isAjaxHeader = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Признак AJAX-запроса через слово "ajax" в URI (в пути или в query)
        $isAjaxInUri = stripos($uri, 'ajax') !== false;

        // Если AJAX (через заголовок или в URL) — не редиректим
        if ($isAjaxHeader || $isAjaxInUri) {
            return;
        }

        // Парсим URI
        $parsedUrl = parse_url($uri);
        $path = $parsedUrl['path'] ?? '/';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';

        // Исключаем нормализацию для API (пример: /api/...)
        if (stripos($path, '/api/') === 0) {
            return;
        }

        // Убираем множественные слэши подряд
        $path = preg_replace('#/+#', '/', $path);

        // Добавляем слэш в конце, если это не корень и не заканчивается на слэш
        if ($path !== '/' && substr($path, -1) !== '/') {
            $path .= '/';
        }

        $normalized = $path . $query;

        // Если URI отличается — редирект с кодом 308 (сохраняет метод и тело)
        if ($uri !== $normalized) {
            $protocol = $this->getProtocol(); // например, https://
            $host = $_SERVER['HTTP_HOST'];

            header("Location: " . $protocol . $host . $normalized, true, 308);
            exit;
        }
    }

    public function add($route, $params)
    {

        $route = preg_replace('/{([a-z]+):([^\}]+)}/', '(?P<\1>\2)', $route);
        $route = '#^' . $route . '$#';
        $this->routes[$route] = $params;

    }

    public function match()
    {

        $url = $this->getUri();

        foreach ($this->redirects as $redirect) {

            $this->redirect($redirect['url'], $redirect['new-url'], 301);

        }

        foreach ($this->routes as $route => $params) {

            if (preg_match($route, $url, $matches)) {

                foreach ($matches as $key => $match) {

                    if (is_string($key)) {

//                        if (is_numeric($match)) {
//
//                            $match = (int) $match;
//
//                        }

                        $params[$key] = htmlspecialchars($match, ENT_QUOTES);

                    }

                }

                $this->params = $params;

                return true;

            }

        }

        return false;

    }

    public function run()
    {

        if (!$this->isAccess($this->ip_user, $this->ip_config['black_ip'])) {

            if ($this->match()) {

                $path = 'Application\controllers\\' . ucfirst($this->params['controller']) . 'Controller';

                if (class_exists($path)) {

//                    print_r('Class exists');

                    $action = $this->params['action'] . 'Action';

                    if (method_exists($path, $action)) {

//                        print_r('Metod exists');

                        if ($this->checkAccess($this->params['access'], $this->params['access_ip'])) {

                            $controller = new $path($this->params, $this->config, $this->user_info);
                            $controller->$action();

                        } else {

                            $this->ERROR(403);

                        }

                    } else {

//                        print_r('Metod not exists');

                        $this->ERROR(404);

                    }

                } else {

//                    print_r('Class not exists');

                    $this->ERROR(404);

                }

            } else {

                $this->ERROR(404);

            }

        } else {

            $this->ERROR(404);

        }

    }

    public function checkAccess($access, $access_ip)
    {

        if (!$access_ip) {

            if ($this->isAccess('all', $access) || $this->isAccess('guest', $access)) {

                return true;

            } elseif (isset($_SESSION['access']) && $this->isAccess($_SESSION['access'], $access)) {

                return true;

            }

            return false;

        } else {

            if ($this->isAccess($this->ip_user, $this->ip_config['white_ip'])) {

                return true;

            }

            return false;

        }

    }

    protected function isAccess($key, $access)
    {

        return in_array($key, $access);

    }

    public function getProtocol()
    {

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {

            $protocol = 'https';

        } else {

            $protocol = 'http';

        }

        $this->config['protocol'] = $protocol;

        return $protocol . '://';

    }

    public function getHost()
    {

        $protocol = $this->getProtocol();

        return $protocol . $_SERVER['HTTP_HOST'];

    }

    public function getUri()
    {

        $url = $_SERVER['REQUEST_URI'];

        $siteLanguage = $this->config['default_language'];

        foreach ($this->config['languages'] as $language) {

            if (strpos($url, $language . '/') !== false) {

                $siteLanguage = $language;

                $url = str_replace($language . '/', '', $url);

            }

        }

        $url = trim($url, '/');

        $slash = '';

        if ($url != '') {

            $slash = '/';

        }

        $this->config['uri'] = $slash . $url . $slash;

        if ($siteLanguage == $this->config['default_language']) {

            $lang_url = '/';
            $this->config['uri_prefix'] = '';

        } else {

            $lang_url = '/' . $siteLanguage . '/';
            $this->config['uri_prefix'] = '/' . $siteLanguage;

        }

        $this->config['url'] = $this->getHost() . $lang_url . $url . $slash;

        $this->config['site_language'] = $siteLanguage;

        // Удаляем гет параметры из uri
        $url = rtrim(preg_replace('/\\?.*/', '', $url),'/');

        return $url;

    }

    public function getIpUser()
    {

        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = @$_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {

            $ip = $client;

        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {

            $ip = $forward;

        } else {

            $ip = $remote;

        }

        return $ip;

    }

    public function getLanguageBrowser()
    {

        $langs = [];

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)s*(;s*qs*=s*(1|0.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

            if (count($lang_parse[1])) {

                $langs = array_combine($lang_parse[1], $lang_parse[4]);

                foreach ($langs as $lang => $val) {

                    if ($val === '') $langs[$lang] = 1;

                }

                arsort($langs, SORT_NUMERIC);

            }

        }

        foreach ($langs as $lang => $val) {

            break;

        }

        if (stristr($lang, "-")) {

            $tmp = explode("-", $lang);
            $lang = $tmp[0];

        }

        return $lang;

    }

    public function getIpInfo($ip)
    {

        $info['countryCode'] = '';
        $info['country'] = '';
        $info['city'] = '';
        $info['ip'] = $ip;

        $detect = new Mobile_Detect;

        if ($detect->isMobile() && !$detect->isTablet()) {

            $info['device'] = 'Смартфон';

        } elseif ($detect->isTablet()) {

            $info['device'] = 'Планшет';

        } else {

            $info['device'] = 'ПК';

        }

        if ($info['device'] != 'ПК') {

            $parser = new UserAgentParser();

            $parser->parse();

            $res = $parser();

            $info['platform'] = $res->platform();
            $info['browser'] = $res->browser();
            $info['version'] = $res->browserVersion();
            $info['browserLanguage'] = $this->getLanguageBrowser();

        } else {

            $info['platform'] = '';
            $info['browser'] = '';
            $info['version'] = '';
            $info['browserLanguage'] = 'ru';

        }

//        if ($ip != '127.0.0.1') {
//
//            $readerCountry = new Reader(APPLICATION . '/../data/geoip2/GeoLite2-Country.mmdb');
//            $geo = $readerCountry->country($ip);
//            $info['countryCode'] = $geo->country->isoCode;
//            $info['country'] = $geo->country->name;
//
//            $readerSity = new Reader(APPLICATION . '/..//data/geoip2/GeoLite2-City.mmdb');
//            $geo = $readerSity->city($ip);
//            $info['city'] = $geo->city->name;
//
//        }

        return $info;

    }

    public function ERROR($code)
    {

        $path = 'Application\controllers\\' . ucfirst('Errors') . 'Controller';

        $action = 'errorAction';

        $params = [
            'controller' => 'errors',
            'action' => 'error' . $code,
            'access' => ['all'],
            'access_ip' => false,
            'name' => 'errors',
            'parent' => '',
            'css' => [],
            'js' => []
        ];

        $controller = new $path($params, $this->config, $this->user_info);

        $controller->$action($code);

    }

    public function redirect($route, $new_route, $code)
    {

        if ($this->getUri() == $route) {

            header('Location: ' . $this->getHost() . '/' . $new_route, true, $code);
            exit();

        }

    }

}