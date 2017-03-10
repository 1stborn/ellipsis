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
	abstract protected function save($data);

	abstract public function create($id, $provider);

	abstract public function account($id, $provider);

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
		if ( $this->valid() ) {
			$id = $this->session->user['id'];

			if ( $info = getimagesizefromstring($image) ) {
				switch ($info[2]) {
					case IMAGETYPE_JPEG:
					case IMAGETYPE_JPEG2000:
						$name = $id . '.jpg';
					break;
					case IMAGETYPE_GIF:
						$name = $id . '.gif';
					break;
					case IMAGETYPE_PNG:
						$name = $id . '.png';
					break;
					default:
						$name = $id;
				}

				return file_put_contents(
					Config::get('settings.public.images') . $name, $image
				) ? $name : false;
			}
		}

		return false;
	}

	public function authorize($data) {

		$this->session->user = $data;
	}
}