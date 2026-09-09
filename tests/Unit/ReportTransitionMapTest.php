<?php

use App\Services\ReportService;

it('allows baru to move only to diproses', function () {
    expect(ReportService::allowedTransitions('baru'))->toBe(['diproses']);
});

it('allows diproses to move only to selesai or ditolak', function () {
    expect(ReportService::allowedTransitions('diproses'))->toBe(['selesai', 'ditolak']);
});

it('treats selesai and ditolak as terminal states', function () {
    expect(ReportService::allowedTransitions('selesai'))->toBeEmpty();
    expect(ReportService::allowedTransitions('ditolak'))->toBeEmpty();
});

it('returns no transitions for an unknown status', function () {
    expect(ReportService::allowedTransitions('hilang'))->toBeEmpty();
});
