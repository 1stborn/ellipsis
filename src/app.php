<?php
namespace Ellipsis;

use Corpus\App as Base;
use Corpus\Config;
use Corpus\Db;
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
			'auth' => function ($ci) {
				return new Auth($ci);
			},
			'http' => function () {
				return new Client(Config::get('http'));
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

		$this->view->addFilter(new \Twig_SimpleFilter('intl', function ($ctx, $text, array $args = []) {
			static $phrases;

			if ( !isset($phrases) )
				$phrases = file_exists($file = APP_DIR . '/languages/' . $ctx['language'] . '.ini')
					? parse_ini_file($file) : [];

			return vsprintf(array_key_exists($key = md5($text), $phrases) ? $phrases[$key] : $text, $args);
		}, ['needs_context' => true, 'is_variadic' => true]));

		$this->assign([
			'language'   => $this->language,
			'auth'       => Config::get('auth'),
			'session'    => $this->session,
			'request'    => $this->getRequest(),
		]);
	}

	protected function getView($method = null) {
		return parent::getView($method) ?: $this->getCustomView($method);
	}

	protected function getCustomView($method = null) {
		return $this->view->find($method ?: $this->method);
	}
}