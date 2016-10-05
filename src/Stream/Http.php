<?php

namespace PG\NtlmSoap\Stream;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author Patryk Grudniewski <patgrudniewski@gmail.com>
 */
class Http
{
    /**
     * @var resource
     */
    public $context;

    /**
     * @var string
     */
    protected $buffer;

    /**
     * @var resource
     */
    protected $adapter;

    public function stream_open($path, $mode, $options, $openedPath)
    {
        $adapter = curl_init($path);
        curl_setopt($adapter, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($adapter, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($adapter, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);

        $accessor = PropertyAccess::createPropertyAccessor();
        $contextParams = stream_context_get_params($this->context);

        try {
            $headers = $accessor->getValue($contextParams, '[options][http][header]');
        } catch (AccessException $e) {
            $headers = '';
        }
        $matches = [];
        if (preg_match('/^Authorization: [^ ]+ (.*)$/m', $headers, $matches)) {
            curl_setopt($adapter, CURLOPT_USERPWD, base64_decode($matches[1]));
        }

        $this->adapter = $adapter;

        return true;
    }

    public function stream_close()
    {
        curl_close($this->adapter);
        $this->adapter = null;
    }

    public function stream_read()
    {
        throw new \Exception('Method ' . __METHOD__ . ' not implemented');
    }

    public function stream_write()
    {
        throw new \Exception('Method ' . __METHOD__ . ' not implemented');
    }

    public function stream_eof()
    {
        throw new \Exception('Method ' . __METHOD__ . ' not implemented');
    }

    public function stream_tell()
    {
        throw new \Exception('Method ' . __METHOD__ . ' not implemented');
    }

    public function stream_flush()
    {
        throw new \Exception('Method ' . __METHOD__ . ' not implemented');
    }

    public function stream_stat()
    {
        throw new \Exception('Method ' . __METHOD__ . ' not implemented');
    }

    /**
     * @param string $path
     * @param int $flags
     * @return array
     */
    public function url_stat($path, $flags)
    {
        return parse_url($path);
    }
}
