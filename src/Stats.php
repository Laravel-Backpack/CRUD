<?php

namespace Backpack\CRUD;

trait Stats
{
    /**
     * Check if the application is running in normal conditions
     * (production env, not in console, not in unit tests).
     *
     * @return void
     */
    private function runningInProduction()
    {
        if ($this->app->environment('local')) {
            return false;
        }

        if ($this->app->runningInConsole()) {
            return false;
        }

        if ($this->app->runningUnitTests()) {
            return false;
        }

        return true;
    }

    /**
     * Send usage statistics to the BackpackForLaravel.com website.
     * Used to track unlicensed usage and general usage statistics.
     *
     * No GDPR implications, since no client info is send, only server info.
     *
     * @return void
     */
    private function sendUsageStats()
    {
        // only send usage stats in production
        if (! $this->runningInProduction()) {
            return;
        }

        // only send stats every ~100 pageloads
        if (rand(1, 100) != 1) {
            return;
        }

        $url = 'https://backpackforlaravel.com/api/stats';
        $method = 'PUT';
        $stats = [
            'URL'                   => url('') ?? false,
            'HTTP_HOST'             => $_SERVER['HTTP_HOST'] ?? false,
            'APP_URL'               => $_SERVER['APP_URL'] ?? false,
            'APP_ENV'               => $this->app->environment() ?? false,
            'APP_DEBUG'             => $_SERVER['APP_DEBUG'] ?? false,
            'SERVER_ADDR'           => $_SERVER['SERVER_ADDR'] ?? false,
            'SERVER_ADMIN'          => $_SERVER['SERVER_ADMIN'] ?? false,
            'SERVER_NAME'           => $_SERVER['SERVER_NAME'] ?? false,
            'SERVER_PORT'           => $_SERVER['SERVER_PORT'] ?? false,
            'SERVER_PROTOCOL'       => $_SERVER['SERVER_PROTOCOL'] ?? false,
            'SERVER_SOFTWARE'       => $_SERVER['SERVER_SOFTWARE'] ?? false,
            'DB_CONNECTION'         => $_SERVER['DB_CONNECTION'] ?? false,
            'LARAVEL_VERSION'       => $this->app->version() ?? false,
            'BACKPACK_CRUD_VERSION' => \Composer\InstalledVersions::getVersion('backpack/crud') ?? false,
            'BACKPACK_LICENSE'      => config('backpack.base.license_code') ?? false,
            'BACKPACK_URL'          => backpack_url('') ?? false,
        ];

        // send this info to the main website to store it in the db
        if (function_exists('exec') && extension_loaded('curl')) {
            $this->makeCurlRequest($method, $url, $stats);
        } else {
            $this->makeGuzzleRequest($method, $url, $stats);
        }
    }

    /**
     * Make a request using CURL.
     *
     * It spins up a separate process for this, and doesn't listen for a reponse,
     * so it has minimal to no impact on pageload.
     *
     * @param string $method  HTTP Method to use for the request.
     * @param string $url     URL to point the request at.
     * @param array  $payload The data you want sent to the URL.
     *
     * @return void
     */
    private function makeCurlRequest($method, $url, $payload)
    {
        $cmd = 'curl -X '.$method." -H 'Content-Type: application/json'";
        $cmd .= " -d '".json_encode($payload)."' "."'".$url."'";
        $cmd .= ' > /dev/null 2>&1 &';

        exec($cmd, $output, $exit);

        return $exit == 0;
    }

    /**
     * Make a request using Guzzle.
     *
     * This request happens in the same process as the page load,
     * and Guzzle listens for a response, so depending on the API latency and
     * geographic location this is usually slower than CURL. However,
     * unlike CURL, it works on most machines, so it's reliable.
     *
     * @param string $method  HTTP Method to use for the request.
     * @param string $url     URL to point the request at.
     * @param array  $payload The data you want sent to the URL.
     *
     * @return void
     */
    private function makeGuzzleRequest($method, $url, $payload)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request($method, $url, [
                'form_params'         => $payload,
                'http_errors'         => false,
                'connect_timeout'     => 0.5,
                'timeout'             => 0.5,
            ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // do nothing
        }
    }
}
