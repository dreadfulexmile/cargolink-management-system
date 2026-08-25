<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic key/value app settings — today just the job/invoice numbering
 * counters (see JobNumberGenerator, InvoiceNumberGenerator), but a natural
 * home for future config (company details on PDFs, etc.) without a new
 * table per setting.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
