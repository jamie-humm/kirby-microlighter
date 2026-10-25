<?php

namespace JamieHumm\Microlighter;

use Kirby\Cms\App;
use Kirby\Cms\Response;

/**
 * Emits the MicroLighter stylesheets + script exactly once per request,
 * and only when a page actually contains a code block.
 */
class Assets
{
	// Public URL prefix served by the route below
	public const PREFIX = 'microlighter-assets';

	protected static bool $loaded = false;

	/**
	 * Configured theme, falling back to `github` if the name doesn't match a bundled theme
	 */
	public static function theme(): string
	{
		$theme = option('jamie-humm.kirby-microlighter.theme', 'github');

		if (is_string($theme) === false || is_file(__DIR__ . '/dist/themes/' . basename($theme) . '.css') === false) {
			return 'github';
		}

		return $theme;
	}

	/**
	 * Configured colour scheme: `light`, `dark` or `auto` (follows the visitor's preference)
	 */
	public static function mode(): string
	{
		$mode = option('jamie-humm.kirby-microlighter.mode', 'auto');

		return in_array($mode, ['light', 'dark'], true) ? $mode : 'auto';
	}

	public static function once(): string
	{
		if (static::$loaded === true) {
			return '';
		}
		static::$loaded = true;

		// ?v= busts the browser cache when the plugin's files change
		$version = '?v=' . filemtime(__DIR__ . '/dist/micro-lighter-element.min.js');
		$base    = url(static::PREFIX);

		$html = '<link rel="stylesheet" href="' . esc($base . '/themes/' . static::theme() . '.css' . $version, 'attr') . '">' . PHP_EOL;

		// Default block styles live in ./assets, which Kirby serves from /media
		// with a cache-busting hash in the URL
		if (option('jamie-humm.kirby-microlighter.css', true) === true) {
			$css   = App::instance()->plugin('jamie-humm/kirby-microlighter')->asset('microlighter.css')->url();
			$html .= '<link rel="stylesheet" href="' . esc($css, 'attr') . '">' . PHP_EOL;
		}

		// micro-lighter-element.min.js is self-contained (highlighter + <micro-lighter>).
		// Language grammars lazy-load from ./grammars/ relative to the script URL,
		// which the route below also serves.
		$html .= '<script type="module" src="' . esc($base . '/micro-lighter-element.min.js' . $version, 'attr') . '"></script>' . PHP_EOL;

		return $html;
	}
}

App::plugin('jamie-humm/kirby-microlighter', [
	'options' => [
		// github, vscode-plus, dracula, monokai, night-owl, solarized-light,
		// min, cobalt2, flexoki, gruvbox, tokyo-night, vesper
		'theme' => 'github',
		// auto (follows the visitor's preference), light or dark
		'mode' => 'auto',
		// Load the plugin's default block styles (assets/microlighter.css)
		'css' => true,
	],
	'routes' => [
		[
			// Serves the bundled files from ./dist (scripts + stylesheets only)
			'pattern'  => Assets::PREFIX . '/(:all)',
			// No 'language' key: static files are the same in every language, and
			// 'language' => '*' would pass the language as the first argument
			'method'   => 'GET',
			'action'   => function (string $path) {
				$root = realpath(__DIR__ . '/dist');
				$file = realpath($root . '/' . $path);

				// Block path traversal and anything that isn't a real file inside ./dist
				if (
					$root === false ||
					$file === false ||
					str_starts_with($file, $root . DIRECTORY_SEPARATOR) === false ||
					is_file($file) === false
				) {
					return false;
				}

				$types = [
					'js'  => 'text/javascript',
					'css' => 'text/css',
				];
				$type = $types[strtolower(pathinfo($file, PATHINFO_EXTENSION))] ?? null;

				if ($type === null) {
					return false;
				}

				return new Response(file_get_contents($file), $type, 200, [
					'Cache-Control' => 'public, max-age=86400',
				]);
			},
		],
	],
	'blueprints' => [
		'blocks/microcode' => __DIR__ . '/blueprints/blocks/microcode.yml',
	],
	'snippets' => [
		'blocks/microcode' => __DIR__ . '/snippets/blocks/microcode.php',
	],
]);
