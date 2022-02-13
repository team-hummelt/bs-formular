<?php

namespace Hupa\BsPluginLicense;

use Exception;
use stdClass;

defined('ABSPATH') or die();

/**
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 */

if (!class_exists('HupaApiPluginBSServerHandle')) {
    add_action('plugin_loaded', array('Hupa\\BsPluginLicense\\HupaApiPluginBSServerHandle', 'init'), 0);

    class HupaApiPluginBSServerHandle
    {
        private static $api_filter_instance;

        /**
         * @return static
         */
        public static function init(): self
        {
            if (is_null(self::$api_filter_instance)) {
                self::$api_filter_instance = new self;
            }
            return self::$api_filter_instance;
        }

        public function __construct()
        {
            //TODO Endpoints URL's
            add_filter('get_bs_formular_api_urls', array($this, 'BsFormularGetApiUrl'));
            //TODO JOB POST Resources Endpoints
            add_filter('bs_formular_scope_resource', array($this, 'bsFormularPOSTApiResource'), 10, 2);

            add_filter('hupa_api_scope_resource', array($this, 'bsServerPOSTApiResource'), 10, 2);

            //TODO JOB GET Resources Endpoints
            add_filter('get_scope_resource', array($this, 'BsFormularGETApiResource'), 10, 2);

            //TODO JOB VALIDATE SOURCE BY Authorization Code
            add_filter('get_bs_formular_resource_authorization_code', array($this, 'BsFormularInstallByAuthorizationCode'));

            //TODO JOB SERVER URL ÄNDERN FALLS NÖTIG
            add_filter('bs_formular_update_server_url', array($this, 'BsFormularUpdateServerUrl'));
        }

        public function BsFormularUpdateServerUrl($url)
        {
            update_option('hupa_server_url', $url);
        }

        public function BsFormularGetApiUrl($scope): string
        {
            $client_id = get_option('bs_formular_client_id');
            return match ($scope) {
                'authorize_url' => get_option('hupa_server_url') . 'authorize?response_type=code&client_id=' . $client_id,
                default => '',
            };
        }

        public function BsFormularInstallByAuthorizationCode($authorization_code): object
        {
            $error = new stdClass();
            $error->status = false;
            $client_id = get_option('bs_formular_client_id');
            $client_secret = get_option('bs_formular_client_secret');
            $token_url = get_option('hupa_server_url') . 'token';
            $authorization = base64_encode("$client_id:$client_secret");

            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => "Basic {$authorization}"
                ),
                'body' => [
                    'grant_type' => "authorization_code",
                    'code' => $authorization_code
                ]
            );

            $response = wp_remote_post($token_url, $args);
            if (is_wp_error($response)) {
                $error->message = $response->get_error_message();
                return $error;
            }

            $apiData = json_decode($response['body']);
            if (isset($apiData->error)) {
                $apiData->status = false;
                return $apiData;
            }

            update_option('bs_formular_access_token', $apiData->access_token);
            $body = [
                'version' => BS_FORMULAR_PLUGIN_VERSION,
            ];

            return $this->bsFormularPOSTApiResource('install', $body);
        }

        public function bsFormularPOSTApiResource($scope, $body = false)
        {
            $response = wp_remote_post(get_option('hupa_server_url') . $scope, $this->BsFormularApiPostArgs($body));
            if (is_wp_error($response)) {
                return $response->get_error_message();
            }
            if (is_array($response)) {
                $query = json_decode($response['body']);
                if (isset($query->error)) {
                    if ($this->get_error_message($query)) {
                        $this->BsFormularGetApiClientCredentials();
                    }
                    $response = wp_remote_post(get_option('hupa_server_url') . $scope, $this->BsFormularApiPostArgs($body));
                    if (is_array($response)) {
                        return json_decode($response['body']);
                    }
                } else {
                    return $query;
                }
            }
            return false;
        }

        public function bsServerPOSTApiResource($scope, $body = false)
        {
            $response = wp_remote_post(get_option('hupa_server_url') . $scope, $this->BsFormularApiPostArgs($body));
            if (is_wp_error($response)) {
                return $response->get_error_message();
            }
            if (is_array($response)) {
                $query = json_decode($response['body']);
                if (isset($query->error)) {
                    if ($this->get_error_message($query)) {
                        $this->BsFormularGetApiClientCredentials();
                    }
                    $response = wp_remote_post(get_option('hupa_server_url') . $scope, $this->BsFormularApiPostArgs($body));
                    if (is_array($response)) {
                        return $response['body'];
                    }
                } else {
                    return $response['body'];
                }
            }
            return false;
        }

        public function BsFormularGETApiResource($scope, $body = false)
        {
            $response = wp_remote_get(get_option('hupa_server_url') . $scope, $this->BsFormularGETApiArgs($body));
            if (is_wp_error($response)) {
                return $response->get_error_message();
            }
            if (is_array($response)) {
                $query = json_decode($response['body']);
                if (isset($query->error)) {
                    if ($this->get_error_message($query)) {
                        $this->BsFormularGetApiClientCredentials();
                    }
                    $response = wp_remote_get(get_option('hupa_server_url') . $scope, $this->BsFormularGETApiArgs($body));
                    if (is_array($response)) {
                        return json_decode($response['body']);
                    }
                } else {
                    return $query;
                }
            }
            return false;
        }

        public function BsFormularGETApiResourceOld($scope, $get = [])
        {
            $error = new stdClass();
            $error->status = false;

            $getUrl = '';
            if ($get) {
                $getUrl = implode('&', $get);
                $getUrl = '?' . $getUrl;
            }

            $url = get_option('hupa_server_url') . $scope . $getUrl;
            $args = $this->BsFormularGETApiArgs();

            $response = wp_remote_get($url, $args);
            if (is_wp_error($response)) {
                $error->message = $response->get_error_message();
                return $error;
            }

            $apiData = json_decode($response['body']);
            if ($apiData->error) {
                $errType = $this->get_error_message($apiData);
                if ($errType) {
                    $this->BsFormularGetApiClientCredentials();
                }
            }

            $response = wp_remote_get(get_option('hupa_server_url'), $this->BsFormularGETApiArgs());
            if (is_wp_error($response)) {
                $error->message = $response->get_error_message();
                return $error;
            }
            $apiData = json_decode($response['body']);
            if (!$apiData->error) {
                $apiData->status = true;
                return $apiData;
            }

            $error->error = $apiData->error;
            $error->error_description = $apiData->error_description;
            return $error;
        }

        public function BsFormularApiPostArgs($body = []): array
        {
            $bearerToken = get_option('bs_formular_access_token');
            return [
                'method'        => 'POST',
                'timeout'       => 45,
                'redirection'   => 5,
                'httpversion'   => '1.0',
                'blocking'      => true,
                'sslverify'     => true,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => "Bearer $bearerToken"
                ],
                'body'          => $body
            ];
        }

        private function BsFormularGETApiArgs($body = []): array
        {
            $bearerToken = get_option('bs_formular_access_token');
            return [
                'method' => 'GET',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'sslverify' => true,
                'blocking' => true,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => "Bearer $bearerToken"
                ],
                'body'          => $body
            ];
        }

        private function BsFormularGetApiClientCredentials(): void
        {
            $token_url = get_option('hupa_server_url') . 'token';
            $client_id = get_option('bs_formular_client_id');
            $client_secret = get_option('bs_formular_client_secret');
            $authorization = base64_encode("$client_id:$client_secret");
            $error = new stdClass();
            $error->status = false;
            $args = [
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'sslverify' => true,
                'blocking' => true,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => "Basic $authorization"
                ],
                'body' => [
                    'grant_type' => 'client_credentials'
                ]
            ];
            $response = wp_remote_post($token_url, $args);
            if (!is_wp_error($response)) {
                $apiData = json_decode($response['body']);
                update_option('bs_formular_access_token', $apiData->access_token);
            }
        }

        public function BSFormApiDownloadFile($url, $body = []) {

            $bearerToken = get_option('bs_formular_access_token');
            $args = [
                'method'        => 'POST',
                'timeout'       => 45,
                'redirection'   => 5,
                'httpversion'   => '1.0',
                'blocking'      => true,
                'sslverify'     => true,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => "Bearer $bearerToken"
                ],
                'body'          => $body
            ];

            $response = wp_remote_post( $url, $args );

            if (is_wp_error($response)) {
                $this->BsFormularGetApiClientCredentials();
            }

            $response = wp_remote_post( $url, $args );

            if (is_wp_error($response)) {
                print_r($response->get_error_message());
                exit();
            }

            if( !is_array( $response ) ) {
                exit('Download Fehlgeschlagen!');
            }
            return $response['body'];
        }

        private function get_error_message($error): bool
        {
            $return = false;
            switch ($error->error) {
                case 'invalid_grant':
                case 'insufficient_scope':
                case 'invalid_request':
                    $return = false;
                    break;
                case'invalid_token':
                    $return = true;
                    break;
            }
            return $return;
        }

    }
}

global $bsFormApiSrv;
$bsFormApiSrv = HupaApiPluginBSServerHandle::init();