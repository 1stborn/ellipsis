<?php
namespace Ellipsis;

use Corpus\App as Base;
use Corpus\Config;
use GuzzleHttp\Client;

/**
 * Class Gull
 *
 * @package Gull
 *
 * @property Db     db
 * @property Client http
 * @property Auth   auth
 */
class App extends Base {
	protected $language;
	protected $view_root = "";

	public static function run($silent = false, $options = []) {
		return parent::run($silent, Config::merge([
			'http' => function () {
				return new Client(Config::get('http'));
			},
		    'db' => function() {
			    return new DB(new \Corpus\DB(Config::get('db.{default}')));
		    }
		], $options));
	}

	public function before() {
		parent::before();

		if ( $this->language = $this->getRequest()->getAttribute('language') )
			$this->session->language = $this->language;
		else
			$this->language = $this->session->language = Config::get('language.default');

		$this->view->addFunction(new \Twig_SimpleFunction('config', function ($name) {
			return Config::get($name);
		}));

		$this->view->addFilter(new \Twig_SimpleFilter('intl', function ($text, array $args = []) {
			return call_user_func_array([$this, 'translate'], array_merge([$text], $args));
		}, ['is_variadic' => true]));

		$this->assign([
			'language'   => $this->language,
			'auth'       => Config::get('auth'),
			'session'    => $this->session,
			'request'    => $this->getRequest(),
		]);
	}

	public function translate($text, ...$args) {
		static $phrases;

		if ( !isset($phrases) )
			$phrases = file_exists($file = APP_DIR . '/languages/' . $this->language . '.ini')
				? parse_ini_file($file) : [];

		return vsprintf(array_key_exists($key = md5($text), $phrases) ? $phrases[$key] : $text, $args);
	}
}