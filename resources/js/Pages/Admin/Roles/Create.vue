<template>
  <AdminLayout title="Create Role">
    <div class="space-y-6">

      <!-- Header / Breadcrumb -->
      <div>
        <nav class="flex items-center gap-2 text-[12px] text-gray-500 mb-3">
          <Link :href="route('admin.roles.index')" class="hover:text-amber-400 transition">Role Management</Link>
          <svg style="width:12px;height:12px" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
          <span class="text-gray-300">Create Role</span>
        </nav>
        <h1 class="text-[22px] font-bold text-white tracking-tight">Create New Role</h1>
        <p class="mt-0.5 text-[13px] text-gray-400">Define a new role with specific permissions for your platform</p>
      </div>

      <!-- Form Card -->
      <div class="max-w-3xl rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
        <form @submit.prevent="submit" class="space-y-5">

          <!-- Role Name -->
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Role Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. content-manager"
              required
              class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
            />
            <p class="mt-1.5 text-[11px] text-gray-500">Use lowercase letters, numbers, hyphens, and underscores only</p>
            <p v-if="errors?.name" class="mt-1 text-[11px] text-red-400">{{ errors.name }}</p>
          </div>

          <!-- Display Name -->
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Display Name</label>
            <input
              v-model="form.display_name"
              type="text"
              placeholder="e.g. Content Manager"
              required
              class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
            />
            <p v-if="errors?.display_name" class="mt-1 text-[11px] text-red-400">{{ errors.display_name }}</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Describe the purpose and scope of this role..."
              class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition resize-none"
            ></textarea>
            <p v-if="errors?.description" class="mt-1 text-[11px] text-red-400">{{ errors.description }}</p>
          </div>

          <!-- Permissions -->
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-3">Permissions</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              <label
                v-for="permission in permissions"
                :key="permName(permission)"
                class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition"
                :class="form.permissions.includes(permName(permission))
                  ? 'border-amber-400/40 bg-amber-400/[0.06]'
                  : 'border-white/[0.06] bg-white/[0.02] hover:border-white/[0.12]'"
              >
                <div class="mt-0.5 w-4 h-4 rounded flex items-center justify-center flex-shrink-0 border transition"
                  :class="form.permissions.includes(permName(permission))
                    ? 'bg-amber-400 border-amber-400'
                    : 'bg-white/[0.04] border-white/[0.15]'">
                  <svg v-if="form.permissions.includes(permName(permission))" style="width:10px;height:10px" class="text-[#07101e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                  </svg>
                  <input
                    type="checkbox"
                    :value="permName(permission)"
                    v-model="form.permissions"
                    class="sr-only"
                  />
                </div>
                <div>
                  <div class="text-[12px] font-semibold text-gray-200">{{ permLabel(permission) }}</div>
                  <div class="text-[11px] text-gray-500 font-mono">{{ permName(permission) }}</div>
                </div>
              </label>
            </div>
            <p v-if="errors?.permissions" class="mt-2 text-[11px] text-red-400">{{ errors.permissions }}</p>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-4 border-t border-white/[0.06]">
            <Link
              :href="route('admin.roles.index')"
              class="inline-flex items-center h-9 px-5 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-semibold text-gray-300 hover:bg-white/[0.08] hover:text-white transition"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition shadow-lg shadow-amber-400/20 disabled:opacity-50"
            >
              <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              {{ form.processing ? 'Creating...' : 'Create Role' }}
            </button>
          </div>

        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  permissions: Array,
  errors: Object
})

const form = useForm({
  name: '',
  display_name: '',
  description: '',
  permissions: []
})

const permName = p => typeof p === 'string' ? p : (p.name ?? '')
const permLabel = p => permName(p).replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())

const submit = () => form.post(route('admin.roles.store'))
</script>
