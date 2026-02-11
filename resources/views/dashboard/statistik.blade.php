@extends('dashboard.layout')

@section('title', 'Statistik')

@section('header', 'Dashboard Statistik')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div id="section-kedatangan" class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-3 mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Bar Chart – Volume Kedatangan</h3>
            <div class="flex flex-wrap items-end justify-end gap-2">
                <div class="flex gap-2">
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartKedatangan" data-type="png"><i class="fas fa-download mr-2"></i>PNG</button>
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartKedatangan" data-type="jpeg"><i class="fas fa-download mr-2"></i>JPEG</button>
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartKedatangan" data-type="jpg"><i class="fas fa-download mr-2"></i>JPG</button>
                </div>

                <form method="GET" action="{{ route('statistik') }}#section-kedatangan" class="stat-form flex flex-col sm:flex-row sm:flex-wrap gap-3 items-end">
                <input type="hidden" name="tahun_muat" value="{{ request('tahun_muat', $tahunMuat ?? now()->year) }}">
                <input type="hidden" name="mitra_bulan" value="{{ request('mitra_bulan', $mitraBulan ?? now()->month) }}">
                <input type="hidden" name="mitra_tahun" value="{{ request('mitra_tahun', $mitraTahun ?? now()->year) }}">
                <input type="hidden" name="sa_bulan" value="{{ request('sa_bulan', $saBulan ?? now()->month) }}">
                <input type="hidden" name="sa_tahun" value="{{ request('sa_tahun', $saTahun ?? now()->year) }}">
                <input type="hidden" name="top_customer_jenis" value="{{ request('top_customer_jenis', $topCustomerJenis ?? 'kedatangan') }}">
                <input type="hidden" name="top_customer_mode" value="{{ request('top_customer_mode', $topCustomerMode ?? 'volume') }}">
                <input type="hidden" name="top_customer_bulan" value="{{ request('top_customer_bulan', $topCustomerBulan ?? now()->month) }}">
                <input type="hidden" name="top_customer_tahun" value="{{ request('top_customer_tahun', $topCustomerTahun ?? now()->year) }}">

                <div class="min-w-[112px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                    <input name="tahun_kedatangan" value="{{ request('tahun_kedatangan', $tahunKedatangan ?? now()->year) }}" type="number" min="2000" max="2100" class="w-full sm:w-28 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                </div>

                <button type="submit" class="h-10 px-4 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 whitespace-nowrap">
                    <i class="fas fa-filter mr-2"></i>
                    Terapkan
                </button>
                </form>
            </div>
        </div>

        <canvas id="chartKedatangan" height="120"></canvas>
    </div>

    <div id="section-muat" class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-3 mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Bar Chart – Volume Muat</h3>
            <div class="flex flex-wrap items-end justify-end gap-2">
                <div class="flex gap-2">
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartMuat" data-type="png"><i class="fas fa-download mr-2"></i>PNG</button>
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartMuat" data-type="jpeg"><i class="fas fa-download mr-2"></i>JPEG</button>
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartMuat" data-type="jpg"><i class="fas fa-download mr-2"></i>JPG</button>
                </div>

                <form method="GET" action="{{ route('statistik') }}#section-muat" class="stat-form flex flex-col sm:flex-row sm:flex-wrap gap-3 items-end">
                <input type="hidden" name="tahun_kedatangan" value="{{ request('tahun_kedatangan', $tahunKedatangan ?? now()->year) }}">
                <input type="hidden" name="mitra_bulan" value="{{ request('mitra_bulan', $mitraBulan ?? now()->month) }}">
                <input type="hidden" name="mitra_tahun" value="{{ request('mitra_tahun', $mitraTahun ?? now()->year) }}">
                <input type="hidden" name="sa_bulan" value="{{ request('sa_bulan', $saBulan ?? now()->month) }}">
                <input type="hidden" name="sa_tahun" value="{{ request('sa_tahun', $saTahun ?? now()->year) }}">
                <input type="hidden" name="top_customer_jenis" value="{{ request('top_customer_jenis', $topCustomerJenis ?? 'kedatangan') }}">
                <input type="hidden" name="top_customer_mode" value="{{ request('top_customer_mode', $topCustomerMode ?? 'volume') }}">
                <input type="hidden" name="top_customer_bulan" value="{{ request('top_customer_bulan', $topCustomerBulan ?? now()->month) }}">
                <input type="hidden" name="top_customer_tahun" value="{{ request('top_customer_tahun', $topCustomerTahun ?? now()->year) }}">

                <div class="min-w-[112px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                    <input name="tahun_muat" value="{{ request('tahun_muat', $tahunMuat ?? now()->year) }}" type="number" min="2000" max="2100" class="w-full sm:w-28 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                </div>

                <button type="submit" class="h-10 px-4 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 whitespace-nowrap">
                    <i class="fas fa-filter mr-2"></i>
                    Terapkan
                </button>
                </form>
            </div>
        </div>

        <canvas id="chartMuat" height="120"></canvas>
    </div>

    <div id="section-mitra" class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-3 mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Horizontal Bar Chart – Volume per Mitra (Bulanan)</h3>
            <div class="flex flex-wrap items-end justify-end gap-2">
                <div class="flex gap-2">
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartMitra" data-type="png"><i class="fas fa-download mr-2"></i>PNG</button>
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartMitra" data-type="jpeg"><i class="fas fa-download mr-2"></i>JPEG</button>
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartMitra" data-type="jpg"><i class="fas fa-download mr-2"></i>JPG</button>
                </div>

                <form method="GET" action="{{ route('statistik') }}#section-mitra" class="stat-form flex flex-col sm:flex-row sm:flex-wrap gap-3 items-end">
                <input type="hidden" name="tahun_kedatangan" value="{{ request('tahun_kedatangan', $tahunKedatangan ?? now()->year) }}">
                <input type="hidden" name="tahun_muat" value="{{ request('tahun_muat', $tahunMuat ?? now()->year) }}">
                <input type="hidden" name="sa_bulan" value="{{ request('sa_bulan', $saBulan ?? now()->month) }}">
                <input type="hidden" name="sa_tahun" value="{{ request('sa_tahun', $saTahun ?? now()->year) }}">
                <input type="hidden" name="top_customer_jenis" value="{{ request('top_customer_jenis', $topCustomerJenis ?? 'kedatangan') }}">
                <input type="hidden" name="top_customer_mode" value="{{ request('top_customer_mode', $topCustomerMode ?? 'volume') }}">
                <input type="hidden" name="top_customer_bulan" value="{{ request('top_customer_bulan', $topCustomerBulan ?? now()->month) }}">
                <input type="hidden" name="top_customer_tahun" value="{{ request('top_customer_tahun', $topCustomerTahun ?? now()->year) }}">

                <div class="min-w-[144px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                    <select name="mitra_bulan" class="w-full sm:w-36 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                        @foreach ([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'] as $m => $label)
                            <option value="{{ $m }}" @selected((int)request('mitra_bulan', $mitraBulan ?? now()->month) === $m)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[112px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                    <input name="mitra_tahun" value="{{ request('mitra_tahun', $mitraTahun ?? now()->year) }}" type="number" min="2000" max="2100" class="w-full sm:w-28 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                </div>

                <button type="submit" class="h-10 px-4 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 whitespace-nowrap">
                    <i class="fas fa-filter mr-2"></i>
                    Terapkan
                </button>
                </form>
            </div>
        </div>
        <canvas id="chartMitra" height="180"></canvas>
    </div>

    <div id="section-year-compare" class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-3 mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Line Chart – Perbandingan Volume Tahunan</h3>
            <div class="flex flex-wrap items-end justify-end gap-2">
                <div class="flex gap-2">
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartYearCompare" data-type="png"><i class="fas fa-download mr-2"></i>PNG</button>
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartYearCompare" data-type="jpeg"><i class="fas fa-download mr-2"></i>JPEG</button>
                    <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartYearCompare" data-type="jpg"><i class="fas fa-download mr-2"></i>JPG</button>
                </div>

                <form method="GET" action="{{ route('statistik') }}#section-year-compare" class="stat-form flex flex-col sm:flex-row sm:flex-wrap gap-3 items-end">
                <input type="hidden" name="tahun_kedatangan" value="{{ request('tahun_kedatangan', $tahunKedatangan ?? now()->year) }}">
                <input type="hidden" name="tahun_muat" value="{{ request('tahun_muat', $tahunMuat ?? now()->year) }}">
                <input type="hidden" name="mitra_bulan" value="{{ request('mitra_bulan', $mitraBulan ?? now()->month) }}">
                <input type="hidden" name="mitra_tahun" value="{{ request('mitra_tahun', $mitraTahun ?? now()->year) }}">
                <input type="hidden" name="sa_bulan" value="{{ request('sa_bulan', $saBulan ?? now()->month) }}">
                <input type="hidden" name="sa_tahun" value="{{ request('sa_tahun', $saTahun ?? now()->year) }}">
                <input type="hidden" name="top_customer_jenis" value="{{ request('top_customer_jenis', $topCustomerJenis ?? 'kedatangan') }}">
                <input type="hidden" name="top_customer_mode" value="{{ request('top_customer_mode', $topCustomerMode ?? 'volume') }}">
                <input type="hidden" name="top_customer_bulan" value="{{ request('top_customer_bulan', $topCustomerBulan ?? now()->month) }}">
                <input type="hidden" name="top_customer_tahun" value="{{ request('top_customer_tahun', $topCustomerTahun ?? now()->year) }}">

                <div class="min-w-[112px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                    <input name="year_compare_display" value="{{ request('year_compare_display', $yearCompareDisplay ?? now()->year) }}" type="number" min="2000" max="2100" class="w-full sm:w-28 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                </div>

                <button type="submit" class="h-10 px-4 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 whitespace-nowrap">
                    <i class="fas fa-filter mr-2"></i>
                    Terapkan
                </button>
                </form>
            </div>
        </div>
        <canvas id="chartYearCompare" height="140"></canvas>
    </div>

    <div id="section-sa-harian" class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-3 mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Line Chart – Jumlah SA per Hari</h3>

            <div class="flex gap-2">
                <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartSaHarian" data-type="png"><i class="fas fa-download mr-2"></i>PNG</button>
                <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartSaHarian" data-type="jpeg"><i class="fas fa-download mr-2"></i>JPEG</button>
                <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartSaHarian" data-type="jpg"><i class="fas fa-download mr-2"></i>JPG</button>
            </div>
        </div>

        <form method="GET" action="{{ route('statistik') }}#section-sa-harian" class="stat-form flex flex-col sm:flex-row sm:flex-wrap gap-3 items-end mb-4">
            <input type="hidden" name="tahun_kedatangan" value="{{ request('tahun_kedatangan', $tahunKedatangan ?? now()->year) }}">
            <input type="hidden" name="tahun_muat" value="{{ request('tahun_muat', $tahunMuat ?? now()->year) }}">
            <input type="hidden" name="mitra_bulan" value="{{ request('mitra_bulan', $mitraBulan ?? now()->month) }}">
            <input type="hidden" name="mitra_tahun" value="{{ request('mitra_tahun', $mitraTahun ?? now()->year) }}">
            <input type="hidden" name="top_customer_jenis" value="{{ request('top_customer_jenis', $topCustomerJenis ?? 'kedatangan') }}">
            <input type="hidden" name="top_customer_mode" value="{{ request('top_customer_mode', $topCustomerMode ?? 'volume') }}">
            <input type="hidden" name="top_customer_bulan" value="{{ request('top_customer_bulan', $topCustomerBulan ?? now()->month) }}">
            <input type="hidden" name="top_customer_tahun" value="{{ request('top_customer_tahun', $topCustomerTahun ?? now()->year) }}">

            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Jenis</label>
                <select name="sa_jenis" class="w-full sm:w-40 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    <option value="keduanya" @selected(request('sa_jenis', 'keduanya') === 'keduanya')>Keduanya</option>
                    <option value="kedatangan" @selected(request('sa_jenis') === 'kedatangan')>Kedatangan</option>
                    <option value="muat" @selected(request('sa_jenis') === 'muat')>Muat</option>
                </select>
            </div>

            <div class="min-w-[144px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                <select name="sa_bulan" class="w-full sm:w-36 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    @foreach ([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'] as $m => $label)
                        <option value="{{ $m }}" @selected((int)request('sa_bulan', $saBulan ?? now()->month) === $m)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[112px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                <input name="sa_tahun" value="{{ request('sa_tahun', $saTahun ?? now()->year) }}" type="number" min="2000" max="2100" class="w-full sm:w-28 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
            </div>

            <button type="submit" class="h-10 px-4 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 whitespace-nowrap">
                <i class="fas fa-filter mr-2"></i>
                Terapkan
            </button>
        </form>

        <canvas id="chartSaHarian" height="160"></canvas>
    </div>

    <div id="section-top-customer" class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-3 mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Bar Chart – Persebaran Tujuan Stasiun</h3>

            <div class="flex gap-2">
                <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartTopCustomer" data-type="png"><i class="fas fa-download mr-2"></i>PNG</button>
                <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartTopCustomer" data-type="jpeg"><i class="fas fa-download mr-2"></i>JPEG</button>
                <button type="button" class="chart-download px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" data-canvas="chartTopCustomer" data-type="jpg"><i class="fas fa-download mr-2"></i>JPG</button>
            </div>
        </div>

        <form method="GET" action="{{ route('statistik') }}#section-top-customer" class="stat-form flex flex-col sm:flex-row sm:flex-wrap gap-3 items-end mb-4">
            <input type="hidden" name="tahun_kedatangan" value="{{ request('tahun_kedatangan', $tahunKedatangan ?? now()->year) }}">
            <input type="hidden" name="tahun_muat" value="{{ request('tahun_muat', $tahunMuat ?? now()->year) }}">
            <input type="hidden" name="mitra_bulan" value="{{ request('mitra_bulan', $mitraBulan ?? now()->month) }}">
            <input type="hidden" name="mitra_tahun" value="{{ request('mitra_tahun', $mitraTahun ?? now()->year) }}">
            <input type="hidden" name="sa_bulan" value="{{ request('sa_bulan', $saBulan ?? now()->month) }}">
            <input type="hidden" name="sa_tahun" value="{{ request('sa_tahun', $saTahun ?? now()->year) }}">

            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Basis</label>
                <select name="top_customer_mode" class="w-full sm:w-40 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    <option value="volume" @selected(request('top_customer_mode', $topCustomerMode ?? 'volume') === 'volume')>Volume (ton)</option>
                    <option value="koli" @selected(request('top_customer_mode', $topCustomerMode ?? 'volume') === 'koli')>Koli</option>
                </select>
            </div>

            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Jenis</label>
                <select name="top_customer_jenis" class="w-full sm:w-40 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    <option value="keduanya" @selected(request('top_customer_jenis', $topCustomerJenis ?? 'kedatangan') === 'keduanya')>Keduanya</option>
                    <option value="kedatangan" @selected(request('top_customer_jenis', $topCustomerJenis ?? 'kedatangan') === 'kedatangan')>Kedatangan (Asal)</option>
                    <option value="muat" @selected(request('top_customer_jenis', $topCustomerJenis ?? 'kedatangan') === 'muat')>Muat (Tujuan)</option>
                </select>
            </div>

            <div class="min-w-[144px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                <select name="top_customer_bulan" class="w-full sm:w-36 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    @foreach ([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'] as $m => $label)
                        <option value="{{ $m }}" @selected((int)request('top_customer_bulan', $topCustomerBulan ?? now()->month) === $m)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[112px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                <input name="top_customer_tahun" value="{{ request('top_customer_tahun', $topCustomerTahun ?? now()->year) }}" type="number" min="2000" max="2100" class="w-full sm:w-28 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
            </div>

            <button type="submit" class="h-10 px-4 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 whitespace-nowrap">
                <i class="fas fa-filter mr-2"></i>
                Terapkan
            </button>
        </form>

        <canvas id="chartTopCustomer" height="160"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const css = getComputedStyle(document.documentElement);
    const kaiOrange = css.getPropertyValue('--kai-orange').trim() || '#FF6B35';
    const kaiNavy = css.getPropertyValue('--kai-navy').trim() || '#1E3A5F';
    const kaiOrangeLight = css.getPropertyValue('--kai-orange-light').trim() || '#FF8C5A';
    const kaiNavyLight = css.getPropertyValue('--kai-navy-light').trim() || '#2C4E7C';

    const scrollToHash = () => {
        const hash = window.location.hash;
        if (!hash) return;
        const el = document.querySelector(hash);
        if (!el) return;
        const y = el.getBoundingClientRect().top + window.pageYOffset - 90;
        window.scrollTo({ top: y, behavior: 'smooth' });
    };

    setTimeout(scrollToHash, 10);

    const downloadCanvasImage = (canvas, mime, filename) => {
        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = canvas.width;
        exportCanvas.height = canvas.height;
        const ctx = exportCanvas.getContext('2d');

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
        ctx.drawImage(canvas, 0, 0);

        const dataUrl = exportCanvas.toDataURL(mime, 0.92);
        const a = document.createElement('a');
        a.href = dataUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

    document.querySelectorAll('.chart-download').forEach(btn => {
        btn.addEventListener('click', () => {
            const canvasId = btn.getAttribute('data-canvas');
            const type = (btn.getAttribute('data-type') || 'png').toLowerCase();
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            const mime = type === 'png' ? 'image/png' : 'image/jpeg';
            const ext = type === 'png' ? 'png' : (type === 'jpg' ? 'jpg' : 'jpeg');
            downloadCanvasImage(canvas, mime, `${canvasId}.${ext}`);
        });
    });

    const labels = @json($bulanLabels);

    const commonOptions = {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    };

    const kedatanganEl = document.getElementById('chartKedatangan');
    if (kedatanganEl) {
        new Chart(kedatanganEl, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'SBI', data: @json($kedatanganSBI), backgroundColor: kaiNavy },
                    { label: 'BBT', data: @json($kedatanganBBT), backgroundColor: kaiOrange },
                    { label: 'BJ', data: @json($kedatanganBJ), backgroundColor: kaiOrangeLight }
                ]
            },
            options: {
                ...commonOptions,
                datasets: {
                    bar: {
                        barPercentage: 0.9,
                        categoryPercentage: 0.7
                    }
                }
            }
        });
    }

    const muatEl = document.getElementById('chartMuat');
    if (muatEl) {
        new Chart(muatEl, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'SBI', data: @json($muatSBI), backgroundColor: kaiNavy, minBarLength: 3 },
                    { label: 'BBT', data: @json($muatBBT), backgroundColor: kaiOrange, minBarLength: 3 },
                    { label: 'BJ', data: @json($muatBJ), backgroundColor: kaiOrangeLight, minBarLength: 3 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.2,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            boxHeight: 14
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const v = Number(ctx.raw || 0);
                                return ctx.dataset.label + ': ' + v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ton';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Ton' }
                    }
                }
            }
        });
    }

    const mitraEl = document.getElementById('chartMitra');
    if (mitraEl) {
        new Chart(mitraEl, {
            type: 'bar',
            data: {
                labels: @json($mitraLabels),
                datasets: [{
                    label: 'Total Volume (ton)',
                    data: @json($mitraVolumes),
                    backgroundColor: kaiOrange,
                    minBarLength: 3,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });
    }

    const yearCompareEl = document.getElementById('chartYearCompare');
    if (yearCompareEl) {
        new Chart(yearCompareEl, {
            type: 'line',
            data: {
                labels: @json($bulanLabelsChart),
                datasets: [
                    {
                        label: 'Kedatangan',
                        data: @json($volMonthKedatangan),
                        borderColor: kaiNavy,
                        backgroundColor: kaiNavy,
                        tension: 0.25,
                    },
                    {
                        label: 'Muat',
                        data: @json($volMonthMuat),
                        borderColor: kaiOrange,
                        backgroundColor: kaiOrange,
                        tension: 0.25,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    const saDates = @json($harianDates);
    const saKed = @json($harianKedatangan);
    const saMuat = @json($harianMuat);

    const saEl = document.getElementById('chartSaHarian');
    let saChart;
    if (saEl) {
        saChart = new Chart(saEl, {
            type: 'line',
            data: {
                labels: saDates,
                datasets: [
                    {
                        label: 'Kedatangan',
                        data: saKed,
                        borderColor: kaiNavy,
                        backgroundColor: kaiNavy,
                        tension: 0.25,
                    },
                    {
                        label: 'Muat',
                        data: saMuat,
                        borderColor: kaiOrange,
                        backgroundColor: kaiOrange,
                        tension: 0.25,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Handle chart visibility based on saJenis value from form
        const saJenis = "{{ $saJenis ?? 'keduanya' }}";
        if (saJenis === 'keduanya') {
            saChart.data.datasets[0].hidden = false;
            saChart.data.datasets[1].hidden = false;
        } else if (saJenis === 'kedatangan') {
            saChart.data.datasets[0].hidden = false;
            saChart.data.datasets[1].hidden = true;
        } else if (saJenis === 'muat') {
            saChart.data.datasets[0].hidden = true;
            saChart.data.datasets[1].hidden = false;
        }
        saChart.update();
    }

    const topCustomerEl = document.getElementById('chartTopCustomer');
    if (topCustomerEl) {
        const topJenis = "{{ $topCustomerJenis ?? 'kedatangan' }}";
        const topMode = "{{ $topCustomerMode ?? 'volume' }}";
        const both = topJenis === 'keduanya';
        const unit = topMode === 'koli' ? 'koli' : 'ton';

        new Chart(topCustomerEl, {
            type: 'bar',
            data: {
                labels: @json($topCustomerLabels),
                datasets: both ? [
                    {
                        label: 'Kedatangan (' + unit + ')',
                        data: @json($topCustomerKedatanganValues ?? []),
                        backgroundColor: kaiNavy,
                        minBarLength: 3,
                    },
                    {
                        label: 'Muat (' + unit + ')',
                        data: @json($topCustomerMuatValues ?? []),
                        backgroundColor: kaiOrange,
                        minBarLength: 3,
                    }
                ] : [{
                    label: (topJenis === 'muat' ? 'Muat' : 'Kedatangan') + ' (' + unit + ')',
                    data: @json($topCustomerValues),
                    backgroundColor: topJenis === 'muat' ? kaiOrange : kaiNavy,
                    minBarLength: 3,
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } },
                plugins: {
                    legend: { display: both, position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const v = Number(ctx.raw || 0);
                                if (topMode === 'koli') {
                                    return ctx.dataset.label + ': ' + v.toLocaleString('id-ID');
                                }
                                return ctx.dataset.label + ': ' + v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
