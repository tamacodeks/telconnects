<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;

class LocalUrlGenerator extends UrlGenerator
{
    /**
     * @var callable|null
     */
    protected $useRequestSchemeResolver;

    /**
     * @param  \Illuminate\Routing\RouteCollection  $routes
     * @param  \Illuminate\Http\Request  $request
     * @param  callable|null  $useRequestSchemeResolver
     * @return void
     */
    public function __construct(
        RouteCollection $routes,
        Request $request,
        callable $useRequestSchemeResolver = null
    ) {
        parent::__construct($routes, $request);

        $this->useRequestSchemeResolver = $useRequestSchemeResolver;
    }

    /**
     * Get the default scheme for a raw URL.
     *
     * In local development, secure_* helpers should follow the current request
     * scheme so localhost HTTP setups still render usable links and assets.
     *
     * @param  bool|null  $secure
     * @return string
     */
    public function formatScheme($secure)
    {
        if ($secure === true && $this->shouldUseRequestScheme()) {
            return $this->request->getScheme().'://';
        }

        return parent::formatScheme($secure);
    }

    /**
     * Determine if secure_* helpers should follow the current request scheme.
     *
     * @return bool
     */
    protected function shouldUseRequestScheme()
    {
        if (! $this->useRequestSchemeResolver) {
            return false;
        }

        return (bool) call_user_func($this->useRequestSchemeResolver, $this->request);
    }
}
