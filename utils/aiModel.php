<?php
require_once "../assets/php/config.php";

if (!class_exists("LucianoTonet\GroqPHP\Groq", true)) {
    // groq now found 
    echo "ERROR: groq class not exists";

    return;
}


class GenAI
{
    private ?string $_api_key = null;
    private ?object $_ai_client = null;
    public ?string $system_prompt = null;
    public array $config = [
        'model' => null,
        'temperature' => null,
        'top_p' => null,
        'stream' => false, // Default to false
    ];

    public function __construct()
    {
        // You might want to initialize the client here if the API key is readily available
        $this->setup_api(GROQ_API_KEY);
    }

    public function setup_api(string $api_key): void
    {
        $this->_api_key = $api_key;
        // Instantiate the Groq client. You'll need to adapt this based on the actual PHP library.
        // Assuming a hypothetical Groq PHP client:
        $this->_ai_client = new LucianoTonet\GroqPHP\Groq(
            $api_key,
            [
                'baseUrl' => 'https://api.groq.com/openai/v1'
            ]
        );
    }

    public function is_api_key_avail(bool $send_error = false): bool
    {
        if ($send_error && $this->_api_key === null) {
            throw new RuntimeException('Please provide API Key.');
        }
        return $this->_api_key !== null;
    }

    public function list_models(): array
    {
        $this->is_api_key_avail(true);

        // Adapt this based on the Groq PHP client's method for listing models
        try {
            $models = $this->_ai_client->models()->list();
            if (isset($models['data']) && is_array($models['data'])) {
                return $models['data'];
            }
        } catch (\Exception $e) {
            // Handle potential API errors
            error_log("Error listing models: " . $e->getMessage());
        }

        return [];
    }

    public function feed_prompt(string $prompt): ?string
    {
        if (!$this->is_api_key_avail() || $this->config['model'] === null) {
            return null;
        }

        $messages = [
            [
                "role" => "system",
                "content" => $this->get_system_prompt(),
            ],
            [
                "role" => "user",
                "content" => $prompt,
            ],
        ];

        $request_config = $this->config;
        unset($request_config['stream']);

        try {
            // Adapt this based on the Groq PHP client's method for creating chat completions
            $chat_completion = $this->_ai_client->chat()->completions()->create(
                array_merge(['messages' => $messages], $request_config)
            );

            if (!$this->config['stream']) {
                if (isset($chat_completion['choices'][0]['message']['content'])) {
                    return $chat_completion['choices'][0]['message']['content'];
                }
                return null;
            } else {
                return $this->stream_data($chat_completion);
            }
        } catch (\Exception $e) {
            // Handle potential API errors
            error_log("Error creating chat completion: " . $e->getMessage());
            return null;
        }
    }

    public function stream_data($chat_completion)
    {
        // This function needs to handle the streaming response from the Groq API.
        // The implementation will heavily depend on how the PHP Groq client handles streaming.
        // You might need to use server-sent events (SSE) or a similar mechanism.

        // This is a placeholder - you'll need to implement the actual streaming logic.
        return function () use ($chat_completion) {
            $full_content = '';
            // Assuming $chat_completion is iterable and yields chunks
            foreach ($chat_completion as $chunk) {
                // Adapt this based on the structure of the chunk in the PHP client
                if (isset($chunk->choices[0]->delta->content) && $content = $chunk->choices[0]->delta->content) {
                    $full_content .= $content;
                    yield $content;
                    usleep(20000); // Equivalent of sleep(0.02) in microseconds
                }
            }
            return $full_content;
        };
    }

    public function set_system_prompt(string $prompt): void
    {
        $this->system_prompt = $prompt;
    }

    public function get_system_prompt(): string
    {
        return $this->system_prompt ?? '';
    }

    public function stream_response(bool $stream = false): void
    {
        $this->config['stream'] = $stream;
    }

    public function is_stream(): bool
    {
        return $this->config['stream'] ?? false;
    }

    public function get_model(): string
    {
        if ($this->config['model'] === null) {
            throw new RuntimeException("No model selected");
        }
        return $this->config['model'];
    }

    public function set_model(string $model_name): void
    {
        if (!$model_name) {
            throw new RuntimeException("Please provide Model Name");
        }
        $this->config['model'] = $model_name;
    }

    public function get_temperature(): float
    {
        if ($this->config['temperature'] === null) {
            throw new RuntimeException("Please select temperature");
        }
        return (float) $this->config['temperature'];
    }

    public function set_temperature(float $temperature): void
    {
        $this->config['temperature'] = $temperature;
    }

    public function get_top_p(): float
    {
        if ($this->config['top_p'] === null) {
            throw new RuntimeException("Please select Top p value");
        }
        return (float) $this->config['top_p'];
    }

    public function set_top_p(float $top_p): void
    {
        $this->config['top_p'] = $top_p;
    }
}

?>