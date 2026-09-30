<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function readIcoSizes(string $path): array
{
    $data = file_get_contents($path);
    $reserved = unpack('v', substr($data, 0, 2))[1];
    $type = unpack('v', substr($data, 2, 2))[1];
    $count = unpack('v', substr($data, 4, 2))[1];

    expect($reserved)->toBe(0)->and($type)->toBe(1);

    $sizes = [];
    for ($i = 0; $i < $count; $i++) {
        $entry = substr($data, 6 + $i * 16, 16);
        $width = ord($entry[0]) ?: 256;
        $height = ord($entry[1]) ?: 256;
        $sizes[] = [$width, $height];
    }

    return $sizes;
}

it('menyediakan favicon di root untuk fallback browser', function () {
    $path = public_path('favicon.ico');

    expect(file_exists($path))->toBeTrue()
        ->and(filesize($path))->toBeGreaterThan(0);
});

it('menyediakan ukuran 16, 32, dan 48 pada favicon', function () {
    expect(readIcoSizes(public_path('favicon.ico')))
        ->toBe([[16, 16], [32, 32], [48, 48]]);
});

it('menautkan favicon dan apple touch icon di setiap halaman', function (string $uri) {
    $this->get($uri)
        ->assertOk()
        ->assertSee('<link rel="icon" href="'.asset('favicon.ico').'" sizes="16x16 32x32 48x48">', false)
        ->assertSee('<link rel="apple-touch-icon" href="'.asset('images/apple-touch-icon.png').'">', false);
})->with([
    'landing' => fn () => '/',
    'login' => fn () => '/login',
    'fasilitas publik' => fn () => '/fasilitas',
]);

it('menautkan favicon pada halaman beranda admin', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('<link rel="icon" href="'.asset('favicon.ico').'" sizes="16x16 32x32 48x48">', false);
});

it('tidak menyalin markup favicon di luar komponen tunggal', function () {
    $owners = [];
    foreach (File::allFiles(resource_path('views')) as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }
        if ($file->getFilename() === 'favicon.blade.php') {
            continue;
        }
        if (str_contains($file->getContents(), 'rel="icon"')) {
            $owners[] = $file->getRelativePathname();
        }
    }

    expect($owners)->toBe([]);
});
