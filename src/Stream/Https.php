<?php

namespace PG\NtlmSoap\Stream;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Patryk Grudniewski <patgrudniewski@gmail.com>
 */
class Https extends Http
{
    /**
     * {@inheritdoc}
     */
    protected function initAdapter($path, $context)
    {
        $adapter = parent::initAdapter($path, $context);
        $accessor = new PropertyAccessor(false, true);
        $contextParams = stream_context_get_params($context);

        try {
            $cert = $accessor->getValue($contextParams, '[options][ssl][local_cert]');
            curl_setopt($adapter, CURLOPT_CAINFO, $cert);
        } catch (AccessException $e) {  }

        try {
            $verify = $accessor->getValue($contextParams, '[options][ssl][verify_peer]');
            curl_setopt($adapter, CURLOPT_SSL_VERIFYPEER, $verify);
        } catch (AccessException $e) {  }

        return $adapter;
    }
}
