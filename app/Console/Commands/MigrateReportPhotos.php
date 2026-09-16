<?php

namespace App\Console\Commands;

use App\Models\Report;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

#[Signature('reports:protect-photos {--delete-public : Remove the legacy public copy after a successful migration}')]
#[Description('Copy report photos to private storage and optionally remove public copies')]
final class MigrateReportPhotos extends Command
{
    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $deletePublic = (bool) $this->option('delete-public');
        $migrated = 0;
        $skipped = 0;
        $failures = 0;

        Report::query()
            ->whereNotNull('photo')
            ->orderBy('id')
            ->chunkById(100, function (Collection $reports) use (
                $public,
                $private,
                $deletePublic,
                &$migrated,
                &$skipped,
                &$failures,
            ): void {
                foreach ($reports as $report) {
                    $path = is_string($report->photo) ? trim($report->photo) : '';

                    if ($path === '' || ! str_starts_with($path, 'reports/') || str_contains($path, '..')) {
                        $this->warn("Laporan #{$report->id} dilewati: path foto tidak valid.");
                        $skipped++;

                        continue;
                    }

                    try {
                        $hasPrivateCopy = $private->exists($path);

                        if (! $hasPrivateCopy) {
                            if (! $public->exists($path)) {
                                throw new RuntimeException('salinan public dan private tidak ditemukan');
                            }

                            $stream = $public->readStream($path);

                            if (! is_resource($stream)) {
                                throw new RuntimeException('file public tidak dapat dibaca');
                            }

                            try {
                                if (! $private->put($path, $stream)) {
                                    throw new RuntimeException('penyalinan ke storage private gagal');
                                }
                            } finally {
                                fclose($stream);
                            }
                        }

                        if ($deletePublic && $public->exists($path) && ! $public->delete($path)) {
                            throw new RuntimeException('penghapusan salinan public gagal');
                        }

                        $migrated++;
                        $this->line("Laporan #{$report->id}: {$path}");
                    } catch (Throwable $exception) {
                        $this->error("Laporan #{$report->id} gagal: {$exception->getMessage()}");
                        $failures++;
                    }
                }
            });

        $this->info("Selesai: {$migrated} diproteksi, {$skipped} dilewati, {$failures} gagal.");

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
