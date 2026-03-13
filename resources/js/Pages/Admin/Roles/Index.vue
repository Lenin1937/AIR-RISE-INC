<template>
  <AdminLayout title="Role Management">
    <div class="space-y-6">

      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-[22px] font-bold text-white tracking-tight">Role Management</h1>
          <p class="mt-0.5 text-[13px] text-gray-400">Manage user roles and permissions for your platform</p>
        </div>
        <div class="flex items-center gap-2.5">
          <Link
            :href="route('admin.users.create')"
            class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-semibold text-gray-300 hover:bg-white/[0.08] hover:text-white transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Add User
          </Link>
          <Link
            :href="route('admin.roles.create')"
            class="inline-flex items-center gap-2 h-9 px-4 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition shadow-lg shadow-amber-400/20"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Create Role
          </Link>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Roles -->
        <div
          class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5"
          :style="{ boxShadow: '0 0 28px 0 rgba(59,130,246,0.10)' }"
        >
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/[0.08] to-transparent pointer-events-none"/>
          <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-blue-500/60 to-transparent"/>
          <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-blue-500/20 flex items-center justify-center">
              <svg class="w-4.5 h-4.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
          </div>
          <div class="text-2xl font-bold text-white">{{ stats?.total_roles ?? roles.length }}</div>
          <div class="mt-1 text-[12px] text-gray-400 font-medium">Total Roles</div>
        </div>

        <!-- Total Permissions -->
        <div
          class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5"
          :style="{ boxShadow: '0 0 28px 0 rgba(34,197,94,0.10)' }"
        >
          <div class="absolute inset-0 bg-gradient-to-br from-green-500/[0.08] to-transparent pointer-events-none"/>
          <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-green-500/60 to-transparent"/>
          <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-green-500/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
          </div>
          <div class="text-2xl font-bold text-white">{{ stats?.total_permissions ?? permissions.length }}</div>
          <div class="mt-1 text-[12px] text-gray-400 font-medium">Total Permissions</div>
        </div>

        <!-- Active Users -->
        <div
          class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5"
          :style="{ boxShadow: '0 0 28px 0 rgba(168,85,247,0.10)' }"
        >
          <div class="absolute inset-0 bg-gradient-to-br from-purple-500/[0.08] to-transparent pointer-events-none"/>
          <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-purple-500/60 to-transparent"/>
          <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-purple-500/20 flex items-center justify-center">
              <svg style="width:18px;height:18px" class="text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
          </div>
          <div class="text-2xl font-bold text-white">{{ stats?.total_users ?? totalUsers }}</div>
          <div class="mt-1 text-[12px] text-gray-400 font-medium">Active Users</div>
        </div>

        <!-- Admin Roles -->
        <div
          class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5"
          :style="{ boxShadow: '0 0 28px 0 rgba(251,191,36,0.10)' }"
        >
          <div class="absolute inset-0 bg-gradient-to-br from-amber-400/[0.08] to-transparent pointer-events-none"/>
          <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-amber-400/60 to-transparent"/>
          <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-amber-400/20 flex items-center justify-center">
              <svg style="width:18px;height:18px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
          </div>
          <div class="text-2xl font-bold text-white">{{ stats?.admin_roles ?? adminRoles }}</div>
          <div class="mt-1 text-[12px] text-gray-400 font-medium">Admin Roles</div>
        </div>
      </div>

      <!-- Roles Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <div
          v-for="role in roles"
          :key="role.id"
          class="relative flex flex-col rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 hover:border-amber-400/30 transition group"
        >
          <!-- Card top: icon + name + actions -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                :class="roleIconBg(role.name)"
              >
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="roleIconColor(role.name)">
                  <path v-if="['administrator','super-admin'].includes(role.name)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                  <path v-else-if="role.name === 'client'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
              <div>
                <div class="text-[14px] font-bold text-white leading-tight">{{ role.display_name }}</div>
                <div class="text-[11px] text-gray-500 font-mono mt-0.5">{{ role.name }}</div>
              </div>
            </div>
            <div class="flex items-center gap-1.5 ml-2">
              <button @click="router.visit(route('admin.roles.edit', role.id))" class="w-7 h-7 rounded-lg bg-white/[0.04] hover:bg-amber-400/20 flex items-center justify-center transition">
                <svg style="width:13px;height:13px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button
                v-if="!['administrator','super-admin','client'].includes(role.name)"
                @click="deleteRole(role.id)"
                class="w-7 h-7 rounded-lg bg-white/[0.04] hover:bg-red-500/20 flex items-center justify-center transition"
              >
                <svg style="width:13px;height:13px" class="text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Description -->
          <p class="text-[12px] text-gray-400 leading-relaxed mb-4 flex-1">{{ role.description || 'No description provided.' }}</p>

          <!-- Meta rows -->
          <div class="space-y-2 mb-4">
            <div class="flex items-center justify-between">
              <span class="text-[12px] text-gray-500">Permissions</span>
              <span class="text-[12px] font-semibold text-gray-300">{{ role.permissions_count }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-[12px] text-gray-500">Users Assigned</span>
              <span class="text-[12px] font-semibold text-gray-300">{{ role.users_count }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-[12px] text-gray-500">Created</span>
              <span class="text-[12px] font-semibold text-gray-300">{{ fmt(role.created_at) }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 pt-4 border-t border-white/[0.06]">
            <button
              @click="router.visit(route('admin.roles.show', role.id))"
              class="flex-1 h-8 rounded-lg border border-white/[0.08] bg-white/[0.03] text-[12px] font-semibold text-gray-300 hover:bg-white/[0.07] hover:text-white transition"
            >
              View Details
            </button>
            <button
              @click="router.visit(route('admin.roles.edit', role.id))"
              class="flex-1 h-8 rounded-lg bg-amber-400 text-[12px] font-semibold text-[#07101e] hover:bg-amber-300 transition"
            >
              Edit Role
            </button>
          </div>
        </div>
      </div>

      <!-- Available Permissions -->
      <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
        <h2 class="text-[15px] font-bold text-white mb-4">Available Permissions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="permission in permissions"
            :key="typeof permission === 'string' ? permission : permission.name"
            class="flex items-center gap-3 p-3 rounded-xl border border-white/[0.05] bg-white/[0.02] hover:border-amber-400/20 transition"
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
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  roles: Array,
  permissions: Array,
  stats: Object
})

const totalUsers = computed(() =>
  props.roles.reduce((s, r) => s + (r.users_count || 0), 0)
)

const adminRoles = computed(() =>
  props.roles.filter(r => ['administrator','super-admin','moderator','staff'].includes(r.name)).length
)

const fmt = d => new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

const permName = p => typeof p === 'string' ? p : (p.name ?? '')
const permLabel = p => {
  const n = permName(p)
  return n.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const roleIconBg = name => {
  if (['administrator','super-admin'].includes(name)) return 'bg-amber-400/20'
  if (name === 'client') return 'bg-green-500/20'
  if (name === 'moderator') return 'bg-purple-500/20'
  return 'bg-blue-500/20'
}

const roleIconColor = name => {
  if (['administrator','super-admin'].includes(name)) return 'text-amber-400'
  if (name === 'client') return 'text-green-400'
  if (name === 'moderator') return 'text-purple-400'
  return 'text-blue-400'
}

const deleteRole = id => {
  if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
    router.delete(route('admin.roles.destroy', id))
  }
}
</script>
