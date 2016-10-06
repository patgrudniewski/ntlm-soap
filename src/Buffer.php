<?php

namespace PG\NtlmSoap;

/**
 * @author Patryk Grudniewski <patgrudniewski@gmail.com>
 */
class Buffer
{
    /**
     * @var string
     */
    protected $buffer;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var int
     */
    protected $length;

    /**
     * @param string $buffer
     * @return void
     */
    public function __construct($buffer)
    {
        $this->buffer = $buffer;
        $this->position = 0;
        $this->length = strlen($buffer);
    }

    /**
     * @param int $count
     * @return string
     */
    public function read($count)
    {
        $read = substr($this->buffer, $this->position, $count);

        $this->seek($count, SEEK_CUR);

        return $read;
    }

    /**
     * @return bool
     */
    public function isEOF()
    {
        return $this->length == $this->position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return void
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        switch ($whence) {
            case SEEK_CUR:
                $pos = $this->position;
                break;
            case SEEK_END:
                $pos = $this->length;
                break;
            case SEEK_SET:
            default:
                $pos = 0;
                break;
        }

        $this->position = min($pos + $offset, $this->length);
    }
}
