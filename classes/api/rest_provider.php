<?php

class SocialUserApiProvider implements ezpRestProviderInterface
{
    public function getRoutes()
    {
        $routes = array(

            //POST /api/<prefisso>/v1/create
            'createUser' => new ezpRestVersionedRoute(
                new SocialUserApiRailsRoute(
                    '/create',
                    'SocialUserApiController',
                    'createUser',
                    array(),
                    'http-post'
                ), 1
            ),

            //GET /api/<prefisso>/v1/get
            'getUser' => new ezpRestVersionedRoute(
                new SocialUserApiRailsRoute(
                    '/get/:Identifier',
                    'SocialUserApiController',
                    'getUser',
                    array(),
                    'http-get'
                ), 1
            ),

        );

        return $routes;
    }

    public function getViewController()
    {
        return new SocialUserApiViewController();
    }

}
