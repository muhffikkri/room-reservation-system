<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['report_id', 'user_id', 'old_status', 'new_status', 'note'])]
/**
 * Satu baris audit untuk satu perpindahan status laporan (§9.2, BR-10).
 *
 * Baris ini hanya boleh bertambah, tidak boleh berubah atau terhapus.
 * Kolom old_status kosong untuk baris pertama karena laporan lahir
 * langsung berstatus baru.
 */
class ReportUpdate extends Model
{
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
