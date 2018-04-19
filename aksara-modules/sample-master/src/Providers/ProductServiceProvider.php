<?php

namespace Plugins\SampleMaster\Providers;

use Aksara\Providers\AbstractModuleProvider;

class ProductServiceProvider extends AbstractModuleProvider
{
    public function safeBoot()
    {
        \Eventy::addAction('aksara.init-completed', function () {
            $args = [
                'page_title' => __('sample-master::product.title'),
                'menu_title' => __('sample-master::product.title'),
                'capability' => '',
                'route' => [
                    'slug' => '/sample-product',
                    'args' => [
                        'as' => 'sample-product',
                        'uses' => '\Plugins\SampleMaster\Http\Controllers\ProductController@index',
                    ],
                ]
            ];
            add_admin_sub_menu_route('sample-master', $args);

            $productCreate = [
                'slug' => '/sample-master/create',
                'method' => 'GET',
                'args' => [
                    'as' => 'sample-product-create',
                    'uses' => '\Plugins\SampleMaster\Http\Controllers\ProductController@create',
                ],
            ];

            \AksaraRoute::addRoute($productCreate);

            $productStore = [
                'slug' => '/sample-product/store',
                'method' => 'POST',
                'args' => [
                    'as' => 'sample-product-store',
                    'uses' => '\Plugins\SampleMaster\Http\Controllers\ProductController@store',
                ],
            ];

            \AksaraRoute::addRoute($productStore);

            $productEdit = [
                'slug' => '/sample-product/{id}/edit',
                'method' => 'GET',
                'args' => [
                    'as' => 'sample-product-edit',
                    'uses' => '\Plugins\SampleMaster\Http\Controllers\ProductController@edit',
                ],
            ];

            \AksaraRoute::addRoute($productEdit);

            $productUpdate = [
                'slug' => '/sample-product/{id}/update',
                'method' => 'PUT',
                'args' => [
                    'as' => 'sample-product-update',
                    'uses' => '\Plugins\SampleMaster\Http\Controllers\ProductController@update',
                ],
            ];

            \AksaraRoute::addRoute($productUpdate);

            $productDestroy = [
                'slug' => '/sample-product/{id}/destroy',
                'method' => 'GET',
                'args' => [
                    'as' => 'sample-product-destroy',
                    'uses' => '\Plugins\SampleMaster\Http\Controllers\ProductController@destroy',
                ],
            ];

            \AksaraRoute::addRoute($productDestroy);
        });
    }
}
