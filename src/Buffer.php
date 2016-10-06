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
        $this->position += min($count, $this->length);

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
}
