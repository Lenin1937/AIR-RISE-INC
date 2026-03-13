<template>
  <AdminLayout title="Create User">
    <div class="space-y-6">

      <!-- Header / Breadcrumb -->
      <div>
        <nav class="flex items-center gap-2 text-[12px] text-gray-500 mb-3">
          <Link :href="route('admin.users.index')" class="hover:text-amber-400 transition">User Management</Link>
          <svg style="width:12px;height:12px" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
          <span class="text-gray-300">Create User</span>
        </nav>
        <h1 class="text-[22px] font-bold text-white tracking-tight">Create New User</h1>
        <p class="mt-0.5 text-[13px] text-gray-400">Add a new user to the system with username, email, password, and role assignment</p>
      </div>

      <!-- Form Card -->
      <div class="max-w-2xl rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
        <form @submit.prevent="submit" class="space-y-5">

          <!-- Username -->
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Username</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. John Smith"
              required
              class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
            />
            <p v-if="errors?.name" class="mt-1 text-[11px] text-red-400">{{ errors.name }}</p>
          </div>

          <!-- Login Email -->
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Login Email</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="e.g. user@example.com"
              required
              class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
            />
            <p v-if="errors?.email" class="mt-1 text-[11px] text-red-400">{{ errors.email }}</p>
          </div>

          <!-- Login Password -->
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Login Password</label>
            <input
              v-model="form.password"
              type="password"
              placeholder="Enter a secure password"
              required
              class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
            />
            <p class="mt-1.5 text-[11px] text-gray-500">Password must be at least 8 characters long</p>
            <p v-if="errors?.password" class="mt-1 text-[11px] text-red-400">{{ errors.password }}</p>
          </div>

          <!-- Confirm Password -->
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Confirm Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Confirm the password"
              required
              class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
            />
            <p v-if="errors?.password_confirmation" class="mt-1 text-[11px] text-red-400">{{ errors.password_confirmation }}</p>
          </div>

          <!-- User Role -->
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">User Role</label>
            <select
              v-model="form.role"
              required
              class="w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0a1628]"
            >
              <option value="">Select a role...</option>
              <option
                v-for="role in roles"
                :key="role.value ?? role.id ?? role.name"
                :value="role.value ?? role.name"
              >
                {{ role.label ?? role.display_name ?? role.name }}
              </option>
            </select>
            <p class="mt-1.5 text-[11px] text-gray-500">Choose the appropriate role for this user</p>
            <p v-if="errors?.role" class="mt-1 text-[11px] text-red-400">{{ errors.role }}</p>
          </div>

          <!-- Role Description hint -->
          <div v-if="selectedRoleDescription" class="flex items-start gap-3 p-3.5 rounded-xl border border-amber-400/20 bg-amber-400/[0.04]">
            <svg style="width:14px;height:14px;flex-shrink:0;margin-top:1px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-[12px] text-amber-200/70">{{ selectedRoleDescription }}</p>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-4 border-t border-white/[0.06]">
            <Link
              :href="route('admin.users.index')"
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
              {{ form.processing ? 'Creating User...' : 'Create User' }}
            </button>
          </div>

        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  roles: Array,
  errors: Object
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: ''
})

const roleDescriptions = {
  'administrator': 'Full access to all administrative functions including user management, system settings, and all business operations.',
  'client': 'Standard user access to client dashboard, order management, document access, and basic services.',
  'moderator': 'Limited administrative access including order management, document approval, and customer support functions.',
  'super-admin': 'System administrator with complete access to all platform features and system-level controls.',
  'staff': 'Basic administrative functions including order processing, document management, and customer communications.'
}

const selectedRoleDescription = computed(() => roleDescriptions[form.role] || null)

const submit = () => form.post(route('admin.users.store'))
</script>
