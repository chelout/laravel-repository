<?php

/*
 * Laravel Repository Config
 */
return [
    /*
     * Repository Pagination Limit Default
     */
    'pagination' => [
        'limit' => 15,
    ],

    /*
     * Cache Config
     */
    'cache' => [
        /*
         * Cache Status
         * Enable or disable cache
         */
        'enabled' => true,

        /*
         * Cache Minutes
         * Time of expiration cache
         */
        'minutes' => 5,

        /*
         * Cache Repository
         * Instance of Illuminate\Contracts\Cache\Repository
         */
        'repository' => 'cache',

        /*
         * Cache Clean Listener
         */
        'clean' => [
            /*
             * Enable clear cache on repository changes
             */
            'enabled' => true,

            /*
             * Actions in Repository
             *
             * create : Clear Cache on create Entry in repository
             * update : Clear Cache on update Entry in repository
             * delete : Clear Cache on delete Entry in repository
             */
            'on' => [
                'create' => true,
                'update' => true,
                'delete' => true,
            ],
        ],

        'params' => [
            /*
             * Skip Cache Params
             * Example: http://repository.test/?skip_cache=true
             */
            'skip_cache' => 'skip_cache',
        ],

        /*
         * Methods Allowed
         *
         * methods cacheable : all, paginate, find, findByField, findWhere, getByScope
         * Example:
         * 'only'  =>['all','paginate'],
         * or
         * 'except'  =>['find'],
         */
        'allowed' => [
            'only' => null,
            'except' => null,
        ],
    ],
];
