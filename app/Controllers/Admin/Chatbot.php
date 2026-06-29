<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\ChatbotApiException;
use App\Services\ChatbotService;

class Chatbot extends BaseController
{
    private ChatbotService $chatbotService;

    public function __construct()
    {
        $this->chatbotService = new ChatbotService();
    }

    public function process()
    {
        $userRole = session()->get('role');
        $userId   = session()->get('user_id');

        if (!$userRole || !$userId) {
            return $this->_sendSSE('Authentication required. Please log in again.');
        }

        if (!$this->_verifySameOrigin()) {
            return $this->_sendSSE('Request blocked: untrusted origin.');
        }

        $input = $this->request->getJSON(true);
        if (!$input || !is_array($input)) {
            return $this->_sendSSE('Invalid request format.');
        }

        $history = $input['history'] ?? [];
        if (!is_array($history)) {
            $history = [];
        }
        $newUserMessage = $input['message'] ?? '';
        $maxLen = $this->chatbotService->getMaxMessageLength();

        if (is_string($newUserMessage) && mb_strlen($newUserMessage) > $maxLen) {
            return $this->_sendSSE('Message too long. Please keep messages under ' . $maxLen . ' characters.');
        }
        if ($newUserMessage === '' && !$this->chatbotService->validateHistoryLength($history)) {
            return $this->_sendSSE('Message too long. Please keep messages under ' . $maxLen . ' characters.');
        }

        $modelKey   = $input['modelName'] ?? 'gemini';
        $resolution = $this->chatbotService->resolveModel($modelKey);
        $modelKey   = $resolution['modelKey'];

        $maxHistory = $this->chatbotService->getMaxHistoryMessages();
        $history = array_slice($history, -$maxHistory);

        if ($userRole === 'customer') {
            $user = $this->chatbotService->loadUserRow($userId);
            $error = $this->chatbotService->handleCustomerRateLimit($userId, $modelKey, $user);
            if ($error !== null) {
                return $this->_sendSSE($error);
            }
        }

        $systemPrompt = $this->chatbotService->buildSystemPrompt($userRole);
        $cleanHistory = $this->chatbotService->cleanHistory($history);

        if (is_string($newUserMessage) && trim($newUserMessage) !== '') {
            $cleanHistory[] = ['role' => 'user', 'content' => trim($newUserMessage)];
        }

        $useGemini     = $resolution['useGemini'];
        $resolvedModel = $resolution['resolvedModel'];

        if (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        try {
            if ($useGemini) {
                try {
                    $this->_streamGemini($resolvedModel, $systemPrompt, $cleanHistory);
                } catch (ChatbotApiException $e) {
                    log_message('warning', '[Chatbot] Gemini failed, attempting fallback: ' . $e->getMessage());

                    echo "data: " . json_encode([
                        'metadata' => true,
                        'fallback_triggered' => true,
                        'disable_gemini'     => true,
                    ]) . "\n\n";
                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    $fallbackModel = ChatbotService::MODEL_MAP[ChatbotService::FALLBACK_MODEL_KEY];
                    $this->_streamOpenRouter($fallbackModel, $systemPrompt, $cleanHistory);
                }
            } else {
                $this->_streamOpenRouter($resolvedModel, $systemPrompt, $cleanHistory);
            }

            $this->_sendChatbotPush($newUserMessage);

            echo "data: [DONE]\n\n";
            return;

        } catch (\Exception $e) {
            log_message('error', '[Chatbot] Process error: ' . $e->getMessage());
            echo "data: " . json_encode(['text' => "An unexpected error occurred. Please try again."]) . "\n\n";
            echo "data: [DONE]\n\n";
            return;
        }
    }

    private function _sendChatbotPush(string $userMessage): void
    {
        if (session()->get('role') !== 'customer') {
            return;
        }

        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return;
        }

        $preview = mb_strlen($userMessage) > 60 ? mb_substr($userMessage, 0, 60) . '...' : $userMessage;
        $title   = 'AI Assistant Response';
        $body    = $preview !== '' ? 'Reply to: ' . $preview : 'Your AI assistant has a new response.';

        try {
            $fcm = new \App\Libraries\FirebaseCloudMessenger();
            $fcm->sendToUserAndPersist($userId, 'chatbot_response', $title, $body, [
                'type' => 'chatbot_response',
            ]);
        } catch (\Exception $e) {
            log_message('error', '[Chatbot] Push notification failed: ' . $e->getMessage());
        }
    }

    public function quota()
    {
        $userRole = session()->get('role');
        $userId   = session()->get('user_id');
        $limit    = ChatbotService::PER_MODEL_LIMIT;

        if ($userRole !== 'customer' || !$userId) {
            return $this->response->setJSON(
                $this->chatbotService->buildQuotaResponse(
                    array_fill_keys(['gemini', 'gemma', 'gpt'], 0), $limit
                )
            );
        }

        $user = $this->chatbotService->loadUserRow($userId);
        if (!$user) {
            return $this->response->setJSON(
                $this->chatbotService->buildQuotaResponse(
                    array_fill_keys(['gemini', 'gemma', 'gpt'], 0), $limit
                )
            );
        }

        $counts = $this->chatbotService->getPerModelCounts($user);
        return $this->response->setJSON($this->chatbotService->buildQuotaResponse($counts, $limit));
    }

    public function deleteHistory()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized access.'
            ])->setStatusCode(403);
        }

        try {
            $logModel = new \App\Models\ActivityLogModel();
            $logModel->save([
                'user_id'       => session()->get('user_id'),
                'user_identity' => session()->get('username') ?? 'Admin',
                'role'          => 'admin',
                'event'         => '[Admin] cleared chat history',
                'ip_address'    => $this->request->getIPAddress(),
                'device'        => $this->request->getUserAgent()->getBrowser(),
                'status_code'   => 200
            ]);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'You may now clear the chat from your browser.',
                'note'    => 'Chat history is stored locally; clear it via the UI to wipe this device.',
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Chatbot] deleteHistory error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to clear history. Please try again.'
            ])->setStatusCode(500);
        }
    }

    protected function _streamGemini(string $model, string $systemPrompt, array $history): void
    {
        $geminiKey = env('GEMINI_API_KEY');
        if (empty($geminiKey)) {
            throw new ChatbotApiException('GEMINI_API_KEY is not configured.');
        }

        $contents = [];
        foreach ($history as $msg) {
            $gRole = $msg['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = ['role' => $gRole, 'parts' => [['text' => $msg['content']]]];
        }

        $payload = json_encode([
            'contents' => $contents,
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
        ]);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:streamGenerateContent?alt=sse";

        $lineBuffer = "";
        $httpCode = 0;
        $writeCallback = function($ch, $data) use (&$lineBuffer, &$httpCode) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $lineBuffer .= $data;
            $lines = explode("\n", $lineBuffer);
            $lineBuffer = array_pop($lines);

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;

                if (strpos($line, 'data: ') === 0) {
                    $content = trim(substr($line, 6));

                    if ($content === '[DONE]') {
                        echo "data: [DONE]\n\n";
                    } else {
                        $decoded = json_decode($content, true);
                        if (is_array($decoded)) {
                            if (isset($decoded['error'])) {
                                $code    = $decoded['error']['code'] ?? 0;
                                $message = $decoded['error']['message'] ?? 'Unknown Gemini error';
                                throw new ChatbotApiException("Gemini API error {$code}: {$message}", (int)$code);
                            }
                            if (!empty($decoded['candidates'][0]['content']['parts'])) {
                                foreach ($decoded['candidates'][0]['content']['parts'] as $part) {
                                    if (!empty($part['text'])) {
                                        echo "data: " . json_encode(['text' => $part['text']]) . "\n\n";
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (ob_get_level() > 0) ob_flush();
            flush();
            return strlen($data);
        };

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "X-Goog-Api-Key: {$geminiKey}"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, $writeCallback);

        $result    = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr   = curl_error($ch);
        curl_close($ch);

        if (trim($lineBuffer) !== '') {
            $lines = explode("\n", $lineBuffer . "\n");
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;
                if (strpos($line, 'data: ') === 0) {
                    $content = trim(substr($line, 6));
                    if ($content !== '[DONE]') {
                        $decoded = json_decode($content, true);
                        if (is_array($decoded) && !empty($decoded['candidates'][0]['content']['parts'])) {
                            foreach ($decoded['candidates'][0]['content']['parts'] as $part) {
                                if (!empty($part['text'])) {
                                    echo "data: " . json_encode(['text' => $part['text']]) . "\n\n";
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($result === false) {
            throw new ChatbotApiException('Gemini connection failed: ' . ($curlErr ?: 'unknown'));
        }
        if ($httpCode === 429 || $httpCode === 503) {
            throw new ChatbotApiException("Gemini HTTP {$httpCode}: quota exhausted or service overloaded.", $httpCode);
        }
        if ($httpCode >= 400) {
            throw new ChatbotApiException("Gemini HTTP {$httpCode}: client or server error.", $httpCode);
        }
    }

    protected function _streamOpenRouter(string $model, string $systemPrompt, array $history): void
    {
        $apiKey = env('OPENROUTER_API_KEY');
        if (empty($apiKey)) {
            throw new ChatbotApiException('OPENROUTER_API_KEY is not configured.');
        }

        $messages = [["role" => "system", "content" => $systemPrompt]];
        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        $payload = json_encode([
            'model'    => $model,
            'messages' => $messages,
            'stream'   => true,
            'temperature' => 0.7,
            'max_tokens' => 2048,
        ]);

        $headers = [
            "Authorization: Bearer " . $apiKey,
            "HTTP-Referer: " . base_url(),
            "X-Title: MJ Chatbot System",
            "Content-Type: application/json",
        ];

        $lineBuffer = "";
        $httpCode = 0;
        $gotStreamData = false;
        $writeCallback = function($ch, $data) use (&$lineBuffer, &$httpCode, &$gotStreamData) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $lineBuffer .= $data;
            $lines = explode("\n", $lineBuffer);
            $lineBuffer = array_pop($lines);

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;

                if (strpos($line, 'data: ') === 0) {
                    $content = trim(substr($line, 6));

                    if ($content === '[DONE]') {
                        echo "data: [DONE]\n\n";
                    } else {
                        $decoded = json_decode($content, true);
                        if (is_array($decoded)) {
                            if (isset($decoded['error'])) {
                                $errMsg = $decoded['error']['message'] ?? 'Unknown OpenRouter error';
                                log_message('error', '[Chatbot] OpenRouter stream error: ' . $errMsg);
                                echo "data: " . json_encode(['text' => "AI service error: {$errMsg}"]) . "\n\n";
                            } elseif (!empty($decoded['choices'])) {
                                $delta = $decoded['choices'][0]['delta'] ?? [];
                                $text  = $delta['content'] ?? '';
                                if ($text !== '') {
                                    $gotStreamData = true;
                                    echo "data: " . json_encode(['text' => $text]) . "\n\n";
                                }
                            }
                        }
                    }
                } else if (strpos($line, '{') === 0) {
                    $decoded = json_decode($line, true);
                    if (isset($decoded['error'])) {
                        $errMsg = $decoded['error']['message'] ?? 'Unknown OpenRouter error';
                        log_message('error', '[Chatbot] OpenRouter API error: ' . $errMsg);
                        echo "data: " . json_encode(['text' => "AI service is temporarily unavailable. Please try again."]) . "\n\n";
                    }
                }
            }

            if (ob_get_level() > 0) ob_flush();
            flush();
            return strlen($data);
        };

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, $writeCallback);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if (trim($lineBuffer) !== '') {
            $lines = explode("\n", $lineBuffer . "\n");
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;
                if (strpos($line, 'data: ') === 0) {
                    $content = trim(substr($line, 6));
                    if ($content !== '[DONE]') {
                        $decoded = json_decode($content, true);
                        if (is_array($decoded) && !empty($decoded['choices'])) {
                            $delta = $decoded['choices'][0]['delta'] ?? [];
                            $text  = $delta['content'] ?? '';
                            if ($text !== '') {
                                echo "data: " . json_encode(['text' => $text]) . "\n\n";
                            }
                        }
                    }
                }
            }
        }

        if ($result === false) {
            log_message('error', '[Chatbot] OpenRouter curl error: ' . ($curlErr ?: 'unknown'));
            echo "data: " . json_encode(['text' => "Connection error. Please try again."]) . "\n\n";
            return;
        }

        if (!$gotStreamData) {
            if ($httpCode === 429) {
                echo "data: " . json_encode(['text' => "Too many requests. The free servers are busy — please try again in a minute."]) . "\n\n";
                return;
            }
            if ($httpCode >= 400) {
                log_message('error', "[Chatbot] OpenRouter HTTP {$httpCode}");
                echo "data: " . json_encode(['text' => "AI service error (HTTP {$httpCode}). Please try again."]) . "\n\n";
                return;
            }
        }
    }

    private function _sendSSE(string $message)
    {
        if (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        if (ob_get_level() > 0) ob_end_clean();

        echo "data: " . json_encode(['text' => $message]) . "\n\n";
        echo "data: [DONE]\n\n";
        return;
    }

    private function _verifySameOrigin(): bool
    {
        $xrw = $this->request->getHeaderLine('X-Requested-With');
        if (strtolower($xrw) === 'xmlhttprequest') {
            return true;
        }

        $origin = $this->request->getHeaderLine('Origin')
                 ?: $this->request->getHeaderLine('Referer');
        if ($origin === '') {
            return false;
        }
        $baseHost   = parse_url(base_url(), PHP_URL_HOST);
        $originHost = parse_url($origin, PHP_URL_HOST);
        return $baseHost !== false && $originHost !== false
            && strcasecmp($baseHost, $originHost) === 0;
    }
}
