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
        static::registerStreamWrappers();
        parent::__construct($wsdl, $options);
        static::restoreStreamWrappers();

    }

    /**
     * {@inheritdoc}
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $this->__last_request = $request;

        $context = stream_context_get_params($this->_stream_context);
        $context = array_merge_recursive($context['options'], [
            'http' => [
                'method' => 'POST',
                'content' => $request,
                'header' => [
                    sprintf('SOAPAction: %s', $action),
                    'Content-Type: text/xml; charset=utf-8',
                    'User-Agent: PHP-NTLM-SOAP',
                ]
            ],
        ]);

        if ($this->_login && $this->_password) {
            $context['http']['header'][] = sprintf(
                'Authorization: Basic %s',
                base64_encode("{$this->_login}:{$this->_password}")
            );
        }

        $context['http']['header'] = implode("\n", $context['http']['header']);

        static::registerStreamWrappers();
        $response = file_get_contents($location, false, stream_context_create($context));
        static::restoreStreamWrappers();

        return $response;
    }

    /**
     * @return void
     */
    private static function registerStreamWrappers()
    {
        stream_wrapper_unregister('http');
        stream_wrapper_unregister('https');

        if (!stream_wrapper_register('http', Stream\Http::CLASS, STREAM_IS_URL)) {
            throw new Exception\StreamRegistrationFailedException(Stream\Http::CLASS);
        }

        if (!stream_wrapper_register('https', Stream\Https::CLASS, STREAM_IS_URL)) {
            throw new Exception\StreamRegistrationFailedException(Stream\Https::CLASS);
        }
    }

    /**
     * @return void
     */
    private static function restoreStreamWrappers()
    {
        stream_wrapper_restore('http');
        stream_wrapper_restore('https');
    }
}
