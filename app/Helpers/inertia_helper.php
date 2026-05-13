<?php

use App\Libraries\Inertia;

if (! function_exists('inertia')) {
    /**
     * Inertia helper for rendering components.
     *
     * @param string $component
     * @param array  $props
     *
     * @return mixed
     */
    function inertia(string $component, array $props = [])
    {
        return Inertia::render($component, $props);
    }
}

if (! function_exists('inertia_div')) {
    /**
     * Helper to render the Inertia root div.
     *
     * @param array $page
     *
     * @return string
     */
    function inertia_div($page = [])
    {
        // Ensure $page is a valid non-empty array for Inertia
        if (!is_array($page) || empty($page) || !isset($page['component'])) {
            log_message('error', '[Inertia] Missing or invalid page data in inertia_div. Falling back to Error component.');
            $page = [
                'component' => 'Error',
                'props'     => (object)[],
                'url'       => (string) current_url(),
                'version'   => null,
            ];
        }
        
        try {
            $json = json_encode($page, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            log_message('error', '[Inertia] JSON encoding failed: ' . $e->getMessage());
            $json = json_encode([
                'component' => 'Error',
                'props'     => ['error' => 'JSON Error: ' . $e->getMessage()],
                'url'       => '/',
                'version'   => null
            ]);
        }
        
        $encodedPage = htmlspecialchars($json, ENT_QUOTES, 'UTF-8');
        return "<div id=\"app\" data-page=\"{$encodedPage}\"></div>";
    }
}
