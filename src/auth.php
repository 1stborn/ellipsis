<?php
namespace Ellipsis;

use Corpus\Config;

class Auth extends Di {
	public function authorize($userData) {
		if ( is_scalar($userData) )
			$userData = json_decode($userData, true);

		return $this->session->user = $userData;
	}

	public function isValid() {
		return (bool)$this->session->user;
	}

	public function getName() {
		return $this->getUserData('name');
	}

	public function getUserData($field = null, $default = null) {
		return $field ? $this->session->user[$field] ?? $default : $this->session->user;
	}

	public function setUserData($userData) {
		return $this->session->user = Config::merge((array)$this->session->user, $userData);
	}

	public function logout() {
		$this->session->user = null;
	}
}