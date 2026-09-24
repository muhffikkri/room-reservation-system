<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacilityRequest;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * CRUD master fasilitas oleh admin (§7.3).
 *
 * "Hapus" fasilitas tidak pernah terjadi fisik; sistem hanya mengubah
 * status ke nonaktif (soft-disable) sehingga riwayat reservasi/laporan
 * tetap konsisten. Upload foto disimpan di disk public/facilities.
 */
class FacilityController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->string('q')->trim()->toString();

        $facilities = Facility::query()
            ->withCount(['reservations', 'reports'])
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', "%{$keyword}%"))
            ->orderBy('name')
            ->get();

        return view('admin.fasilitas.index', [
            'facilities' => $facilities,
            'keyword' => $keyword,
        ]);
    }

    public function create(): View
    {
        return view('admin.fasilitas.create');
    }

    public function store(FacilityRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $facility = Facility::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'location' => $validated['location'],
            'capacity' => $validated['capacity'],
            'description' => $validated['description'] ?? null,
            'photo' => $this->storePhoto($request),
            'status' => 'aktif',
        ]);

        return redirect()
            ->route('admin.fasilitas.index')
            ->with('success', "Fasilitas {$facility->name} berhasil dibuat.");
    }

    public function edit(Facility $facility): View
    {
        return view('admin.fasilitas.edit', ['facility' => $facility]);
    }

    public function update(FacilityRequest $request, Facility $facility): RedirectResponse
    {
        $validated = $request->validated();

        // Foto baru menggantikan yang lama agar file tak terpakai tidak menumpuk.
        if ($request->hasFile('photo')) {
            $this->deletePhoto($facility);
        }

        $facility->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'location' => $validated['location'],
            'capacity' => $validated['capacity'],
            'description' => $validated['description'] ?? null,
            'photo' => $this->storePhoto($request) ?? $facility->photo,
        ]);

        return redirect()
            ->route('admin.fasilitas.index')
            ->with('success', "Fasilitas {$facility->name} berhasil diperbarui.");
    }

    /**
     * Nonaktifkan fasilitas (soft-disable alih-alih hapus fisik).
     *
     * Status perbaikan milik alur petugas (§4.2): admin tidak boleh
     * menonaktifkan fasilitas yang sedang ditangani sampai laporannya
     * selesai dan statusnya kembali aktif.
     */
    public function deactivate(Facility $facility): RedirectResponse
    {
        $result = DB::transaction(function () use ($facility): array {
            $locked = Facility::whereKey($facility->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'perbaikan') {
                return ['error', "Fasilitas {$locked->name} sedang dalam perbaikan dan tidak dapat dinonaktifkan sampai penanganannya selesai."];
            }

            $locked->update(['status' => 'nonaktif']);

            return ['success', "Fasilitas {$locked->name} dinonaktifkan."];
        });

        return back()->with($result[0], $result[1]);
    }

    /**
     * Aktifkan kembali fasilitas yang sebelumnya nonaktif.
     *
     * Mengaktifkan dari perbaikan adalah wewenang petugas via alur
     * laporan (BR-11); admin hanya mengaktifkan dari nonaktif.
     */
    public function activate(Facility $facility): RedirectResponse
    {
        $result = DB::transaction(function () use ($facility): array {
            $locked = Facility::whereKey($facility->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'perbaikan') {
                return ['error', "Fasilitas {$locked->name} sedang dalam perbaikan; pengembaliannya ke aktif dilakukan petugas melalui alur laporan."];
            }

            $locked->update(['status' => 'aktif']);

            return ['success', "Fasilitas {$locked->name} diaktifkan kembali."];
        });

        return back()->with($result[0], $result[1]);
    }

    /**
     * Simpan foto fasilitas ke storage/public/facilities.
     */
    private function storePhoto(FacilityRequest $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        $path = $request->file('photo')->storePublicly('facilities', 'public');

        if ($path === false) {
            throw new \RuntimeException('Foto fasilitas gagal disimpan.');
        }

        return $path;
    }

    private function deletePhoto(Facility $facility): void
    {
        if ($facility->photo !== null) {
            Storage::disk('public')->delete($facility->photo);
        }
    }
}
