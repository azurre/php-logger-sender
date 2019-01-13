<?php
/**
 * @author Alex Milenin
 * @email  admin@azrr.info
 * @date   09.12.2018
 */

namespace Azurre\Component\Logger\Handler;

/**
 * Class Json
 */
class Json implements \Azurre\Component\Logger\Handler\HandlerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $storages;

    /**
     * @var \Closure
     */
    protected $prepareCallback;

    /**
     * @var array
     */
    protected $data;

    /**
     * Json constructor.
     *
     * @param string|array $storages
     */
    public function __construct($storages = 'logs/')
    {
        $storages = \is_array($storages) ? $storages : [$storages];
        $this->setStorages($storages);
    }

    /**
     * @param string $channel
     * @param string $level
     * @param string $message
     * @param array  $data
     * @return $this
     */
    public function handle($channel, $level, $message = '', array $data = null)
    {
        $logLine = $this->prepare($channel, $level, $message, $data);
        $size = \strlen($logLine);
        $name = uniqid(round(microtime(true) * 10000) . '_', true) . '.json';
        $success = false;
        foreach ($this->getStorages() as $storage) {
            $writeBytes = @file_put_contents("{$storage}/{$name}", $logLine, FILE_APPEND);
            if ($writeBytes === $size) {
                $success = true;
                break;
            }
        }
        if (!$success) {
            throw new \RuntimeException('Cannot write log');
        }

        return $this;
    }

    /**
     * @param string $channel
     * @param string $level
     * @param string $message
     * @param array  $data
     * @return string
     */
    protected function prepare($channel, $level, $message = '', array $data = null)
    {
        $log = [
            'timestamp' => microtime(true),
            'pid' => getmypid(),
            'channel' => $channel,
            'level' => $level,
            'message' => $message
        ];
        if ($this->data) {
            $log = array_merge($log, $this->data);
        }
        if (!empty($data)) {
            $log['data'] = $data;
        }
        if ($this->prepareCallback) {
            $log = $this->prepareCallback->call($this, $log, $channel, $level, $message, $data);
        }

        return json_encode($log);
    }

    /**
     * @param \Closure $prepareCallback
     * @return $this
     */
    public function setPrepareCallback(\Closure $prepareCallback)
    {
        $this->prepareCallback = $prepareCallback;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getStorages()
    {
        return $this->storages;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param array $storages
     * @return Json
     */
    public function setStorages(array $storages)
    {
        foreach ($storages as $storage) {
            if (!is_dir($storage)) {
                !mkdir($storage) && !is_dir($storage);
            }
        }
        $this->storages = $storages;

        return $this;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @return $this
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
