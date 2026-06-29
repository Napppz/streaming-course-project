<?php

namespace Config;

use App\Services\Payments\XenditPaymentLinkService;
use App\Services\Payments\XenditPaymentStatusMapper;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function xenditStatusMapper(bool $getShared = true): XenditPaymentStatusMapper
    {
        if ($getShared) {
            return static::getSharedInstance('xenditStatusMapper');
        }

        return new XenditPaymentStatusMapper();
    }

    public static function xenditHttpClient(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('xenditHttpClient');
        }

        /** @var Xendit $config */
        $config = config('Xendit');

        return parent::curlrequest([
            'baseURI' => rtrim($config->baseUrl, '/') . '/',
            'timeout' => $config->timeout,
            'connect_timeout' => $config->connectTimeout,
            'verify' => static::xenditCaBundlePath(),
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($config->secretKey . ':'),
            ],
        ], null, null, false);
    }

    public static function xenditPaymentLinks(bool $getShared = true): XenditPaymentLinkService
    {
        if ($getShared) {
            return static::getSharedInstance('xenditPaymentLinks');
        }

        /** @var Xendit $config */
        $config = config('Xendit');

        return new XenditPaymentLinkService(
            static::xenditHttpClient(false),
            $config,
            static::xenditStatusMapper(false)
        );
    }

    private static function xenditCaBundlePath(): bool|string
    {
        $candidates = [
            'C:\\xampp\\apache\\bin\\curl-ca-bundle.crt',
            'C:\\xampp\\phpMyAdmin\\vendor\\composer\\ca-bundle\\res\\cacert.pem',
            'C:\\xampp\\perl\\vendor\\lib\\Mozilla\\CA\\cacert.pem',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return true;
    }
}
