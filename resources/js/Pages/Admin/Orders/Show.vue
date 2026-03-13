<template>
  <AdminLayout title="Order Details">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-3 flex-wrap">
          <h1 class="text-[20px] font-bold text-white tracking-tight">Order {{ order.order_number }}</h1>
          <span :class="statusBadgeClass(order.status)" class="inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1">
            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80"></span>
            {{ order.status_display || order.status }}
          </span>
        </div>
        <p class="text-[12px] text-gray-500 mt-1">{{ order.service_type }} &middot; Created {{ formatDate(order.created_at) }}</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button
          v-if="order.status === 'pending' || order.status === 'under_review'"
          @click="showApprovalModal = true"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-[12px] font-semibold text-emerald-400 hover:bg-emerald-500/20 transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Approve
        </button>
        <button
          v-if="order.status === 'pending' || order.status === 'under_review'"
          @click="showRejectionModal = true"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-red-500/10 border border-red-500/20 text-[12px] font-semibold text-red-400 hover:bg-red-500/20 transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          Reject
        </button>
        <button
          @click="showEditModal = true"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-400/10 border border-amber-400/20 text-[12px] font-semibold text-amber-400 hover:bg-amber-400/20 transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          Edit Order
        </button>
        <Link
          href="/admin/orders"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/[0.04] border border-white/[0.07] text-[12px] font-semibold text-gray-300 hover:bg-white/[0.08] transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Back to Orders
        </Link>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

      <!-- Left: 2 cols -->
      <div class="xl:col-span-2 space-y-5">

        <!-- Order Information -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Order Information</h2>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Order Number</span>
              <span class="text-[12px] font-medium text-white font-mono">{{ order.order_number }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Service Type</span>
              <span class="text-[12px] font-medium text-white">{{ order.service_type }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Business Name</span>
              <span class="text-[12px] font-medium text-white">{{ order.business_name || '—' }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">State</span>
              <span class="text-[12px] font-medium text-white">{{ order.state || '—' }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Business Purpose</span>
              <span class="text-[12px] font-medium text-white max-w-xs text-right">{{ order.business_purpose || '—' }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Processing Speed</span>
              <span class="text-[12px] font-medium text-white capitalize">
                {{ order.processing_speed === 'pro' ? 'Pro (Expedited)' : order.processing_speed === 'economic' ? 'Economic (Standard)' : (order.package_type || '—') }}
              </span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Status</span>
              <span :class="statusBadgeClass(order.status)" class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2.5 py-1">
                {{ order.status_display || order.status }}
              </span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Progress</span>
              <div class="flex items-center gap-2">
                <div class="w-24 h-1.5 bg-white/[0.06] rounded-full overflow-hidden">
                  <div class="h-full bg-amber-400 rounded-full" :style="`width: ${order.progress_percentage}%`"></div>
                </div>
                <span class="text-[12px] font-semibold text-amber-400">{{ order.progress_percentage }}%</span>
              </div>
            </div>
            <div v-if="order.estimated_completion" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Est. Completion</span>
              <span class="text-[12px] font-medium text-white">{{ formatDate(order.estimated_completion) }}</span>
            </div>
            <div v-if="order.lottery_year" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Lottery Year</span>
              <span class="text-[12px] font-medium text-white">{{ order.lottery_year }}</span>
            </div>
          </div>
        </div>

        <!-- Add-ons -->
        <div v-if="order.addons && order.addons.length > 0" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
              <h2 class="text-[13px] font-bold text-white">Selected Add-ons</h2>
            </div>
            <span class="text-[11px] font-semibold text-amber-400">{{ order.addons.length }} add-on{{ order.addons.length !== 1 ? 's' : '' }}</span>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div v-for="addonId in order.addons" :key="addonId" class="flex items-center justify-between px-5 py-3">
              <div class="flex items-center gap-2.5">
                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                <span class="text-[12px] font-medium text-white">{{ addonLabel(addonId) }}</span>
              </div>
              <span class="text-[12px] font-semibold text-amber-400">${{ addonPrice(addonId) }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3 bg-amber-400/[0.03]">
              <span class="text-[12px] font-semibold text-gray-400">Add-ons Total</span>
              <span class="text-[13px] font-bold text-amber-400">${{ formatAmount(order.addons_total) }}</span>
            </div>
          </div>
        </div>

        <!-- Client Information -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Client Information</h2>
          </div>
          <div class="px-5 py-4 flex items-center gap-4 border-b border-white/[0.04]">
            <div class="w-10 h-10 rounded-full shrink-0 overflow-hidden">
              <img
                v-if="order.user?.profile_picture"
                :src="order.user.profile_picture"
                :alt="order.client_name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-[13px] font-bold text-white" :style="avatarGradient(order.client_name)">
                {{ initials(order.client_name) }}
              </div>
            </div>
            <div>
              <p class="text-[13px] font-semibold text-white">{{ order.client_name }}</p>
              <p class="text-[11px] text-gray-500">{{ order.client_email }}</p>
            </div>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Email</span>
              <span class="text-[12px] font-medium text-white">{{ order.client_email }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Phone</span>
              <span class="text-[12px] font-medium text-white">{{ order.client_phone || '—' }}</span>
            </div>
            <div v-if="order.user?.company_name" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Company</span>
              <span class="text-[12px] font-medium text-white">{{ order.user.company_name }}</span>
            </div>
            <div v-if="order.user?.address_line_1" class="flex items-start justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500 shrink-0">Address</span>
              <span class="text-[12px] font-medium text-white text-right max-w-[60%]">
                {{ order.user.address_line_1 }}
                <span v-if="order.user.address_line_2">, {{ order.user.address_line_2 }}</span>
                <span v-if="order.user.city || order.user.zip_code"><br/>{{ [order.user.city, order.user.zip_code].filter(Boolean).join(', ') }}</span>
                <span v-if="order.user.state || order.user.country"><br/>{{ [order.user.state, order.user.country].filter(Boolean).join(', ') }}</span>
              </span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Member Since</span>
              <span class="text-[12px] font-medium text-white">{{ formatDate(order.user?.created_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Personal Information (from order form) -->
        <div v-if="order.applicant_info && Object.keys(order.applicant_info).some(k => order.applicant_info[k])" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/></svg>
            <h2 class="text-[13px] font-bold text-white">Personal Information</h2>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div v-if="order.applicant_info.full_name" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Full Name</span>
              <span class="text-[12px] font-medium text-white">{{ order.applicant_info.full_name }}</span>
            </div>
            <div v-if="order.applicant_info.email" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Email Address</span>
              <span class="text-[12px] font-medium text-white">{{ order.applicant_info.email }}</span>
            </div>
            <div v-if="order.applicant_info.phone" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Phone Number</span>
              <span class="text-[12px] font-medium text-white">{{ order.applicant_info.phone }}</span>
            </div>
            <div v-if="order.applicant_info.date_of_birth" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Date of Birth</span>
              <span class="text-[12px] font-medium text-white">
                {{ formatDate(order.applicant_info.date_of_birth) }}
                <span class="text-gray-500 ml-1">(Age: {{ calcAge(order.applicant_info.date_of_birth) }})</span>
              </span>
            </div>
            <div v-if="order.applicant_info.ssn" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">SSN / ITIN</span>
              <span class="text-[12px] font-medium text-white font-mono tracking-wider">{{ maskSsn(order.applicant_info.ssn) }}</span>
            </div>
            <div v-if="order.applicant_info.address" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Street Address</span>
              <span class="text-[12px] font-medium text-white text-right max-w-[60%]">{{ order.applicant_info.address }}</span>
            </div>
            <div v-if="order.applicant_info.city || order.applicant_info.zip" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">City / ZIP</span>
              <span class="text-[12px] font-medium text-white">
                {{ [order.applicant_info.city, order.applicant_info.zip].filter(Boolean).join(', ') }}
              </span>
            </div>
            <div v-if="order.applicant_info.country" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Country</span>
              <span class="text-[12px] font-medium text-white">{{ order.applicant_info.country }}</span>
            </div>
          </div>
        </div>

        <!-- Green Card: Family Information -->
        <div v-if="order.family_info && Array.isArray(order.family_info) && order.family_info.length > 0" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Family Members</h2>
            <span class="ml-auto text-[11px] text-gray-500">{{ order.family_info.length }} member{{ order.family_info.length !== 1 ? 's' : '' }}</span>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div v-for="(member, idx) in order.family_info" :key="idx" class="px-5 py-4">
              <p class="text-[11px] font-bold text-amber-400 uppercase tracking-wider mb-2">Member {{ idx + 1 }}<span v-if="member.relationship"> · {{ member.relationship }}</span></p>
              <div class="space-y-1.5">
                <div v-if="member.full_name" class="flex justify-between text-[12px]"><span class="text-gray-500">Full Name</span><span class="text-white font-medium">{{ member.full_name }}</span></div>
                <div v-if="member.date_of_birth" class="flex justify-between text-[12px]"><span class="text-gray-500">Date of Birth</span><span class="text-white">{{ member.date_of_birth }}</span></div>
                <div v-if="member.passport_number" class="flex justify-between text-[12px]"><span class="text-gray-500">Passport</span><span class="text-white font-mono">{{ member.passport_number }}</span></div>
                <div v-if="member.nationality" class="flex justify-between text-[12px]"><span class="text-gray-500">Nationality</span><span class="text-white">{{ member.nationality }}</span></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Corp/LLC: Shareholders -->
        <div v-if="order.shareholders && order.shareholders.length > 0" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Shareholders</h2>
            <span v-if="order.authorized_shares" class="ml-auto text-[11px] text-gray-500">Authorized: {{ order.authorized_shares }} shares</span>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div v-for="(sh, idx) in order.shareholders" :key="idx" class="px-5 py-3 flex justify-between items-start">
              <div>
                <p class="text-[12px] font-semibold text-white">{{ sh.name || 'Shareholder ' + (idx + 1) }}</p>
                <p v-if="sh.email" class="text-[11px] text-gray-500">{{ sh.email }}</p>
              </div>
              <span v-if="sh.shares || sh.percentage" class="text-[11px] font-semibold text-amber-400">{{ sh.shares ? sh.shares + ' shares' : sh.percentage + '%' }}</span>
            </div>
          </div>
        </div>

        <!-- Corp: Directors & Officers -->
        <div v-if="(order.directors && order.directors.length > 0) || (order.officers && order.officers.length > 0)" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <h2 class="text-[13px] font-bold text-white">Directors &amp; Officers</h2>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div v-for="(dir, idx) in order.directors" :key="'dir-' + idx" class="px-5 py-3 flex justify-between">
              <span class="text-[12px] text-white">{{ dir.name || 'Director ' + (idx + 1) }}</span>
              <span class="text-[11px] font-semibold text-blue-400">Director</span>
            </div>
            <div v-for="(off, idx) in order.officers" :key="'off-' + idx" class="px-5 py-3 flex justify-between">
              <span class="text-[12px] text-white">{{ off.name || 'Officer ' + (idx + 1) }}</span>
              <span class="text-[11px] font-semibold text-purple-400">{{ off.title || 'Officer' }}</span>
            </div>
          </div>
        </div>

        <!-- LLC: Members & Managers -->
        <div v-if="(order.members && order.members.length > 0) || (order.managers && order.managers.length > 0)" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Members &amp; Managers</h2>
            <span v-if="order.management_structure" class="ml-auto text-[11px] text-gray-500 capitalize">{{ order.management_structure.replace('_', '-') }}</span>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div v-for="(m, idx) in order.members" :key="'mem-' + idx" class="px-5 py-3 flex justify-between">
              <span class="text-[12px] text-white">{{ m.name || 'Member ' + (idx + 1) }}</span>
              <span class="text-[11px] font-semibold text-teal-400">{{ m.ownership ? m.ownership + '%' : 'Member' }}</span>
            </div>
            <div v-for="(mg, idx) in order.managers" :key="'mgr-' + idx" class="px-5 py-3 flex justify-between">
              <span class="text-[12px] text-white">{{ mg.name || 'Manager ' + (idx + 1) }}</span>
              <span class="text-[11px] font-semibold text-indigo-400">Manager</span>
            </div>
          </div>
        </div>

        <!-- Nonprofit: Charitable Purpose & Board -->
        <div v-if="order.charitable_purpose || (order.board_members && order.board_members.length > 0)" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Nonprofit Details</h2>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div v-if="order.charitable_purpose" class="px-5 py-3">
              <span class="text-[12px] text-gray-500 block mb-1">Charitable Purpose</span>
              <span class="text-[12px] text-white">{{ order.charitable_purpose }}</span>
            </div>
            <div v-if="order.c501c3_application" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">501(c)(3) Application</span>
              <span class="text-[11px] font-semibold text-emerald-400">Requested</span>
            </div>
            <div v-for="(bm, idx) in order.board_members" :key="idx" class="px-5 py-3 flex justify-between">
              <span class="text-[12px] text-white">{{ bm.name || 'Board Member ' + (idx + 1) }}</span>
              <span class="text-[11px] text-gray-500">{{ bm.title || 'Board Member' }}</span>
            </div>
          </div>
        </div>

        <!-- Uploaded Documents -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Identity &amp; Documents</h2>
          </div>
          <div v-if="hasUploadedDocs" class="p-5 space-y-4">
            <!-- Identity docs: passport, id_card, drivers_license -->
            <div v-if="order.required_documents.passport || order.required_documents.id_card || order.required_documents.drivers_license">
              <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Identity</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <template v-for="[type, label, color] in [['passport','Passport','blue'],['id_card','ID Card','emerald'],['drivers_license','Driver\'s License','amber']]" :key="type">
                  <div v-if="order.required_documents[type]" class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                      <div :class="`w-7 h-7 rounded-lg bg-${color}-500/10 flex items-center justify-center`">
                        <svg :class="`w-3.5 h-3.5 text-${color}-400`" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2a1 1 0 01-1 1H6a1 1 0 01-1-1v-2h8z" clip-rule="evenodd"/></svg>
                      </div>
                      <div>
                        <p class="text-[12px] font-semibold text-white">{{ label }}</p>
                        <p class="text-[11px] text-gray-500 truncate max-w-[120px]">{{ docName(order.required_documents[type]) }}</p>
                      </div>
                    </div>
                    <a :href="`/admin/orders/${order.id}/documents/${type}/download`" download class="text-[11px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">Download</a>
                  </div>
                </template>
              </div>
            </div>
            <!-- Photos -->
            <div v-if="orderPhotos.length > 0">
              <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Photos</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div v-for="(photo, i) in orderPhotos" :key="i" class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.06]">
                  <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-pink-500/10 flex items-center justify-center">
                      <svg class="w-3.5 h-3.5 text-pink-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                      <p class="text-[12px] font-semibold text-white">Photo {{ i + 1 }}</p>
                      <p class="text-[11px] text-gray-500 truncate max-w-[120px]">{{ docName(photo) }}</p>
                    </div>
                  </div>
                  <a :href="`/admin/orders/${order.id}/documents/photos/download?index=${i}`" download class="text-[11px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">Download</a>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="px-5 py-8 text-center">
            <svg class="w-8 h-8 text-gray-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <p class="text-[12px] text-gray-600">No documents uploaded yet</p>
          </div>
        </div>

      </div><!-- /Left -->

      <!-- Right Sidebar -->
      <div class="space-y-5">

        <!-- Payment Details -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Payment Details</h2>
          </div>
          <div class="divide-y divide-white/[0.04]">
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Method</span>
              <span class="text-[12px] font-medium text-white capitalize">{{ order.payment_method || '—' }}</span>
            </div>
            <div v-if="order.service_fee > 0" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Service Fee</span>
              <span class="text-[12px] font-medium text-white">${{ formatAmount(order.service_fee) }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Subtotal</span>
              <span class="text-[12px] font-medium text-white">${{ formatAmount(order.subtotal) }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">State Fee</span>
              <span class="text-[12px] font-medium text-white">${{ formatAmount(order.state_fee) }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Processing Fee</span>
              <span class="text-[12px] font-medium text-white">${{ formatAmount(order.processing_fee) }}</span>
            </div>
            <div v-if="order.addons_total > 0" class="flex items-center justify-between px-5 py-3">
              <span class="text-[12px] text-gray-500">Add-ons</span>
              <span class="text-[12px] font-medium text-white">${{ formatAmount(order.addons_total) }}</span>
            </div>
          </div>
          <div class="px-5 py-4 bg-amber-400/[0.04] border-t border-amber-400/[0.08]">
            <div class="flex items-center justify-between">
              <span class="text-[12px] font-semibold text-gray-300">Total Amount</span>
              <span class="text-[18px] font-bold text-amber-400">${{ formatAmount(order.amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Quick Actions</h2>
          </div>
          <div class="p-4 space-y-2">
            <button
              v-if="order.status === 'pending' || order.status === 'under_review'"
              @click="showApprovalModal = true"
              class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-[12px] font-semibold text-emerald-400 hover:bg-emerald-500/20 transition-colors"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Approve Order
            </button>
            <button
              v-if="order.status === 'pending' || order.status === 'under_review'"
              @click="showRejectionModal = true"
              class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-[12px] font-semibold text-red-400 hover:bg-red-500/20 transition-colors"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Reject Order
            </button>
            <button
              @click="showEditModal = true"
              class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.07] text-[12px] font-semibold text-gray-300 hover:bg-white/[0.07] transition-colors"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Edit Order
            </button>
          </div>
        </div>

        <!-- Timeline -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
          <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h2 class="text-[13px] font-bold text-white">Timeline</h2>
          </div>
          <div class="p-5">
            <div v-if="order.timeline_events && order.timeline_events.length > 0" class="space-y-0">
              <div v-for="(event, i) in order.timeline_events" :key="i" class="relative flex gap-3">
                <div class="flex flex-col items-center">
                  <div class="w-7 h-7 rounded-full bg-amber-400/10 border-2 border-amber-400/40 flex items-center justify-center shrink-0 z-10">
                    <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  </div>
                  <div v-if="i < order.timeline_events.length - 1" class="w-px flex-1 bg-white/[0.06] my-1"></div>
                </div>
                <div class="pb-4 min-w-0 flex-1 pt-0.5">
                  <p class="text-[12px] font-semibold text-white leading-snug">{{ event.title }}</p>
                  <p class="text-[11px] text-gray-500 mt-0.5">{{ event.description }}</p>
                  <p class="text-[10px] text-gray-600 mt-1">{{ formatDate(event.completed_at) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-4">
              <p class="text-[12px] text-gray-600">No timeline events yet</p>
            </div>
          </div>
        </div>

      </div><!-- /Sidebar -->
    </div><!-- /Grid -->


    <!-- ====== Approval Modal ====== -->
    <Teleport to="body">
      <div v-if="showApprovalModal" class="fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="showApprovalModal = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-[#0c1c30] border border-white/[0.08] shadow-2xl p-6">
          <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <p class="text-[14px] font-bold text-white">Approve Order</p>
              <p class="text-[11px] text-gray-500">{{ order.order_number }}</p>
            </div>
          </div>
          <p class="text-[12px] text-gray-400 mb-4">A notification will be sent to the client upon approval.</p>
          <div class="mb-5">
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Approval Message</label>
            <textarea
              v-model="approvalMessage"
              rows="3"
              class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] text-[12px] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/50 placeholder-gray-600 resize-none"
              placeholder="Your order has been approved and we will begin processing..."
            />
          </div>
          <div class="flex items-center gap-2 justify-end">
            <button @click="showApprovalModal = false" class="px-4 py-2 rounded-xl border border-white/[0.07] text-[12px] font-semibold text-gray-400 hover:bg-white/[0.04] transition-colors">Cancel</button>
            <button @click="approveOrder" :disabled="processing" class="px-5 py-2 rounded-xl bg-emerald-500 text-[12px] font-semibold text-white hover:bg-emerald-400 disabled:opacity-50 transition-colors">
              {{ processing ? 'Approving…' : 'Approve Order' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ====== Rejection Modal ====== -->
    <Teleport to="body">
      <div v-if="showRejectionModal" class="fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="showRejectionModal = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-[#0c1c30] border border-white/[0.08] shadow-2xl p-6">
          <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-red-500/10 flex items-center justify-center">
              <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <p class="text-[14px] font-bold text-white">Reject Order</p>
              <p class="text-[11px] text-gray-500">{{ order.order_number }}</p>
            </div>
          </div>
          <p class="text-[12px] text-gray-400 mb-4">A notification with your reason will be sent to the client.</p>
          <div class="mb-5">
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Rejection Reason <span class="text-red-400">*</span></label>
            <textarea
              v-model="rejectionMessage"
              rows="3"
              class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] text-[12px] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/50 placeholder-gray-600 resize-none"
              placeholder="Unfortunately, we cannot process your order because..."
            />
          </div>
          <div class="flex items-center gap-2 justify-end">
            <button @click="showRejectionModal = false" class="px-4 py-2 rounded-xl border border-white/[0.07] text-[12px] font-semibold text-gray-400 hover:bg-white/[0.04] transition-colors">Cancel</button>
            <button @click="rejectOrder" :disabled="processing || !rejectionMessage.trim()" class="px-5 py-2 rounded-xl bg-red-500 text-[12px] font-semibold text-white hover:bg-red-400 disabled:opacity-50 transition-colors">
              {{ processing ? 'Rejecting…' : 'Reject Order' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ====== Edit Modal ====== -->
    <Teleport to="body">
      <div v-if="showEditModal" class="fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="showEditModal = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-[#0c1c30] border border-white/[0.08] shadow-2xl p-6">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 rounded-xl bg-amber-400/10 flex items-center justify-center">
              <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div>
              <p class="text-[14px] font-bold text-white">Edit Order</p>
              <p class="text-[11px] text-gray-500">{{ order.order_number }}</p>
            </div>
          </div>
          <div class="space-y-4">
            <div>
              <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Status</label>
              <select
                v-model="editForm.status"
                class="w-full px-4 py-2.5 bg-[#0c1c30] border border-white/[0.08] text-[12px] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400/50 [&>option]:bg-[#0c1c30] [&>option]:text-white"
              >
                <option value="pending" class="bg-[#0c1c30] text-white">Pending</option>
                <option value="in_progress" class="bg-[#0c1c30] text-white">In Progress</option>
                <option value="under_review" class="bg-[#0c1c30] text-white">Under Review</option>
                <option value="approved" class="bg-[#0c1c30] text-white">Approved</option>
                <option value="filed" class="bg-[#0c1c30] text-white">Filed</option>
                <option value="completed" class="bg-[#0c1c30] text-white">Completed</option>
                <option value="cancelled" class="bg-[#0c1c30] text-white">Cancelled</option>
                <option value="refunded" class="bg-[#0c1c30] text-white">Refunded</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Estimated Completion Date</label>
              <input
                type="date"
                v-model="editForm.estimated_completion_date"
                class="w-full px-4 py-2.5 bg-white/[0.04] border border-white/[0.08] text-[12px] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400/50"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Internal Notes</label>
              <textarea
                v-model="editForm.internal_notes"
                rows="3"
                class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] text-[12px] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400/50 placeholder-gray-600 resize-none"
                placeholder="Internal notes for this order…"
              />
            </div>
          </div>
          <div class="flex items-center gap-2 justify-end mt-6">
            <button @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-white/[0.07] text-[12px] font-semibold text-gray-400 hover:bg-white/[0.04] transition-colors">Cancel</button>
            <button @click="updateOrder" :disabled="processing" class="px-5 py-2 rounded-xl bg-amber-400 text-[12px] font-semibold text-[#07101e] hover:bg-amber-300 disabled:opacity-50 transition-colors">
              {{ processing ? 'Saving…' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  order: Object
})

// Modal states
const showApprovalModal = ref(false)
const showRejectionModal = ref(false)
const showEditModal = ref(false)
const processing = ref(false)

// Form data
const approvalMessage = ref('Great news! Your order has been approved and we will begin processing immediately. You will receive updates as we progress through each stage.')
const rejectionMessage = ref('')

const editForm = ref({
  status: props.order.status,
  estimated_completion_date: props.order.estimated_completion ? props.order.estimated_completion.split('T')[0] : '',
  internal_notes: ''
})

// Helpers
const formatDate = (dateString) => {
  if (!dateString) return '—'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric'
  })
}

const formatAmount = (val) => {
  if (val == null) return '0.00'
  const n = parseFloat(val)
  return isNaN(n) ? '0.00' : n.toFixed(2)
}

const initials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
}

const avatarGradient = (name) => {
  const colors = [
    'linear-gradient(135deg,#6366f1,#8b5cf6)',
    'linear-gradient(135deg,#f59e0b,#ef4444)',
    'linear-gradient(135deg,#10b981,#06b6d4)',
    'linear-gradient(135deg,#3b82f6,#6366f1)',
    'linear-gradient(135deg,#ec4899,#f43f5e)',
  ]
  const idx = (name || '').charCodeAt(0) % colors.length
  return `background: ${colors[idx]};`
}

const calcAge = (dob) => {
  if (!dob) return '—'
  const birth = new Date(dob)
  const today = new Date()
  let age = today.getFullYear() - birth.getFullYear()
  const m = today.getMonth() - birth.getMonth()
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--
  return age
}

const addonLabels = {
  registered_agent: 'Registered Agent (1 year)',
  ein: 'EIN Application',
  corporate_kit: 'Corporate Kit & Seal',
  compliance: 'Compliance Service',
  operating_agreement: 'Operating Agreement',
  annual_report: 'Annual Report',
  apostille: 'Apostille',
  good_standing: 'Good Standing Certificate',
  mail_forwarding: 'Mail Forwarding',
  itin: 'ITIN Application',
  tax_consult: 'Tax Consultation',
  banking: 'Banking Setup',
  gc_premium: 'Premium Green Card Service',
  gc_notification: 'Green Card Notification',
  tax_bookkeeping: 'Tax Bookkeeping',
  tax_amendment: 'Tax Amendment',
  rush: 'Rush Processing',
}

const addonPrices = {
  registered_agent: 125, ein: 0, corporate_kit: 85, compliance: 50,
  operating_agreement: 99, annual_report: 149, apostille: 149,
  good_standing: 99, mail_forwarding: 99, itin: 249, tax_consult: 149,
  banking: 49, gc_premium: 79, gc_notification: 29,
  tax_bookkeeping: 299, tax_amendment: 199, rush: 99,
}

const addonLabel = (id) => addonLabels[id] || id
const addonPrice = (id) => addonPrices[id] != null ? addonPrices[id].toFixed(2) : '—'

// Document helpers — handle both legacy path strings and new metadata objects
const docName = (doc) => {
  if (!doc) return ''
  if (typeof doc === 'string') return doc.split('/').pop()
  return doc.original_name || doc.stored_path?.split('/').pop() || 'document'
}

// Normalize photos list — handles both new `photos:[...]` and legacy `photo_0` keys
const orderPhotos = computed(() => {
  const docs = props.order.required_documents
  if (!docs) return []
  if (Array.isArray(docs.photos)) return docs.photos
  return Object.keys(docs)
    .filter(k => /^photo_\d+$/.test(k))
    .sort()
    .map(k => docs[k])
})

const hasUploadedDocs = computed(() => {
  const docs = props.order.required_documents
  if (!docs || typeof docs !== 'object') return false
  return !!(docs.passport || docs.id_card || docs.drivers_license || orderPhotos.value.length > 0)
})

const maskSsn = (ssn) => {
  if (!ssn) return '—'
  const clean = ssn.replace(/\D/g, '')
  if (clean.length === 9) return `${clean.slice(0, 3)}-${clean.slice(3, 5)}-${clean.slice(5)}`
  return ssn
}

const statusBadgeClass = (status) => {
  const map = {
    pending:       'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
    in_progress:   'bg-blue-500/10 text-blue-400 border border-blue-500/20',
    under_review:  'bg-purple-500/10 text-purple-400 border border-purple-500/20',
    approved:      'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    filed:         'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20',
    completed:     'bg-green-500/10 text-green-400 border border-green-500/20',
    cancelled:     'bg-red-500/10 text-red-400 border border-red-500/20',
    refunded:      'bg-gray-500/10 text-gray-400 border border-gray-500/20',
    on_hold:       'bg-orange-500/10 text-orange-400 border border-orange-500/20',
    draft:         'bg-gray-500/10 text-gray-400 border border-gray-500/20',
  }
  return map[status] || 'bg-gray-500/10 text-gray-400 border border-gray-500/20'
}

// Actions
const approveOrder = () => {
  processing.value = true
  router.post(`/admin/orders/${props.order.id}/approve`, {
    message: approvalMessage.value
  }, {
    onSuccess: () => { showApprovalModal.value = false; processing.value = false },
    onError:   () => { processing.value = false }
  })
}

const rejectOrder = () => {
  processing.value = true
  router.post(`/admin/orders/${props.order.id}/reject`, {
    message: rejectionMessage.value
  }, {
    onSuccess: () => { showRejectionModal.value = false; processing.value = false },
    onError:   () => { processing.value = false }
  })
}

const updateOrder = () => {
  processing.value = true
  router.patch(`/admin/orders/${props.order.id}`, editForm.value, {
    onSuccess: () => { showEditModal.value = false; processing.value = false },
    onError:   () => { processing.value = false }
  })
}
</script>
