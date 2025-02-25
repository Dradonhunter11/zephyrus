<?php namespace Zephyrus\Network\Responses;

use Zephyrus\Application\Callback;
use Zephyrus\Network\ContentType;
use Zephyrus\Network\Response;

trait StreamResponses
{
    /**
     * Does a simple server-sent event response which will do a simple polling.
     *
     * @param mixed $data
     * @param string $eventId
     * @param int $retry
     * @return Response
     */
    public function ssePolling($data, $eventId = 'stream', $retry = 1000): Response
    {
        $response = new Response(ContentType::SSE);
        $response->addHeader('Cache-Control', 'no-cache');
        ob_start();
        $this->outputSseMessage($eventId, $data, $retry);
        $response->setContent(ob_get_clean());
        return $response;
    }

    /**
     * Does a streaming server-sent event response which will loop and execute
     * the specified callback indefinitely and update the client only when
     * needed.
     *
     * @param $callback
     * @param string $eventId
     * @param int $sleep
     * @return Response
     */
    public function sseStreaming($callback, $eventId = 'stream', $sleep = 1): Response
    {
        $response = new Response(ContentType::SSE);
        $that = $this;
        $response->setContentCallback(function () use ($callback, $eventId, $sleep, $that) {
            $that->initializeStreaming();
            $callbackExecutionCounter = 1;
            $call = new Callback($callback);
            while (connection_status() == CONNECTION_NORMAL && !connection_aborted()) {
                $data = $call->execute();
                if ($data === false) {
                    break;
                }
                if (!empty($data)) {
                    $that->ssePolling($data, $eventId, $sleep * 1000)->sendContent();
                }
                @flush();
                sleep($sleep);
                $callbackExecutionCounter++;

                // Reduce potential memory leaks
                if ($callbackExecutionCounter % 1000 == 0) {
                    gc_collect_cycles();
                    $callbackExecutionCounter = 1;
                }
            }
        });
        return $response;
    }

    /**
     * Used to implement a manual SSE flow (e.g. progressbar). Requires a callback
     * which receives a specific function destined to be used when sending an SSE
     * message to the client side.
     *
     * @param $callback
     * @return Response
     */
    public function sseFlow($callback)
    {
        $response = new Response(ContentType::SSE);
        $that = $this;
        $response->setContentCallback(function () use ($callback, $that) {
            $that->initializeStreaming();
            $call = new Callback($callback);
            $call->execute(function ($id, $data) use ($that) {
                // @codeCoverageIgnoreStart
                if (connection_aborted()) {
                    exit;
                }
                // @codeCoverageIgnoreEnd
                $that->outputSseMessage($id, $data);
                @flush();
            });
        });
        return $response;
    }

    private function initializeStreaming()
    {
        // Make session readonly to prevent hang from other pages
        // session_start(); is optional since its always starting from kernel
        session_write_close();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header("Connection: keep-alive");
        set_time_limit(0);
        ignore_user_abort(true);
        ini_set('max_execution_time', '0');
        // @codeCoverageIgnoreStart
        // ob_get_level should always be at 1 when using SSE, this if is for
        // test coverage to allow PHPUnit to output buffer results.
        if (ob_get_level() < 2) {
            ob_end_clean();
        }
        // @codeCoverageIgnoreEnd
        gc_enable();
    }

    private function outputSseMessage($eventId, $data, $retry = null)
    {
        $encodeOptions = JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS;
        echo "id: $eventId" . PHP_EOL;
        if (!is_null($retry)) {
            echo "retry: " . $retry . PHP_EOL;
        }
        echo "data: " . json_encode($data, $encodeOptions) . PHP_EOL;
        echo PHP_EOL;
    }
}
