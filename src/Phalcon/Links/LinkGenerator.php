<?php

namespace VideoRecruit\Phalcon\Links;

use League\Uri\Components\Query;
use League\Uri\Schemes\Http;
use Phalcon\Mvc\Url;

/**
 * Class LinkGenerator
 *
 * @package VideoRecruit\Phalcon\Links
 */
class LinkGenerator
{

	/**
	 * @var Http
	 */
	private $url;

	/**
	 * @var Url
	 */
	private $resolver;

	/**
	 * @var array
	 */
	private $proxies;

	/**
	 * LinkGenerator constructor.
	 *
	 * @param Url $resolver
	 * @param array $proxies
	 */
	public function __construct(Url $resolver, array $proxies = NULL)
	{
		if ($proxies) {
			foreach ($proxies as &$proxy) {
				$proxy = trim($proxy);
			}
			unset($proxy);
		}

		$this->url = Http::createFromServer($_SERVER);
		$this->resolver = $resolver;
		$this->proxies = $proxies ?: [];

		$this->checkProxy();
	}

	/**
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	public function link($path, array $params = [])
	{
		$absolute = FALSE;

		if (strpos($path, '//') !== FALSE) {
			$absolute = TRUE;
			$path = substr($path, 2);
		}

		$query = new Query($_SERVER['QUERY_STRING']);
		$urlPath = str_replace($query->getValue('_url'), '', $this->url->getPath()) . '/';

		$resolverParams = [
			$path,
			$params,
			NULL,
			($absolute ? $this->url->getScheme() . '://' . $this->url->getAuthority() : '') . $urlPath,
		];

		if (count(explode(':', $path)) !== 0) {
			$resolverParams[0] = array_merge($params, [
				'for' => $path,
			]);
			$resolverParams[1] = NULL;
		}

		return $this->resolver->get(...$resolverParams);
	}

	/**
	 * @param bool $absolute
	 * @return string
	 */
	public function getCurrentUrl($absolute = FALSE)
	{
		if ($absolute) {
			return (string) $this->url;
		}

		return $this->url->getPath() . ($this->url->getQuery() ? '?' . $this->url->getQuery() : '');
	}

	/**
	 * @return void
	 */
	private function checkProxy()
	{
		$remoteAddress = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;
		$usingTrustedProxy = $remoteAddress && array_filter($this->proxies, function ($proxy) use ($remoteAddress) {
			return Helpers::ipMatch($remoteAddress, $proxy);
		});

		if ($usingTrustedProxy) {
			if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
				$scheme = strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0 ? 'https' : 'http';
				$this->url = $this->url->withScheme($scheme);
			}

			if (!empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
				$this->url = $this->url->withPort((int) $_SERVER['HTTP_X_FORWARDED_PORT']);
			}
		}
	}
}
