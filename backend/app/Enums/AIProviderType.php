<?php

namespace App\Enums;

enum AIProviderType: string
{
    case OpenAI = 'openai';
    case Anthropic = 'anthropic';
    case Gemini = 'gemini';
}
