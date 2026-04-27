<?php

namespace App\Services;

interface AIServiceInterface
{
    public function chat(string $message): array;

    public function analyzeProject(string $idea): array;

    public function generateBudget(string $idea): array;

    public function suggestTools(string $idea): array;

    public function analyzeProjectIdea(string $idea): array;
}
