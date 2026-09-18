<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ReportPhotoController extends Controller
{
    public function __invoke(Report $report): Response
    {
        Gate::authorize('view', $report);

        $path = $report->photo;

        if ($path === null
            || ! str_starts_with($path, 'reports/')
            || str_contains($path, '..')
            || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }
}
