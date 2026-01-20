@extends('dashboard.layout')

@section('title', 'Capaian Target')

@section('header', 'Capaian Target')

@section('content')
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow-md p-6 mb-6 reveal">
    <form method="GET" action="{{ route('preview.target') }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Filter Berdasarkan Tahun</label>
            <select name="tahun" class="h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                @foreach(($availableYears ?? []) as $y)
                    <option value="{{ $y }}" @selected((int)($tahun ?? now()->year) === (int)$y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="h-10 px-4 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 whitespace-nowrap">
            <i class="fas fa-filter mr-2"></i>
            Terapkan Filter
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow-md p-6 reveal">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-base">{{ ($yearAchievement['label'] ?? ('Tahun ' . ($tahun ?? ''))) }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $yearAchievement['text'] ?? 'Belum ada data' }}</p>
                    <p class="text-sm text-gray-500">Asli: {{ number_format((float)($yearAchievement['actual_ton'] ?? 0), 2) }} ton | Target: {{ number_format((float)($yearAchievement['target_ton'] ?? 0), 2) }} ton</p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center
                    @if(($yearAchievement['type'] ?? 'neutral') === 'over') bg-green-600
                    @elseif(($yearAchievement['type'] ?? 'neutral') === 'under') bg-red-600
                    @else bg-gray-400 @endif
                ">
                    @if(($yearAchievement['type'] ?? 'neutral') === 'over')
                        <i class="fas fa-arrow-up text-white text-xl"></i>
                    @elseif(($yearAchievement['type'] ?? 'neutral') === 'under')
                        <i class="fas fa-arrow-down text-white text-xl"></i>
                    @else
                        <i class="fas fa-minus text-white text-xl"></i>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-gray-500 text-base">Capaian Bulan</p>
                            <p id="monthAchievementText" class="text-2xl font-bold text-gray-800">-</p>
                            <p id="monthAchievementSub" class="text-sm text-gray-500">Asli: 0.00 ton | Target: 0.00 ton</p>
                        </div>
                        <div class="min-w-[160px]">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Bulan</label>
                            <select id="monthSelect" class="h-10 w-full px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="monthAchievementIcon" class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-400">
                    <i id="monthAchievementIconI" class="fas fa-minus text-white text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Bar Chart – Perbandingan Volume Muat Asli vs Target ({{ $tahun ?? '' }})</h3>
    </div>
    <div class="h-[360px]">
        <canvas id="targetVsActualChart"></canvas>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6 reveal mt-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Data Target</h3>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun Program</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target (kg)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target (ton)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse(($targetList ?? []) as $idx => $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm text-gray-900">{{ ($targetList->firstItem() ?? 0) + $idx }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ ($row->bulan ?? 0) >= 1 && ($row->bulan ?? 0) <= 12 ? (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][(int)$row->bulan - 1] ?? $row->bulan) : ($row->bulan ?? '-') }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $row->tahun_program }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ number_format((int)($row->target_kg ?? 0), 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ number_format(((float)($row->target_kg ?? 0) / 1000), 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>Belum ada data target</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($targetList))
        <div class="mt-4">
            {{ $targetList->onEachSide(1)->links() }}
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const selectedYear = Number("{{ (int) ($tahun ?? now()->year) }}");

    const monthCards = @json($achievementCards ?? []);
    const initialMonth = Number("{{ (int) ($initialMonth ?? 1) }}");

    const monthSelect = document.getElementById('monthSelect');
    const monthAchievementText = document.getElementById('monthAchievementText');
    const monthAchievementSub = document.getElementById('monthAchievementSub');
    const monthAchievementIcon = document.getElementById('monthAchievementIcon');
    const monthAchievementIconI = document.getElementById('monthAchievementIconI');

    function applyMonthCard(monthNumber) {
        const m = Number(monthNumber);
        const card = (monthCards || []).find(x => Number(x.bulan) === m);
        if (!card) {
            monthAchievementText.textContent = '-';
            monthAchievementSub.textContent = 'Asli: 0.00 ton | Target: 0.00 ton';
            monthAchievementIcon.classList.remove('bg-green-600', 'bg-red-600');
            monthAchievementIcon.classList.add('bg-gray-400');
            monthAchievementIconI.classList.remove('fa-arrow-up', 'fa-arrow-down');
            monthAchievementIconI.classList.add('fa-minus');
            return;
        }

        monthAchievementText.textContent = String(card.text || '-');
        const actualTon = Number(card.actual_ton || 0);
        const targetTon = Number(card.target_ton || 0);
        monthAchievementSub.textContent = 'Asli: ' + actualTon.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ton | Target: ' + targetTon.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ton';

        monthAchievementIcon.classList.remove('bg-green-600', 'bg-red-600', 'bg-gray-400');
        monthAchievementIconI.classList.remove('fa-arrow-up', 'fa-arrow-down', 'fa-minus');

        if (card.type === 'over') {
            monthAchievementIcon.classList.add('bg-green-600');
            monthAchievementIconI.classList.add('fa-arrow-up');
        } else if (card.type === 'under') {
            monthAchievementIcon.classList.add('bg-red-600');
            monthAchievementIconI.classList.add('fa-arrow-down');
        } else {
            monthAchievementIcon.classList.add('bg-gray-400');
            monthAchievementIconI.classList.add('fa-minus');
        }
    }

    if (monthSelect) {
        const init = (initialMonth >= 1 && initialMonth <= 12) ? initialMonth : 1;
        monthSelect.value = String(init);
        applyMonthCard(init);

        monthSelect.addEventListener('change', function () {
            applyMonthCard(this.value);
        });
    }

    const labels = @json($bulanLabels ?? []);
    const actualData = @json($chartActualTons ?? []);
    const targetData = @json($chartTargetTons ?? []);

    const ctx = document.getElementById('targetVsActualChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Muat Asli (ton)',
                        data: actualData,
                        backgroundColor: 'rgba(255, 107, 53, 0.70)',
                        borderColor: 'rgba(255, 107, 53, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Target (ton)',
                        data: targetData,
                        backgroundColor: 'rgba(30, 58, 95, 0.60)',
                        borderColor: 'rgba(30, 58, 95, 1)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Ton'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const v = Number(ctx.raw || 0);
                                return ctx.dataset.label + ': ' + v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
    }
})();
</script>
@endsection
