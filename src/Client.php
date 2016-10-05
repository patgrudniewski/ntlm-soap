<?php

namespace PG\NtlmSoap;

/**
 * @author Patryk Grudniewski <patgrudniewski@gmail.com>
 */
class Client extends \SoapClient
{
    /**
     * {@inheritdoc}
     */
    public function __construct($wsdl, array $options = [])
    {
        stream_wrapper_unregister('http');
        stream_wrapper_unregister('https');

        if (!stream_wrapper_register('http', Stream\Http::CLASS, STREAM_IS_URL)) {
            throw new Exception\StreamRegistrationFailedException(Stream\Http::CLASS);
        }

        if (!stream_wrapper_register('https', Stream\Https::CLASS, STREAM_IS_URL)) {
            throw new Exception\StreamRegistrationFailedException(Stream\Https::CLASS);
        }

        parent::__construct($wsdl, $options);

        stream_wrapper_restore('http');
        stream_wrapper_restore('https');
    }

    /**
     * {@inheritdoc}
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        throw new \Exception('Method ' . __METHOD__ . ' not implemented');
    }
}
