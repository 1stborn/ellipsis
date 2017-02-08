<?php
namespace Ellipsis\View;

use Ellipsis\Di;
use Parsedown;
use Twig_Error_Loader;

class Loader extends Di implements \Twig_LoaderInterface {
	const FORMAT_PLAINTEXT = 'plaintext';
	const FORMAT_MARKDOWN = 'markdown';

	/**
	 * Gets the source code of a template, given its name.
	 *
	 * @param string $name The name of the template to load
	 *
	 * @return string The template source code
	 *
	 * @throws Twig_Error_Loader When $name is not found
	 */
	public function getSource($name) {
		list($content, $format) = $this->obtain('content, format', $name, 'current', [null, null]);

		switch ($format) {
			case self::FORMAT_MARKDOWN:
				return ( new Parsedown )->text($content);
			case self::FORMAT_PLAINTEXT:
				return $content;
			default:
				throw new Twig_Error_Loader("Unable to locate {$name}");
		}
	}

	/**
	 * Gets the cache key to use for the cache for a given template name.
	 *
	 * @param string $name The name of the template to load
	 *
	 * @return string The cache key
	 *
	 * @throws Twig_Error_Loader When $name is not found
	 */
	public function getCacheKey($name) {
		return $this->session->language . '/' . $name;
	}

	/**
	 * Returns true if the template is still fresh.
	 *
	 * @param string $name The template name
	 * @param int    $time Timestamp of the last modification time of the
	 *                     cached template
	 *
	 * @return bool true if the template is fresh, false otherwise
	 *
	 * @throws Twig_Error_Loader When $name is not found
	 */
	public function isFresh($name, $time) {
		return $this->obtain('UNIX_TIMESTAMP(edited_at)', $name) < $time;
	}

	private function obtain($value, $name, $exec = 'scalar', $default = null) {
		static $cache = [];
		if ( !array_key_exists($key = $value . '_' . $name, $cache) )
			$cache[$key] = $this->db->$exec(
				"SELECT {$value} FROM pages WHERE path = :path", ['path' => $name]
			);

		return $cache[$key] ?: $default;
	}
}