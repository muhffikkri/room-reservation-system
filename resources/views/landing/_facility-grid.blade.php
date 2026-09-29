{{--
    Satu-satunya pemilik isi grid fasilitas landing: daftar kartu atau pesan
    kosong. Dipakai oleh render server dan oleh HomeController::ajaxFacilities,
    sehingga hasil pencarian langsung dan render awal memakai bentuk yang sama
    dan pesan kosong tidak pernah berada di luar container grid.
--}}
@forelse ($facilities as $facility)
    @include('landing._facility-grid-card', ['facility' => $facility])
@empty
    <div class="landing-panel col-span-full rounded-2xl p-10 text-center shadow-sm">
        <p class="text-base font-medium text-[#0F172A]">Fasilitas tidak ditemukan</p>
        <p class="mt-1 text-sm text-[#475569]">Coba ubah kata kunci atau filter pencarian Anda.</p>
        <a href="{{ route('home') }}" class="landing-button mt-4 inline-block rounded-full border border-blue-100 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-blue-50">Reset Filter</a>
    </div>
@endforelse
