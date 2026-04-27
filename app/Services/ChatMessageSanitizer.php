<?php

namespace App\Services;

class ChatMessageSanitizer
{
    public function sanitize(string $message): string
    {
        $clean = strip_tags($message);
        $clean = str_replace(["\r\n", "\r"], "\n", $clean);
        $clean = preg_replace("/[ \t]+\n/", "\n", $clean) ?? $clean;
        $clean = preg_replace("/\n{3,}/", "\n\n", $clean) ?? $clean;

        return trim($clean);
    }
}

