<template>
  <AdminLayout :title="role.display_name">
    <div class="space-y-6">

      <!-- Header / Breadcrumb -->
      <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
          <nav class="flex items-center gap-2 text-[12px] text-gray-500 mb-3">
            <Link :href="route('admin.roles.index')" class="hover:text-amber-400 transition">Role Management</Link>
            <svg style="width:12px;height:12px" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-300">{{ role.display_name }}</span>
          </nav>
          <h1 class="text-[22px] font-bold text-white tracking-tight">{{ role.display_name }}</h1>
          <p class="mt-0.5 text-[13px] text-gray-400">{{ role.description || 'System role' }}</p>
        </div>
        <Link
          :href="route('admin.roles.edit', role.id)"
          class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-semibold text-gray-300 hover:bg-white/[0.08] hover:text-white transition flex-shrink-0"
        >
          <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Edit Role
        </Link>
      </div>

      <!-- 2-col layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Role Information -->
        <div class="lg:col-span-1">
          <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
            <h2 class="text-[14px] font-bold text-white mb-5">Role Information</h2>
            <dl class="space-y-4">
              <div>
                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Role Name</dt>
                <dd class="text-[13px] text-gray-200 font-mono bg-white/[0.03] px-2.5 py-1.5 rounded-lg inline-block">{{ role.name }}</dd>
              </div>
              <div>
                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Display Name</dt>
                <dd class="text-[13px] text-gray-200">{{ role.display_name }}</dd>
              </div>
              <div>
                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Description</dt>
                <dd class="text-[13px] text-gray-300 leading-relaxed">{{ role.description || '—' }}</dd>
              </div>
              <div class="pt-3 border-t border-white/[0.06] flex justify-between items-center">
                <dt class="text-[12px] text-gray-500">Permissions Count</dt>
                <dd class="text-[13px] font-bold text-white">{{ role.permissions_count ?? role.permissions?.length ?? 0 }}</dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-[12px] text-gray-500">Users Assigned</dt>
                <dd class="text-[13px] font-bold text-white">{{ role.users_count ?? role.users?.length ?? 0 }}</dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-[12px] text-gray-500">Created</dt>
                <dd class="text-[12px] text-gray-300">{{ fmt(role.created_at) }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Right: Permissions + Users -->
        <div class="lg:col-span-2 space-y-5">

          <!-- Assigned Permissions -->
          <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
            <h2 class="text-[14px] font-bold text-white mb-4">Assigned Permissions</h2>
            <div v-if="role.permissions && role.permissions.length" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div
                v-for="permission in role.permissions"
                :key="permName(permission)"
                class="flex items-center gap-3 p-3 rounded-xl border border-white/[0.05] bg-white/[0.02]"
              >
                <div class="w-8 h-8 rounded-lg bg-amber-400/20 flex items-center justify-center flex-shrink-0">
                  <svg style="width:14px;height:14px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                </div>
                <div>
                  <div class="text-[12px] font-semibold text-gray-200">{{ permLabel(permission) }}</div>
                  <div class="text-[11px] text-gray-500 font-mono">{{ permName(permission) }}</div>
                </div>
              </div>
            </div>
            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
              <div class="w-10 h-10 rounded-xl bg-white/[0.03] flex items-center justify-center mb-3">
                <svg style="width:18px;height:18px" class="text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <p class="text-[13px] text-gray-500">No permissions assigned</p>
            </div>
          </div>

          <!-- Users with this Role -->
          <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
            <h2 class="text-[14px] font-bold text-white mb-4">Users with this Role</h2>
            <div v-if="role.users && role.users.length" class="space-y-2.5">
              <div
                v-for="user in role.users"
                :key="user.id"
                class="flex items-center justify-between p-3 rounded-xl border border-white/[0.05] bg-white/[0.02] hover:border-white/[0.09] transition"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 rounded-full flex items-center justify-center text-[12px] font-bold text-white flex-shrink-0"
                    :style="{ background: avatarGrad(user.name) }"
                  >
                    {{ initials(user.name) }}
                  </div>
                  <div>
                    <div class="text-[13px] font-semibold text-gray-200">{{ user.name }}</div>
                    <div class="text-[11px] text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
                <div class="text-[11px] text-gray-500 hidden sm:block">Joined {{ fmt(user.created_at) }}</div>
              </div>
            </div>
            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
              <div class="w-10 h-10 rounded-xl bg-white/[0.03] flex items-center justify-center mb-3">
                <svg style="width:18px;height:18px" class="text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
              <p class="text-[13px] text-gray-500">No users assigned</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ role: Object })

const fmt = d => new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

const permName = p => typeof p === 'string' ? p : (p.name ?? '')
const permLabel = p => permName(p).replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())

const initials = name => (name || '').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()

const avatarGrad = name => {
  const grads = [
    'linear-gradient(135deg,#3b82f6,#6366f1)',
    'linear-gradient(135deg,#f59e0b,#f97316)',
    'linear-gradient(135deg,#10b981,#06b6d4)',
    'linear-gradient(135deg,#8b5cf6,#ec4899)',
    'linear-gradient(135deg,#ef4444,#f97316)',
    'linear-gradient(135deg,#0ea5e9,#6366f1)',
  ]
  const i = (name || '').split('').reduce((a, c) => a + c.charCodeAt(0), 0) % grads.length
  return grads[i]
}
</script>
