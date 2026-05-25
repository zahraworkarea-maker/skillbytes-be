<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MinimumWordCount implements Rule
{
    private int $minimumWords;
    private int $actualWords;

    public function __construct(int $minimumWords = 300)
    {
        $this->minimumWords = $minimumWords;
        $this->actualWords = 0;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        // Count words by splitting on whitespace and filtering empty values
        $this->actualWords = count(array_filter(preg_split('/\s+/', trim($value))));
        return $this->actualWords >= $this->minimumWords;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return "Resume harus mengandung minimal {$this->minimumWords} kata (saat ini: {$this->actualWords} kata).";
    }
}
