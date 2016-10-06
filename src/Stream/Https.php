<?php

namespace PG\NtlmSoap\Stream;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
        $accessor = PropertyAccess::createPropertyAccessor();
        $contextParams = stream_context_get_params($context);

        try {
            $cert = $accessor->getValue($contextParams, '[options][ssl][local_cert]');
            curl_setopt($adapter, CURLOPT_CAINFO, $cert);
        } catch (AccessException $e) {  }

        return $adapter;
    }
}
