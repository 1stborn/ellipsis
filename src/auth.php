<?php
namespace Ellipsis;

use Corpus\Config;
use Corpus\Session;
use GuzzleHttp\Client;

/**
 * Class Auth
 *
 * @property Client  http
 * @property DB      db
 * @property Session session
 *
 * @package Ellipsis
 */
abstract class Auth extends Di {
    abstract public function authorize($key, $provider);
	abstract public function create($key, $provider);

	public function valid() {
		return (bool)$this->session->user;
	}

	public function get($name) {
		return $this->valid() ? $this->session->user[$name] : null;
	}

	public function set($name, $value = null) {
		if ( is_array($name) ) {
			$this->session->user = Config::merge($this->session->user ?? [], $name);
			$this->save($name);
		}
		else {
			$this->session->user = Config::merge($this->session->user ?? [], [$name =>$value]);
			$this->save([$name => $value]);
		}
	}

	public function logout() {
		$this->session->user = null;
	}

	public function upload($image) {
		if ( $id = $this->get('id') ) {
		    $name = Config::get('settings.public.images') . $id . '.jpg';
                if (function_exists('imagejpeg')) {
                    return imagejpeg(imagecreatefromstring(file_get_contents($image)),$name, 100);
                } else if (class_exists('Imagick')) {
                    $imagick = new \Imagick($image);
                    $imagick->setImageFormat('jpeg');
                    $imagick->setCompressionQuality(100);
                    file_put_contents($name, $imagick);
                }
		}

		return false;
	}

	public function authorize($data) {

		$this->session->user = $data;
	}
}