<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationCache extends Model
{
    protected $table = 'translation_cache';

    protected $fillable = [
        'source_text',
        'source_lang',
        'target_lang',
        'translated_text',
    ];
}
