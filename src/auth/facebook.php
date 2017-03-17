<?php
namespace Ellipsis\Auth;

use Corpus\Config;
use Ellipsis\Auth;

abstract class Facebook {
	public static $fields = [
		'id',
		'first_name',
		'last_name',
		'email'
	];

	public static function authorize(Auth $auth, $code) {
		$token = json_decode($auth->http->get(
			'https://graph.facebook.com/v' . Config::get('auth.providers.facebook.version') . '/oauth/access_token' .
			'?client_id=' . Config::get('auth.providers.facebook.id') .
			'&client_secret=' . Config::get('auth.providers.facebook.secret') .
			'&redirect_uri=' . Config::get('auth.base_url') . DS . 'facebook' .
			'&code=' . $code)->getBody()->getContents())->access_token;

		$user = json_decode($auth->http->get(
			"https://graph.facebook.com/me" .
			"?fields=" . implode(',', static::$fields) .
			"&access_token={$token}")->getBody()->getContents(), true);

		$image = $auth->http->get(
			"https://graph.facebook.com/me/picture" .
			"?width=200".
			"&height=200" .
			"&access_token={$token}")->getBody()->getContents();

		$auth->authorize($user['id'], 'facebook') or
			$auth->create($user['id'], 'facebook');

        $auth->upload($image);

		$auth->set([
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
        ]);
	}
}