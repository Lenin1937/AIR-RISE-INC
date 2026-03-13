<template>
    <div>
        <!-- Profile Picture Section -->
        <div class="mb-5">
            <p class="text-[13px] text-gray-400 mb-4">
                Upload a new profile picture. JPG, PNG or GIF (max. 2MB)
            </p>
            
            <!-- Avatar Display -->
            <div class="flex items-center space-x-4 mb-4">
                <div class="relative">
                    <img 
                        v-if="user?.profile_picture_url" 
                        :src="user.profile_picture_url" 
                        :alt="user.name"
                        class="w-16 h-16 rounded-full object-cover border-2 border-amber-400/20"
                    />
                    <div 
                        v-else
                        class="w-16 h-16 rounded-full bg-amber-400/10 border-2 border-amber-400/20 flex items-center justify-center"
                    >
                        <span class="text-[#d4a02f] text-xl font-bold">
                            {{ getInitials(user?.name || 'U') }}
                        </span>
                    </div>
                    
                    <!-- Hidden File Input -->
                    <input 
                        ref="fileInput"
                        type="file" 
                        accept="image/*" 
                        @change="handleFileSelect"
                        class="hidden"
                    />
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button 
                    @click="triggerFileInput"
                    :disabled="uploading"
                    class="px-4 py-2 bg-[#d4a02f] text-[#0b1e33] font-medium rounded-lg hover:bg-[#d4a02f]/80 focus:outline-none focus:ring-2 focus:ring-[#d4a02f] focus:ring-offset-2 focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm"
                >
                    <span v-if="uploading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading...
                    </span>
                    <span v-else>📷 Choose Photo</span>
                </button>
                
                <button 
                    v-if="user?.profile_picture"
                    @click="removeProfilePicture"
                    :disabled="removing"
                    class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm"
                >
                    <span v-if="removing" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Removing...
                    </span>
                    <span v-else>🗑️ Remove</span>
                </button>
            </div>
        </div>
        
        <!-- Image Preview Modal -->
        <div v-if="showPreview" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 max-w-md w-full mx-4" style="box-shadow:0 0 40px 0 rgba(0,0,0,.5)">
                <h3 class="text-lg font-semibold text-white mb-4">Preview & Upload</h3>
                
                <div class="flex justify-center mb-4">
                    <img 
                        :src="previewUrl" 
                        alt="Preview"
                        class="w-48 h-48 rounded-full object-cover border-4 border-[#d4a02f]/30"
                    />
                </div>
                
                <p class="text-sm text-gray-300 text-center mb-6">
                    Your image will be automatically resized to 300x300 pixels.
                </p>
                
                <div class="flex space-x-3">
                    <button 
                        @click="confirmUpload"
                        :disabled="uploading"
                        class="flex-1 px-4 py-2 bg-[#d4a02f] text-[#0b1e33] font-medium rounded-lg hover:bg-[#d4a02f]/80 focus:outline-none focus:ring-2 focus:ring-[#d4a02f] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="uploading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading...
                        </span>
                        <span v-else>Upload Photo</span>
                    </button>
                    
                    <button 
                        @click="cancelUpload"
                        :disabled="uploading"
                        class="flex-1 px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    user: Object
})

const page = usePage()

// Detect if we're in admin context
const isAdminContext = computed(() => {
    return page.url.startsWith('/admin')
})

// Dynamic routes based on context
const uploadRoute = computed(() => {
    return isAdminContext.value ? '/admin/profile/upload-avatar' : '/profile/upload-avatar'
})

const removeRoute = computed(() => {
    return isAdminContext.value ? '/admin/profile/remove-avatar' : '/profile/remove-avatar'
})

const fileInput = ref(null)
const showPreview = ref(false)
const previewUrl = ref('')
const selectedFile = ref(null)
const uploading = ref(false)
const removing = ref(false)

const getInitials = (name) => {
    if (!name) return 'U'
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2)
}

const triggerFileInput = () => {
    fileInput.value.click()
}

const handleFileSelect = (event) => {
    const file = event.target.files[0]
    if (!file) return
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file.')
        return
    }
    
    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('File size must be less than 2MB.')
        return
    }
    
    selectedFile.value = file
    previewUrl.value = URL.createObjectURL(file)
    showPreview.value = true
}

const confirmUpload = () => {
    if (!selectedFile.value) return
    
    uploading.value = true
    
    const formData = new FormData()
    formData.append('profile_picture', selectedFile.value)
    
    router.post(uploadRoute.value, formData, {
        onSuccess: () => {
            showPreview.value = false
            selectedFile.value = null
            previewUrl.value = ''
            uploading.value = false
            fileInput.value.value = ''
            
            // Refresh the current page to get updated user data
            router.reload({ only: ['user'] })
        },
        onError: (errors) => {
            uploading.value = false
            alert(errors.profile_picture || 'Failed to upload image. Please try again.')
        }
    })
}

const cancelUpload = () => {
    showPreview.value = false
    selectedFile.value = null
    previewUrl.value = ''
    fileInput.value.value = ''
}

const removeProfilePicture = () => {
    if (!confirm('Are you sure you want to remove your profile picture?')) return
    
    removing.value = true
    
    router.delete(removeRoute.value, {
        onSuccess: () => {
            removing.value = false
            
            // Refresh the current page to get updated user data
            router.reload({ only: ['user'] })
        },
        onError: () => {
            removing.value = false
            alert('Failed to remove profile picture. Please try again.')
        }
    })
}
</script>