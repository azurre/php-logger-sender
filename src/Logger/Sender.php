<?php
/**
 * @author Alex Milenin
 * @email  admin@azrr.info
 * @date   13.01.2019
 */

namespace Azurre\Component\Logger;

/**
 * Class Sender
 */
class Sender
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Sender constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $i = 1;
        while (true) {
            $logs = [];
            foreach ($this->config['storages'] as $storagePath) {
                foreach (glob("{$storagePath}*.json", GLOB_NOSORT) as $file) {
                    if (!is_readable($file) || !is_writable($file)) {
                        throw new \Exception("Log file '{$file}' should be readable and writable");
                    }
                    $content = @file_get_contents($file);
                    if ($content) {
                        $logs[$file] = $content;
                    }
                }
            }
            if (\count($logs) < 1) {
                break;
            }
            $data = ['token' => $this->config['token'], 'bulk' => array_values($logs)];
            $client = \Azurre\Component\Http::init();
            $client->post($this->config['api'], $data)->execute();
            $response = @json_decode($client->getResponse(), true);
            if (\is_array($response) && !$response['error']) {
                foreach ($logs as $file => $log) {
                    if (!unlink($file)) {
                        throw new \Exception('Cannot remove log file');
                    }
                }
            } else {
                print_r($response);
            }

            if ($i >= $this->config['max_iterations']) {
                break;
            }
            $i++;
        }
    }
}
