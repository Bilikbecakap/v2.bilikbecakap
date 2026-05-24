<?php

namespace App\Contracts;

interface AIServiceInterface
{
    public function testConnection(): array;

    public function translateDirectlyWithAI(string $text, string $direction): array;

    public function translateToEnglish(string $text): array;

    public function translateToIndonesian(string $text): array;

    public function chatWithContext(string $userMessage, string $contextType = 'general'): array;

    public function chatWithHistory(string $userMessage, array $conversationHistory = [], string $contextType = 'general'): array;

    public function extractTextFromImage(string $imagePath, int $maxRetries = 3): array;

    public function getDatabaseStats(): array;

    public function loadKamusData(int $limit = 100): array;

    public function testKamusData(): array;

    public function testTranslateDetails(string $text, string $direction = 'belitung_to_indonesia'): array;
}
