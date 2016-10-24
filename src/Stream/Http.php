<?php

namespace PG\NtlmSoap\Stream;

use PG\NtlmSoap\Buffer;
use PG\NtlmSoap\Exception\CurlRequestException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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
     * @var Buffer
     */
    protected $buffer;

    /**
     * @var resource
     */
    protected $adapter;

    /**
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string &$openedPath
     * @return bool
     */
    final public function stream_open($path, $mode, $options, &$openedPath)
    {
        $this->adapter = $this->initAdapter($path, $this->context);

        try {
            $this->buffer = $this->initBuffer($this->adapter);
        } catch (CurlRequestException $e) {
            return false;
        }

        $openedPath = $path;

        return true;
    }

    /**
     * @return void
     */
    final public function stream_close()
    {
        curl_close($this->adapter);

        $this->adapter = null;
        $this->buffer = null;
    }

    /**
     * @param int $count
     * @return string
     */
    final public function stream_read($count)
    {
        return $this->buffer->read($count);
    }

    /**
     * @param int $cast_as
     * @return resource
     */
    final public function stream_cast($cast_as)
    {
        return $this->adapter;
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    final public function stream_seek($offset, $whence = SEEK_SET)
    {
        $this->buffer->seek($offset, $whence);

        return true;
    }

    /**
     * @return bool
     */
    final public function stream_eof()
    {
        return $this->buffer->isEOF();
    }

    /**
     * @return bool
     */
    final public function stream_flush()
    {
        return true;
    }

    /**
     * @return int
     */
    final public function stream_tell()
    {
        return $this->buffer->getPosition();
    }

    /**
     * @return array
     */
    final public function stream_stat()
    {
        return [
            'size' => $this->buffer->getLength(),
        ];
    }

    /**
     * @param string $path
     * @param int $flags
     * @return array
     */
    final public function url_stat($path, $flags)
    {
        return parse_url($path);
    }

    /**
     * @return resource
     */
    protected function initAdapter($path, $context)
    {
        $adapter = curl_init($path);
        curl_setopt($adapter, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($adapter, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($adapter, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);

        $accessor = new PropertyAccessor(false, true);
        $contextParams = stream_context_get_params($context);

        // get headers from context params
        $headers = [];
        try {
            $raw = $accessor->getValue($contextParams, '[options][http][header]');
            $headers = $this->parseHeaders($raw);
        } catch (AccessException $e) {  }

        // unset connection header
        if (array_key_exists('connection', $headers)) {
            unset($headers['connection']);
        }

        // get auth info
        $matches = [];
        if (
            array_key_exists('authorization', $headers)
            && preg_match('/^Authorization: [^ ]+ (.*)$/m', $headers['authorization'], $matches)
        ) {
            curl_setopt($adapter, CURLOPT_USERPWD, base64_decode($matches[1]));
            unset($headers['authorization']);
        }

        curl_setopt($adapter, CURLOPT_HTTPHEADER, array_values($headers));

        // get http timeout
        try {
            if ($timeout = $accessor->getValue($contextParams, '[options][http][timeout]')) {
                curl_setopt($adapter, CURLOPT_TIMEOUT, (int)$timeout);
            }
        } catch (AccessException $e) {  }

        // get http method from context params
        try {
            $method = strtoupper($accessor->getValue($contextParams, '[options][http][method]'));
            curl_setopt($adapter, CURLOPT_CUSTOMREQUEST, $method);
        } catch (AccessException $e) {  }

        // get http content from context params
        try {
            $content = $accessor->getValue($contextParams, '[options][http][content]');
            curl_setopt($adapter, CURLOPT_POSTFIELDS, $content);
        } catch (AccessException $e) {  }

        return $adapter;
    }

    /**
     * @param string $raw
     * @return array
     */
    protected function parseHeaders($raw)
    {
        $headers = [];

        $rawArray = explode("\n", $raw);
        foreach ($rawArray as $header) {
            $matches = [];
            if (!preg_match('/^([^:]+): (.+)$/', $header, $matches)) {
                continue;
            };
            $headers[strtolower($matches[1])] = $matches[0];
        }

        return $headers;
    }

    /**
     * @param resource $adapter
     * @throws CurlRequestException
     * @return Buffer
     */
    private function initBuffer($adapter)
    {
        $response = curl_exec($adapter);
        if (false === $response) {
            throw new CurlRequestException($adapter);
        }

        return new Buffer($response);
    }
}
