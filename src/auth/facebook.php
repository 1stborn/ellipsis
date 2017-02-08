<?php
namespace Ellipsis\Auth;

use Corpus\Config;
use Ellipsis\Auth;

class Facebook {
	public static function authorize(Auth $auth, $code) {
		$token = json_decode($auth->http->get(
			'https://graph.facebook.com/v' . Config::get('auth.providers.facebook.version') . '/oauth/access_token' .
			'?client_id=' . Config::get('auth.providers.facebook.id') .
			'&client_secret=' . Config::get('auth.providers.facebook.secret') .
			'&redirect_uri=' . Config::get('auth.base_url') . DS . 'facebook' .
			'&code=' . $code)->getBody()->getContents())->access_token;

		$user = $auth->auth->authorize($auth->http->get(
			"https://graph.facebook.com/me?fields=id,name,locale,timezone,email&access_token={$token}")
		                                          ->getBody()->getContents());

		if ( $info = getimagesizefromstring(
			$image = $auth->http->get("https://graph.facebook.com/me/picture?type=square&access_token={$token}")
			                    ->getBody()->getContents())
		) {

			switch ($info[2]) {
				case IMAGETYPE_JPEG:
				case IMAGETYPE_JPEG2000:
					$extension = '.jpg';
				break;
				case IMAGETYPE_GIF:
					$extension = '.gif';
				break;
				case IMAGETYPE_PNG:
					$extension = '.png';
				break;
				default:
					$extension = '';
			}

			$picture = $user['id'] . $extension;

			$user = $auth->auth->setUserData(['picture' => $picture]);

			file_put_contents(DOC_DIR . '/images/users/' . $picture, $image);
		}

		$current = $auth->db->current("
            SELECT id, `access` FROM `users` WHERE `provider` = 'fb' AND `key` = :id
        ", ['id' => $user['id']]);

		if ( $current ) {
			list($id, $access) = $current;
		}
		else {
			$access = 1;
			$id     = $auth->db->query(
				"INSERT INTO `users` 
                  SET`provider` = 'fb', `key` = :id 
                  ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)",
				['id' => (int)$user['id']]);
		}

		if ( $id ) {
			$auth->db->query("
              INSERT DELAYED INTO `users_info`
                SET id = :id, `info` = :info
              ON DUPLICATE 
                KEY UPDATE `info` = VALUES(`info`) 
              ", ['info' => serialize($user), 'id' => $id]);
		}

		return $auth->auth->setUserData(['access' => $access]);
	}
}