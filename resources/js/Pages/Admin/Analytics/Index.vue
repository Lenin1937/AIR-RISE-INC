<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ArcElement, BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title, Tooltip,
} from 'chart.js';
import { computed, ref, watch } from 'vue';
import { Line, Pie } from 'vue-chartjs';

ChartJS.register(
    CategoryScale, LinearScale, PointElement, LineElement,
    ArcElement, BarElement, Title, Tooltip, Legend, Filler,
);

// ── props from controller ───────────────────────────────────────────────────
const props = defineProps({
    ga_configured:  { type: Boolean, default: false },
    gsc_configured: { type: Boolean, default: false },
    initial:        { type: Object,  default: () => ({}) },
});

// ── reactive state ──────────────────────────────────────────────────────────
const loading  = ref(false);
const error    = ref('');

// filter controls
const startDate   = ref(props.initial.start_date || '');
const endDate     = ref(props.initial.end_date   || '');
const granularity = ref('day');
const country     = ref('');
const searchType  = ref('web');

// presets
const presets = [
    { label: 'Last 7 days',  days: 7  },
    { label: 'Last 30 days', days: 30 },
    { label: 'Last 90 days', days: 90 },
    { label: 'This year',    days: 0, thisYear: true },
];
const activePreset = ref(30);

// data
const metrics         = ref(props.initial.metrics         || {});
const sessionsOverTime = ref(props.initial.sessions_over_time || { labels: [], sessions: [], users: [] });
const trafficSources  = ref(props.initial.traffic_sources  || []);
const topGaPages      = ref(props.initial.top_ga_pages     || []);
const gscQueries      = ref(props.initial.gsc_queries      || []);
const gscPages        = ref(props.initial.gsc_pages        || []);

// active tab for bottom section
const activeTab = ref('sources');

// ── helpers ──────────────────────────────────────────────────────────────────
const fmtDuration = (secs) => {
    secs = parseInt(secs) || 0;
    const m = Math.floor(secs / 60);
    const s = secs % 60;
    return m > 0 ? `${m}m ${s}s` : `${s}s`;
};

const setPreset = (p) => {
    activePreset.value = p.days;
    const end   = new Date();
    let start;
    if (p.thisYear) {
        start = new Date(end.getFullYear(), 0, 1);
    } else {
        start = new Date();
        start.setDate(start.getDate() - p.days + 1);
    }
    endDate.value   = end.toISOString().slice(0, 10);
    startDate.value = start.toISOString().slice(0, 10);
    fetchData();
};

// ── fetch ─────────────────────────────────────────────────────────────────────
const fetchData = async () => {
    if (!startDate.value || !endDate.value) return;
    loading.value = true;
    error.value   = '';
    try {
        const params = new URLSearchParams({
            start_date:  startDate.value,
            end_date:    endDate.value,
            granularity: granularity.value,
            country:     country.value,
            search_type: searchType.value,
        });
        const res  = await fetch(`/admin/analytics/data?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('Request failed');
        const json = await res.json();
        metrics.value          = json.metrics;
        sessionsOverTime.value = json.sessions_over_time;
        trafficSources.value   = json.traffic_sources;
        topGaPages.value       = json.top_ga_pages;
        gscQueries.value       = json.gsc_queries;
        gscPages.value         = json.gsc_pages;
    } catch (e) {
        error.value = 'Failed to load analytics data. Please try again.';
    } finally {
        loading.value = false;
    }
};

watch(granularity, fetchData);
watch(searchType,  fetchData);

// ── format GA4 date labels (YYYYMMDD / YYYYMM / GGGGWWW → readable) ────────
const fmtChartLabel = (raw) => {
    if (!raw) return raw;
    // YYYYMMDD  e.g. 20260210 → Feb 10
    if (/^\d{8}$/.test(raw)) {
        const d = new Date(`${raw.slice(0,4)}-${raw.slice(4,6)}-${raw.slice(6,8)}`);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
    // YYYYMM  e.g. 202602 → Feb 2026
    if (/^\d{6}$/.test(raw)) {
        const d = new Date(`${raw.slice(0,4)}-${raw.slice(4,6)}-01`);
        return d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    }
    // ISO week  e.g. 2026W08 → keep as-is
    return raw;
};

// ── KPI cards ─────────────────────────────────────────────────────────────
const kpis = computed(() => [
    {
        label:   'Total Sessions',
        value:   (metrics.value.sessions  || 0).toLocaleString(),
        sub:     'Pageload events',
        accent:  '#f4b840',
        iconBg:  'bg-amber-500/15',
        iconClr: 'text-amber-400',
        icon:    'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
    },
    {
        label:   'Unique Users',
        value:   (metrics.value.users     || 0).toLocaleString(),
        sub:     'Distinct visitors',
        accent:  '#60a5fa',
        iconBg:  'bg-blue-500/15',
        iconClr: 'text-blue-400',
        icon:    'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    },
    {
        label:   'Conversion Rate',
        value:   (metrics.value.conversion_rate || 0) + '%',
        sub:     (metrics.value.conversions || 0) + ' total conversions',
        accent:  '#34d399',
        iconBg:  'bg-emerald-500/15',
        iconClr: 'text-emerald-400',
        icon:    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        label:   'Avg. Session Duration',
        value:   fmtDuration(metrics.value.avg_session_duration),
        sub:     'Time on site',
        accent:  '#a78bfa',
        iconBg:  'bg-violet-500/15',
        iconClr: 'text-violet-400',
        icon:    'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        label:   'Bounce Rate',
        value:   (metrics.value.bounce_rate || 0) + '%',
        sub:     'Single-page sessions',
        accent:  '#f87171',
        iconBg:  'bg-red-500/15',
        iconClr: 'text-red-400',
        icon:    'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
]);

// ── Chart: Sessions Over Time ─────────────────────────────────────────────────
const sessionChartData = computed(() => ({
    labels: (sessionsOverTime.value.labels || []).map(fmtChartLabel),
    datasets: [
        {
            label:           'Sessions',
            data:            sessionsOverTime.value.sessions || [],
            borderColor:     '#f4b840',
            backgroundColor: 'rgba(244,184,64,0.08)',
            tension:         0.4,
            fill:            true,
            pointBackgroundColor: '#f4b840',
            pointRadius:     3,
            pointHoverRadius: 6,
        },
        {
            label:           'Users',
            data:            sessionsOverTime.value.users || [],
            borderColor:     '#60a5fa',
            backgroundColor: 'rgba(96,165,250,0.05)',
            tension:         0.4,
            fill:            true,
            pointBackgroundColor: '#60a5fa',
            pointRadius:     3,
            pointHoverRadius: 6,
        },
    ],
}));

const sessionChartOptions = computed(() => ({
    responsive:          true,
    maintainAspectRatio: false,
    interaction: { intersect: false, mode: 'index' },
    plugins: {
        legend: { labels: { color: '#94a3b8', font: { size: 12 } } },
        tooltip: {
            backgroundColor: '#0f1f38',
            titleColor:      '#f1f5f9',
            bodyColor:       '#94a3b8',
            borderColor:     'rgba(255,255,255,0.08)',
            borderWidth:     1,
        },
    },
    scales: {
        x: { ticks: { color: '#64748b', maxRotation: 0 }, grid: { color: 'rgba(255,255,255,0.04)' } },
        y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.04)' } },
    },
}));

// ── Chart: Traffic Sources Pie ─────────────────────────────────────────────────
const sourceColors = ['#f4b840','#60a5fa','#34d399','#f87171','#a78bfa','#fb923c','#22d3ee'];
const sourceChartData = computed(() => ({
    labels: trafficSources.value.slice(0, 7).map(r => r.source),
    datasets: [{
        data:            trafficSources.value.slice(0, 7).map(r => r.sessions),
        backgroundColor: sourceColors,
        borderColor:     '#0a1628',
        borderWidth:     2,
        hoverOffset:     8,
    }],
}));

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right', labels: { color: '#94a3b8', font: { size: 11 }, padding: 14, usePointStyle: true } },
        tooltip: {
            backgroundColor: '#0f1f38',
            titleColor:      '#f1f5f9',
            bodyColor:       '#94a3b8',
            borderColor:     'rgba(255,255,255,0.08)',
            borderWidth:     1,
        },
    },
};

// ── sort / pagination ─────────────────────────────────────────────────────────
const querySort    = ref({ col: 'impressions', dir: -1 });
const pageSort     = ref({ col: 'impressions', dir: -1 });
const sourcesSort  = ref({ col: 'sessions',    dir: -1 });

const sortedQueries = computed(() => [...gscQueries.value].sort((a, b) => querySort.value.dir * ((b[querySort.value.col] ?? 0) - (a[querySort.value.col] ?? 0))));
const sortedGscPages = computed(() => [...gscPages.value].sort((a, b) => pageSort.value.dir * ((b[pageSort.value.col] ?? 0) - (a[pageSort.value.col] ?? 0))));
const sortedSources = computed(() => [...trafficSources.value].sort((a, b) => sourcesSort.value.dir * ((b[sourcesSort.value.col] ?? 0) - (a[sourcesSort.value.col] ?? 0))));

const toggleQuerySort = (col) => { querySort.value = { col, dir: querySort.value.col === col ? -querySort.value.dir : -1 }; };
const togglePageSort  = (col) => { pageSort.value  = { col, dir: pageSort.value.col  === col ? -pageSort.value.dir  : -1 }; };
const toggleSourceSort = (col) => { sourcesSort.value = { col, dir: sourcesSort.value.col === col ? -sourcesSort.value.dir : -1 }; };

const sortIcon = (sort, col) => sort.col === col ? (sort.dir === -1 ? '↓' : '↑') : '↕';

const positionColor = (pos) => {
    if (pos <= 3)  return 'text-emerald-400';
    if (pos <= 10) return 'text-amber-400';
    return 'text-red-400';
};

const ctrColor = (ctr) => {
    if (ctr >= 5)  return 'text-emerald-400';
    if (ctr >= 2)  return 'text-amber-400';
    return 'text-red-400';
};

const tabs = [
    { key: 'sources', label: 'Traffic Sources'   },
    { key: 'ga_pages', label: 'Top GA Pages'    },
    { key: 'queries',  label: 'Search Queries'  },
    { key: 'seo_pages', label: 'Top SEO Pages'  },
];
</script>

<template>
    <AdminLayout>
        <template #header>SEO &amp; Analytics</template>

        <div class="space-y-6 p-6">

            <!-- ── Setup notice ──────────────────────────────────────────── -->
            <div v-if="!ga_configured || !gsc_configured"
                 class="rounded-xl border border-amber-500/30 bg-amber-500/5 px-5 py-4 text-sm">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <div>
                        <p class="font-medium text-amber-300">Demo Data — APIs Not Yet Connected</p>
                        <p class="mt-1 text-amber-400/80">
                            Add your <code class="rounded bg-amber-500/10 px-1">GA4_PROPERTY_ID</code>,
                            <code class="rounded bg-amber-500/10 px-1">GSC_SITE_URL</code>, and
                            <code class="rounded bg-amber-500/10 px-1">GA4_CREDENTIALS_PATH</code> to
                            <code class="rounded bg-amber-500/10 px-1">.env</code> and upload
                            your Google service-account JSON to <code class="rounded bg-amber-500/10 px-1">storage/app/google-credentials.json</code>
                            to see live data.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Filters row ──────────────────────────────────────────── -->
            <div class="flex flex-wrap items-end gap-3 rounded-xl border border-white/[0.07] bg-[#0d1b30] p-4">

                <!-- Presets -->
                <div class="flex flex-wrap gap-2">
                    <button v-for="p in presets" :key="p.label"
                        @click="setPreset(p)"
                        :class="[
                            'rounded-lg px-3 py-1.5 text-xs font-medium transition-all',
                            activePreset === p.days && !p.thisYear
                                ? 'bg-amber-500 text-[#07101e]'
                                : (p.thisYear && activePreset === 0 ? 'bg-amber-500 text-[#07101e]' : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white'),
                        ]">
                        {{ p.label }}
                    </button>
                </div>

                <div class="h-6 w-px bg-white/10"/>

                <!-- Custom dates -->
                <div class="flex items-center gap-2">
                    <input type="date" v-model="startDate"
                        class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-300 focus:border-amber-500/50 focus:outline-none focus:ring-1 focus:ring-amber-500/30"/>
                    <span class="text-xs text-slate-500">to</span>
                    <input type="date" v-model="endDate"
                        class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-300 focus:border-amber-500/50 focus:outline-none focus:ring-1 focus:ring-amber-500/30"/>
                    <button @click="fetchData"
                        class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-[#07101e] hover:bg-amber-400 transition-colors">
                        Apply
                    </button>
                </div>

                <div class="h-6 w-px bg-white/10"/>

                <!-- Granularity -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">Group by</span>
                    <select v-model="granularity"
                        class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-300 focus:border-amber-500/50 focus:outline-none">
                        <option value="day">Day</option>
                        <option value="week">Week</option>
                        <option value="month">Month</option>
                    </select>
                </div>

                <!-- GSC filters (country & type) -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">Country</span>
                    <input type="text" v-model="country" placeholder="e.g. usa"
                        class="w-20 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-300 placeholder-slate-600 focus:border-amber-500/50 focus:outline-none"/>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">Search type</span>
                    <select v-model="searchType"
                        class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-300 focus:border-amber-500/50 focus:outline-none">
                        <option value="web">Web</option>
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                        <option value="news">News</option>
                    </select>
                </div>

                <!-- loading spinner -->
                <div v-if="loading" class="ml-auto flex items-center gap-2 text-xs text-slate-400">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Loading…
                </div>
            </div>

            <!-- error -->
            <div v-if="error" class="rounded-xl border border-red-500/30 bg-red-500/5 px-4 py-3 text-sm text-red-400">
                {{ error }}
            </div>

            <!-- ── KPI Cards ──────────────────────────────────────────────── -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5">
                <div v-for="kpi in kpis" :key="kpi.label"
                     class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-gradient-to-br from-white/[0.04] to-transparent p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-400">{{ kpi.label }}</p>
                            <p class="mt-1 text-2xl font-bold text-white">{{ kpi.value }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ kpi.sub }}</p>
                        </div>
                        <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl', kpi.iconBg]">
                            <svg :class="['h-5 w-5', kpi.iconClr]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="kpi.icon"/>
                            </svg>
                        </div>
                    </div>
                    <!-- accent bar -->
                    <div class="absolute bottom-0 left-0 h-0.5 w-full opacity-30"
                         :style="{ background: kpi.accent }"/>
                </div>
            </div>

            <!-- ── Sitemap URL card ─────────────────────────────────────── -->
            <div class="flex flex-wrap items-center gap-4 rounded-xl border border-white/[0.07] bg-[#0d1b30] px-5 py-3.5">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sitemap URL</span>
                </div>
                <code class="flex-1 rounded-lg bg-white/[0.05] border border-white/[0.08] px-3 py-1.5 text-xs text-amber-300 font-mono select-all">https://corpius.net/sitemap.xml</code>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="navigator.clipboard.writeText('https://corpius.net/sitemap.xml').then(() => { const b = $event.target.closest('button'); b.innerText='Copied!'; setTimeout(()=>b.innerText='Copy',1500); })" class="rounded-lg border border-white/[0.10] bg-white/[0.05] px-3 py-1.5 text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/[0.10] transition">Copy</button>
                    <a href="https://corpius.net/sitemap.xml" target="_blank" rel="noopener" class="rounded-lg border border-white/[0.10] bg-white/[0.05] px-3 py-1.5 text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/[0.10] transition">Open ↗</a>
                    <a href="https://search.google.com/search-console" target="_blank" rel="noopener" class="rounded-lg border border-amber-400/30 bg-amber-400/10 px-3 py-1.5 text-xs font-semibold text-amber-400 hover:bg-amber-400/20 transition">Submit to GSC ↗</a>
                </div>
            </div>

            <!-- ── Sessions Over Time Chart ───────────────────────────────── -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0d1b30] p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Sessions Over Time</h2>
                    <span class="text-xs text-slate-500">{{ startDate }} → {{ endDate }}</span>
                </div>
                <div class="h-64">
                    <Line v-if="sessionsOverTime.labels?.length"
                        :data="sessionChartData"
                        :options="sessionChartOptions"/>
                    <div v-else class="flex h-full items-center justify-center text-sm text-slate-600">
                        No session data for this period.
                    </div>
                </div>
            </div>

            <!-- ── Tabs Section ───────────────────────────────────────────── -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0d1b30] overflow-hidden">

                <!-- Tab bar -->
                <div class="flex gap-0 border-b border-white/[0.07]">
                    <button v-for="tab in tabs" :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'px-5 py-3.5 text-sm font-medium transition-all border-b-2 -mb-px',
                            activeTab === tab.key
                                ? 'border-amber-500 text-amber-400'
                                : 'border-transparent text-slate-500 hover:text-slate-300',
                        ]">
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Tab: Traffic Sources -->
                <div v-if="activeTab === 'sources'" class="p-5">
                    <div class="grid gap-6 lg:grid-cols-3">
                        <!-- Pie chart -->
                        <div class="flex items-center justify-center">
                            <div class="h-64 w-full max-w-xs">
                                <Pie v-if="trafficSources.length" :data="sourceChartData" :options="pieChartOptions"/>
                                <div v-else class="flex h-full items-center justify-center text-sm text-slate-600">No data</div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="lg:col-span-2 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-white/[0.06]">
                                        <th class="py-2 pr-4 text-left text-xs font-medium text-slate-500">Source / Medium</th>
                                        <th @click="toggleSourceSort('sessions')" class="cursor-pointer py-2 pr-4 text-right text-xs font-medium text-slate-500 hover:text-white">
                                            Sessions {{ sortIcon(sourcesSort, 'sessions') }}
                                        </th>
                                        <th @click="toggleSourceSort('users')" class="cursor-pointer py-2 pr-4 text-right text-xs font-medium text-slate-500 hover:text-white">
                                            Users {{ sortIcon(sourcesSort, 'users') }}
                                        </th>
                                        <th @click="toggleSourceSort('bounce_rate')" class="cursor-pointer py-2 pr-4 text-right text-xs font-medium text-slate-500 hover:text-white">
                                            Bounce {{ sortIcon(sourcesSort, 'bounce_rate') }}
                                        </th>
                                        <th @click="toggleSourceSort('conversion_rate')" class="cursor-pointer py-2 text-right text-xs font-medium text-slate-500 hover:text-white">
                                            Conv. {{ sortIcon(sourcesSort, 'conversion_rate') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(row, i) in sortedSources" :key="i"
                                        class="border-b border-white/[0.04] hover:bg-white/[0.02]">
                                        <td class="py-2.5 pr-4">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-block h-2 w-2 rounded-full flex-shrink-0"
                                                      :style="{ background: sourceColors[i % sourceColors.length] }"/>
                                                <span class="text-slate-300 font-medium truncate max-w-[160px]">{{ row.source }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 pr-4 text-right text-slate-300">{{ row.sessions.toLocaleString() }}</td>
                                        <td class="py-2.5 pr-4 text-right text-slate-400">{{ row.users.toLocaleString() }}</td>
                                        <td class="py-2.5 pr-4 text-right text-slate-400">{{ row.bounce_rate }}%</td>
                                        <td class="py-2.5 text-right font-medium"
                                            :class="row.conversion_rate >= 3 ? 'text-emerald-400' : 'text-slate-400'">
                                            {{ row.conversion_rate }}%
                                        </td>
                                    </tr>
                                    <tr v-if="!sortedSources.length">
                                        <td colspan="5" class="py-8 text-center text-sm text-slate-600">No traffic source data.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab: Top GA Pages -->
                <div v-if="activeTab === 'ga_pages'" class="p-5 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/[0.06]">
                                <th class="py-2 pr-4 text-left text-xs font-medium text-slate-500">Page</th>
                                <th class="py-2 pr-4 text-right text-xs font-medium text-slate-500">Pageviews</th>
                                <th class="py-2 pr-4 text-right text-xs font-medium text-slate-500">Sessions</th>
                                <th class="py-2 pr-4 text-right text-xs font-medium text-slate-500">Users</th>
                                <th class="py-2 pr-4 text-right text-xs font-medium text-slate-500">Bounce Rate</th>
                                <th class="py-2 text-right text-xs font-medium text-slate-500">Avg. Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in topGaPages" :key="i"
                                class="border-b border-white/[0.04] hover:bg-white/[0.02]">
                                <td class="py-2.5 pr-4 font-medium text-slate-300 max-w-xs truncate">{{ row.page }}</td>
                                <td class="py-2.5 pr-4 text-right text-slate-300">{{ (row.pageviews||0).toLocaleString() }}</td>
                                <td class="py-2.5 pr-4 text-right text-slate-400">{{ (row.sessions||0).toLocaleString() }}</td>
                                <td class="py-2.5 pr-4 text-right text-slate-400">{{ (row.users||0).toLocaleString() }}</td>
                                <td class="py-2.5 pr-4 text-right text-slate-400">{{ row.bounce_rate }}%</td>
                                <td class="py-2.5 text-right text-slate-400">{{ fmtDuration(row.avg_time) }}</td>
                            </tr>
                            <tr v-if="!topGaPages.length">
                                <td colspan="6" class="py-8 text-center text-sm text-slate-600">No page data available.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tab: Search Queries (GSC) -->
                <div v-if="activeTab === 'queries'" class="p-5 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/[0.06]">
                                <th class="py-2 pr-4 text-left text-xs font-medium text-slate-500">Query</th>
                                <th @click="toggleQuerySort('impressions')" class="cursor-pointer py-2 pr-4 text-right text-xs font-medium text-slate-500 hover:text-white">
                                    Impressions {{ sortIcon(querySort, 'impressions') }}
                                </th>
                                <th @click="toggleQuerySort('clicks')" class="cursor-pointer py-2 pr-4 text-right text-xs font-medium text-slate-500 hover:text-white">
                                    Clicks {{ sortIcon(querySort, 'clicks') }}
                                </th>
                                <th @click="toggleQuerySort('ctr')" class="cursor-pointer py-2 pr-4 text-right text-xs font-medium text-slate-500 hover:text-white">
                                    CTR {{ sortIcon(querySort, 'ctr') }}
                                </th>
                                <th @click="toggleQuerySort('position')" class="cursor-pointer py-2 text-right text-xs font-medium text-slate-500 hover:text-white">
                                    Avg. Position {{ sortIcon(querySort, 'position') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in sortedQueries" :key="i"
                                class="border-b border-white/[0.04] hover:bg-white/[0.02]">
                                <td class="py-2.5 pr-4 font-medium text-slate-300">{{ row.query }}</td>
                                <td class="py-2.5 pr-4 text-right text-slate-400">{{ (row.impressions||0).toLocaleString() }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium text-sky-400">{{ (row.clicks||0).toLocaleString() }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium" :class="ctrColor(row.ctr)">{{ row.ctr }}%</td>
                                <td class="py-2.5 text-right font-bold tabular-nums" :class="positionColor(row.position)">
                                    #{{ row.position }}
                                </td>
                            </tr>
                            <tr v-if="!sortedQueries.length">
                                <td colspan="5" class="py-8 text-center text-sm text-slate-600">No GSC query data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tab: Top SEO Pages (GSC) -->
                <div v-if="activeTab === 'seo_pages'" class="p-5 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/[0.06]">
                                <th class="py-2 pr-4 text-left text-xs font-medium text-slate-500">Page</th>
                                <th @click="togglePageSort('impressions')" class="cursor-pointer py-2 pr-4 text-right text-xs font-medium text-slate-500 hover:text-white">
                                    Impressions {{ sortIcon(pageSort, 'impressions') }}
                                </th>
                                <th @click="togglePageSort('clicks')" class="cursor-pointer py-2 pr-4 text-right text-xs font-medium text-slate-500 hover:text-white">
                                    Clicks {{ sortIcon(pageSort, 'clicks') }}
                                </th>
                                <th @click="togglePageSort('ctr')" class="cursor-pointer py-2 pr-4 text-right text-xs font-medium text-slate-500 hover:text-white">
                                    CTR {{ sortIcon(pageSort, 'ctr') }}
                                </th>
                                <th @click="togglePageSort('position')" class="cursor-pointer py-2 text-right text-xs font-medium text-slate-500 hover:text-white">
                                    Avg. Position {{ sortIcon(pageSort, 'position') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in sortedGscPages" :key="i"
                                class="border-b border-white/[0.04] hover:bg-white/[0.02]">
                                <td class="py-2.5 pr-4">
                                    <div>
                                        <p class="font-medium text-slate-300">{{ row.page }}</p>
                                        <p class="text-xs text-slate-600 truncate max-w-xs">{{ row.full_url }}</p>
                                    </div>
                                </td>
                                <td class="py-2.5 pr-4 text-right text-slate-400">{{ (row.impressions||0).toLocaleString() }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium text-sky-400">{{ (row.clicks||0).toLocaleString() }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium" :class="ctrColor(row.ctr)">{{ row.ctr }}%</td>
                                <td class="py-2.5 text-right font-bold tabular-nums" :class="positionColor(row.position)">
                                    #{{ row.position }}
                                </td>
                            </tr>
                            <tr v-if="!sortedGscPages.length">
                                <td colspan="5" class="py-8 text-center text-sm text-slate-600">No GSC page data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
