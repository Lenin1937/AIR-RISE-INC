<template>
  <AdminLayout title="Admin Profile">
    <div class="space-y-6 max-w-3xl">

      <!-- Page Header -->
      <div>
        <h1 class="text-[22px] font-bold text-white tracking-tight">Admin Profile</h1>
        <p class="mt-0.5 text-[13px] text-gray-400">Manage your admin profile and settings</p>
      </div>

      <!-- Profile Picture Card -->
      <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
        <h2 class="text-[14px] font-bold text-white mb-1">Profile Picture</h2>
        <p class="text-[12px] text-gray-500 mb-5">Upload a new profile picture. JPG, PNG or GIF (max. 2MB)</p>

        <div class="flex items-center gap-5">
          <!-- Avatar preview -->
          <div class="relative flex-shrink-0">
            <img
              v-if="user?.profile_picture_url"
              :src="user.profile_picture_url"
              :alt="user.name"
              class="w-16 h-16 rounded-full object-cover ring-2 ring-amber-400/30"
            />
            <div
              v-else
              class="w-16 h-16 rounded-full flex items-center justify-center text-[20px] font-bold text-amber-400 ring-2 ring-amber-400/20"
              :style="{ background: avatarGrad }"
            >
              {{ initials }}
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex items-center gap-2.5">
            <button
              @click="$refs.fileInput.click()"
              :disabled="uploading"
              class="inline-flex items-center gap-2 h-9 px-4 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition shadow-lg shadow-amber-400/20 disabled:opacity-50"
            >
              <svg v-if="uploading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              <svg v-else style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              {{ uploading ? 'Uploading...' : 'Choose Photo' }}
            </button>

            <button
              v-if="user?.profile_picture"
              @click="removePhoto"
              :disabled="removing"
              class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-red-500/30 bg-red-500/10 text-[13px] font-semibold text-red-400 hover:bg-red-500/20 transition disabled:opacity-50"
            >
              <svg style="width:13px;height:13px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              Remove
            </button>
          </div>
        </div>

        <!-- Hidden file input -->
        <input ref="fileInput" type="file" accept="image/*" @change="handleFileSelect" class="hidden" />

        <!-- Preview modal -->
        <div v-if="showPreview" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 px-4">
          <div class="rounded-2xl border border-white/[0.10] bg-[#0c1c30] p-6 w-full max-w-sm">
            <h3 class="text-[15px] font-bold text-white mb-4">Preview & Upload</h3>
            <div class="flex justify-center mb-5">
              <img :src="previewUrl" class="w-28 h-28 rounded-full object-cover ring-2 ring-amber-400/30" />
            </div>
            <div class="flex gap-3">
              <button @click="cancelPreview" class="flex-1 h-9 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-semibold text-gray-300 hover:bg-white/[0.08] transition">Cancel</button>
              <button @click="uploadPhoto" :disabled="uploading" class="flex-1 h-9 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition disabled:opacity-50">
                {{ uploading ? 'Uploading...' : 'Upload' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Information -->
      <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
        <h2 class="text-[14px] font-bold text-white mb-5">Profile Information</h2>

        <!-- Success banner -->
        <div v-if="profileSuccess" class="flex items-center gap-2.5 mb-5 p-3 rounded-xl border border-green-500/20 bg-green-500/[0.06]">
          <svg style="width:14px;height:14px;flex-shrink:0" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-[12px] text-green-300">Profile updated successfully.</span>
        </div>

        <form @submit.prevent="updateProfile" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Name</label>
              <input
                v-model="profileForm.name"
                type="text"
                class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
              />
              <p v-if="profileForm.errors.name" class="mt-1 text-[11px] text-red-400">{{ profileForm.errors.name }}</p>
            </div>
            <div>
              <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Email</label>
              <input
                v-model="profileForm.email"
                type="email"
                class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
              />
              <p v-if="profileForm.errors.email" class="mt-1 text-[11px] text-red-400">{{ profileForm.errors.email }}</p>
            </div>
          </div>
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Phone</label>
            <input
              v-model="profileForm.phone"
              type="tel"
              placeholder="e.g. +1 234 567 8900"
              class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
            />
          </div>
          <div class="pt-2">
            <button
              type="submit"
              :disabled="profileForm.processing"
              class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition shadow-lg shadow-amber-400/20 disabled:opacity-50"
            >
              <svg v-if="profileForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              {{ profileForm.processing ? 'Updating...' : 'Update Profile' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Change Password -->
      <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
        <h2 class="text-[14px] font-bold text-white mb-5">Change Password</h2>

        <!-- Success banner -->
        <div v-if="passwordSuccess" class="flex items-center gap-2.5 mb-5 p-3 rounded-xl border border-green-500/20 bg-green-500/[0.06]">
          <svg style="width:14px;height:14px;flex-shrink:0" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-[12px] text-green-300">Password updated successfully.</span>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-4">
          <div>
            <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Current Password</label>
            <input
              v-model="passwordForm.current_password"
              type="password"
              placeholder="Enter current password"
              class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
            />
            <p v-if="passwordForm.errors.current_password" class="mt-1 text-[11px] text-red-400">{{ passwordForm.errors.current_password }}</p>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">New Password</label>
              <input
                v-model="passwordForm.password"
                type="password"
                placeholder="Enter new password"
                class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
              />
              <p v-if="passwordForm.errors.password" class="mt-1 text-[11px] text-red-400">{{ passwordForm.errors.password }}</p>
            </div>
            <div>
              <label class="block text-[12px] font-semibold text-gray-300 mb-1.5">Confirm New Password</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                placeholder="Repeat new password"
                class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
              />
            </div>
          </div>
          <div class="pt-2">
            <button
              type="submit"
              :disabled="passwordForm.processing"
              class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition shadow-lg shadow-amber-400/20 disabled:opacity-50"
            >
              <svg v-if="passwordForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
        </form>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  user: Object,
  status: String
})

// ---------- Avatar ----------
const initials = computed(() =>
  (props.user?.name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
)
const avatarGrad = 'linear-gradient(135deg,#f59e0b,#f97316)'

// ---------- Photo upload ----------
const fileInput = ref(null)
const previewUrl = ref(null)
const selectedFile = ref(null)
const showPreview = ref(false)
const uploading = ref(false)
const removing = ref(false)

const handleFileSelect = e => {
  const file = e.target.files[0]
  if (!file) return
  selectedFile.value = file
  previewUrl.value = URL.createObjectURL(file)
  showPreview.value = true
}

const cancelPreview = () => {
  showPreview.value = false
  previewUrl.value = null
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

const uploadPhoto = () => {
  if (!selectedFile.value) return
  uploading.value = true
  const fd = new FormData()
  fd.append('profile_picture', selectedFile.value)
  fd.append('_method', 'POST')
  router.post(route('admin.profile.upload-avatar'), fd, {
    forceFormData: true,
    onSuccess: () => { uploading.value = false; showPreview.value = false },
    onError: () => { uploading.value = false }
  })
}

const removePhoto = () => {
  if (!confirm('Remove profile picture?')) return
  removing.value = true
  router.delete(route('admin.profile.remove-avatar'), {
    onFinish: () => { removing.value = false }
  })
}

// ---------- Profile form ----------
const profileForm = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  phone: props.user?.phone ?? ''
})

const profileSuccess = ref(false)

const updateProfile = () => {
  profileForm.patch(route('admin.profile.update'), {
    onSuccess: () => {
      profileSuccess.value = true
      setTimeout(() => { profileSuccess.value = false }, 3000)
    }
  })
}

// ---------- Password form ----------
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const passwordSuccess = ref(false)

const updatePassword = () => {
  passwordForm.put(route('admin.password.update'), {
    onSuccess: () => {
      passwordSuccess.value = true
      passwordForm.reset()
      setTimeout(() => { passwordSuccess.value = false }, 3000)
    }
  })
}
</script>
