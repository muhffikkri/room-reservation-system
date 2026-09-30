<?php

namespace App\Models;

use Database\Factories\FacilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'type', 'location', 'capacity', 'description', 'photo', 'status', 'repair_report_id'])]
class Facility extends Model
{
    /**
     * Batas jumlah opsi lokasi yang dirender pada filter publik.
     *
     * Satu-satunya pemilik nilai ini: landing page dan katalog publik memakai
     * angka yang sama sehingga respons publik tetap terbatas payload-nya
     * meski tabel facilities bertambah besar.
     */
    public const MAX_PUBLIC_FILTER_OPTIONS = 50;

    /**
     * Batas panjang deskripsi fasilitas yang diizinkan dan dirender.
     *
     * Satu-satunya pemilik nilai ini: validasi admin, kartu grid landing, kartu
     * katalog, halaman detail, dan renderer pencarian langsung (AJAX) semuanya
     * membaca dari konstanta yang sama sehingga tidak ada jalur yang meloloskan
     * deskripsi panjang dan membuat card tidak rapi.
     */
    public const MAX_DESCRIPTION_WORDS = 30;

    /**
     * Label resmi untuk setiap tipe fasilitas.
     *
     * Satu-satunya pemilik nilai ini: landing page, katalog, halaman detail,
     * form admin, dan renderer pencarian langsung (AJAX) memakainya, sehingga
     * tidak ada daftar tipe yang bisa berbeda antar halaman.
     *
     * @var array<string, string>
     */
    public const TYPE_LABELS = [
        'ruang_kelas' => 'Ruang Kelas',
        'aula' => 'Aula',
        'laboratorium' => 'Laboratorium',
        'alat' => 'Alat',
        'lapangan' => 'Lapangan',
    ];

    /**
     * Nama berkas gambar cadangan per tipe, dipakai saat fasilitas belum punya
     * foto unggahan.
     *
     * Satu-satunya pemilik nilai ini: seluruh card, halaman detail, dan halaman
     * jadwal memakai accessor display_image_url, bukan menyalin daftar sendiri.
     *
     * @var array<string, string>
     */
    public const TYPE_FALLBACK_IMAGES = [
        'ruang_kelas' => 'ruang-kelas.webp',
        'aula' => 'aula.webp',
        'laboratorium' => 'lab-komputer.webp',
        'alat' => 'proyektor.webp',
        'lapangan' => 'lapangan-futsal.webp',
    ];

    /**
     * Accessor legacy hanya ikut ter-serialize bila didaftarkan di sini.
     *
     * @var list<string>
     */
    protected $appends = ['photo_url'];

    /** @use HasFactory<FacilityFactory> */
    use HasFactory;

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function repairReport(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'repair_report_id');
    }

    /**
     * Menyaring fasilitas aktif. Hanya fasilitas aktif yang dapat user
     * reservasi (BR-5, BR-12).
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Filter halaman daftar fasilitas: keyword nama, tipe, lokasi, dan
     * kapasitas minimum (§6 fasilitas.index).
     */
    public function scopeSearch(Builder $query, ?string $keyword, ?string $type, ?string $location, ?int $minCapacity): Builder
    {
        $keyword = strip_tags($keyword ?? '');
        $location = strip_tags($location ?? '');

        return $query
            ->when($keyword, fn (Builder $q): Builder => $q->where('name', 'like', '%'.$keyword.'%'))
            ->when($type, fn (Builder $q): Builder => $q->where('type', $type))
            ->when($location, fn (Builder $q): Builder => $q->where('location', 'like', '%'.$location.'%'))
            ->when($minCapacity, fn (Builder $q): Builder => $q->where('capacity', '>=', $minCapacity));
    }

    /**
     * Opsi lokasi unik untuk dropdown filter publik, dipotong pada
     * MAX_PUBLIC_FILTER_OPTIONS supaya halaman publik tidak ikut membesar
     * mengikuti jumlah baris facilities.
     */
    public function scopePublicLocationOptions(Builder $query): Builder
    {
        return $query
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->limit(self::MAX_PUBLIC_FILTER_OPTIONS);
    }

    /**
     * URL absolut foto fasilitas di disk public, atau string kosong bila belum
     * ada foto.
     *
     * Satu-satunya pemilik resolusi URL foto: blade dan renderer pencarian
     * langsung (AJAX) memakai nilai yang sama, sehingga kartu hasil pencarian
     * tidak pernah memakai path relatif yang membuat gambar rusak.
     */
    public function getPhotoUrlAttribute(): string
    {
        return $this->photo === null || $this->photo === ''
            ? ''
            : Storage::disk('public')->url($this->photo);
    }

    /**
     * Deskripsi facilities yang dipangkas ke batas kata yang sah.
     *
     * Validasi admin sudah menolak deskripsi di atas batas, jadi pemotongan ini
     * adalah jaring pengaman untuk baris lama yang dibuat sebelum aturan itu
     * ada. Satu-satunya pemilik pemotongan: seluruh card dan halaman detail
     * memakai nilai ini, bukan memanggil Str::words dengan angka sendiri.
     */
    public function getShortDescriptionAttribute(): string
    {
        return Str::words((string) $this->description, self::MAX_DESCRIPTION_WORDS);
    }

    /**
     * URL gambar yang selalu bisa dipakai di atribut src: foto unggahan bila
     * ada, selain itu gambar cadangan sesuai tipe fasilitas.
     *
     * Satu-satunya pemilik pemilihan gambar: card grid landing, card katalog,
     * halaman detail, dan halaman jadwal memakai nilai ini. Accessor photo_url
     * tetap kosong bila tidak ada foto, karena ia menggambarkan kolom disk.
     */
    public function getDisplayImageUrlAttribute(): string
    {
        if ($this->photo_url !== '') {
            return $this->photo_url;
        }

        return asset('images/'.(self::TYPE_FALLBACK_IMAGES[$this->type] ?? self::TYPE_FALLBACK_IMAGES['aula']));
    }
}
