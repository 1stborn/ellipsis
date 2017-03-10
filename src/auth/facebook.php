<?php
namespace Ellipsis\Auth;

use Corpus\Config;
use Ellipsis\Auth;

abstract class Facebook {
	public static $fields = [
		'id',
		'first_name',
		'last_name',
		'locale',
		'timezone',
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
			"?type=square" .
			"&access_token={$token}")->getBody()->getContents();

		if ( $id = $auth->account('facebook', $user['id']) )
			$auth->authorize($id);
		else
			$auth->create($user['id'], 'facebook');

		$data = [
			'first_name' => $user['first_name'],
			'last_name'  => $user['last_name'],
			'email'      => $user['email'],
		];

		if ( $name = $auth->upload($image) )
			$data['picture'] = $name;

		$auth->set($data);
	}
}