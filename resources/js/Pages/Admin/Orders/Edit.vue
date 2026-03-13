<template>
  <AdminLayout title="Professional Order Editor">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Professional Header with Action Bar -->
        <div class="mb-8">
          <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-[#d4a02f] to-[#b8941a] rounded-xl flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-[#0b1e33]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-3xl font-bold leading-7 text-white sm:text-4xl">
                    Professional Order Editor
                  </h1>
                  <p class="mt-2 text-lg text-gray-300">
                    Order {{ order.order_number }} • {{ order.service_type }} • {{ order.client_name }}
                  </p>
                  <div class="mt-2 flex items-center space-x-4">
                    <span :class="{
                      'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30': order.status === 'pending',
                      'bg-blue-500/20 text-blue-400 border border-blue-500/30': order.status === 'in_progress' || order.status === 'under_review',
                      'bg-green-500/20 text-green-400 border border-green-500/30': order.status === 'completed',
                      'bg-red-500/20 text-red-400 border border-red-500/30': order.status === 'cancelled',
                      'bg-orange-500/20 text-orange-400 border border-orange-500/30': order.status === 'on_hold'
                    }" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                      {{ order.status_display }}
                    </span>
                    <span class="text-sm text-gray-400">
                      Created {{ formatDate(order.created_at) }}
                    </span>
                    <span class="text-sm text-[#d4a02f] font-medium">
                      ${{ (order.amount / 100).toFixed(2) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
              <Link :href="`/admin/orders/${order.id}`" class="inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg text-gray-300 bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Order
              </Link>
              <button
                @click="saveAndContinue"
                :disabled="processing"
                class="inline-flex items-center px-4 py-2 border border-[#d4a02f] rounded-lg text-[#d4a02f] bg-[#d4a02f]/10 hover:bg-[#d4a02f]/20 focus:outline-none focus:ring-2 focus:ring-[#d4a02f] transition-all duration-200 disabled:opacity-50"
              >
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span v-if="processing">Saving...</span>
                <span v-else>Save & Continue</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Professional Tabbed Interface -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 shadow-2xl border border-gray-700 rounded-2xl overflow-hidden">
          <!-- Tab Navigation -->
          <div class="border-b border-gray-700 overflow-x-auto">
            <nav class="flex space-x-8 px-6 min-w-max" aria-label="Tabs">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  activeTab === tab.id
                    ? 'border-[#d4a02f] text-[#d4a02f] bg-[#d4a02f]/10'
                    : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600',
                  'whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200'
                ]"
              >
                <div class="flex items-center">
                  <component :is="tab.icon" class="w-5 h-5 mr-2" />
                  {{ tab.name }}
                </div>
              </button>
            </nav>
          </div>

          <!-- Tab Content -->
          <div class="p-4 sm:p-8">
            <!-- Order Details Tab -->
            <div v-if="activeTab === 'details'" class="space-y-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Status & Progress -->
                <div class="space-y-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Order Status</label>
                    <div class="relative">
                      <select
                        v-model="form.status"
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f] appearance-none pr-10"
                      >
                        <option value="pending">🟡 Order Received</option>
                        <option value="in_progress">🔵 In Progress</option>
                        <option value="under_review">🟠 Under Review</option>
                        <option value="approved">✅ Approved</option>
                        <option value="filed">📋 Filed with State</option>
                        <option value="completed">🟢 Completed</option>
                        <option value="cancelled">🔴 Cancelled</option>
                        <option value="refunded">↩️ Refunded</option>
                      </select>
                      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </div>
                    </div>
                    <div v-if="errors.status" class="mt-2 text-sm text-red-400">{{ errors.status }}</div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Processing Priority</label>
                    <select
                      v-model="form.processing_speed"
                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f]"
                    >
                      <option value="standard">🚶 Standard (7-10 days)</option>
                      <option value="expedited">🚀 Expedited (3-5 days)</option>
                      <option value="rush">⚡ Rush (1-2 days)</option>
                    </select>
                  </div>
                </div>

                <!-- Dates & Timeline -->
                <div class="space-y-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Estimated Completion Date</label>
                    <input
                      type="date"
                      v-model="form.estimated_completion_date"
                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f]"
                    />
                    <div v-if="errors.estimated_completion_date" class="mt-2 text-sm text-red-400">{{ errors.estimated_completion_date }}</div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">State Filing Date</label>
                    <input
                      type="date"
                      v-model="form.state_filing_date"
                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f]"
                    />
                  </div>
                </div>
              </div>

              <!-- Company Information Section -->
              <div class="border-t border-gray-600 pt-8">
                <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                  <svg class="w-6 h-6 mr-3 text-[#d4a02f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  Company Information
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Business Name</label>
                    <input
                      type="text"
                      v-model="form.entity_name"
                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f]"
                      placeholder="Enter business name..."
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">State of Incorporation</label>
                    <input
                      type="text"
                      v-model="form.state"
                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f]"
                      placeholder="e.g., Delaware, California..."
                    />
                  </div>
                  <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-3">Business Purpose</label>
                    <textarea
                      v-model="form.business_purpose"
                      rows="3"
                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f] placeholder-gray-400"
                      placeholder="Describe the business purpose..."
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Financial Details Tab -->
            <div v-if="activeTab === 'financial'" class="space-y-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Payment Information -->
                <div class="space-y-6">
                  <h3 class="text-xl font-semibold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3 text-[#d4a02f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Payment Details
                  </h3>
                  <div class="bg-gray-700/50 rounded-xl p-6 space-y-4">
                    <div class="flex justify-between items-center">
                      <span class="text-gray-300">Service Fee:</span>
                      <span class="text-white font-medium">${{ (form.service_fee || 0).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                      <span class="text-gray-300">State Fee:</span>
                      <span class="text-white font-medium">${{ (form.state_fee || 0).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                      <span class="text-gray-300">Processing Fee:</span>
                      <span class="text-white font-medium">${{ (form.processing_fee || 0).toFixed(2) }}</span>
                    </div>
                    <div class="border-t border-gray-600 pt-4">
                      <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-white">Total Amount:</span>
                        <span class="text-xl font-bold text-[#d4a02f]">${{ (form.total_amount || 0).toFixed(2) }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Fee Adjustments -->
                <div class="space-y-6">
                  <h3 class="text-xl font-semibold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3 text-[#d4a02f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                    Fee Adjustments
                  </h3>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-300 mb-2">Service Fee</label>
                      <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400">$</span>
                        <input
                          type="number"
                          step="0.01"
                          v-model="form.service_fee"
                          class="w-full pl-8 pr-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f]"
                        />
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-300 mb-2">State Fee</label>
                      <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400">$</span>
                        <input
                          type="number"
                          step="0.01"
                          v-model="form.state_fee"
                          class="w-full pl-8 pr-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f]"
                        />
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-300 mb-2">Processing Fee</label>
                      <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400">$</span>
                        <input
                          type="number"
                          step="0.01"
                          v-model="form.processing_fee"
                          class="w-full pl-8 pr-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f]"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes & Communications Tab -->
            <div v-if="activeTab === 'notes'" class="space-y-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Internal Notes -->
                <div>
                  <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-[#d4a02f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Internal Notes
                  </h3>
                  <div class="space-y-4">
                    <textarea
                      v-model="form.internal_notes"
                      rows="8"
                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f] placeholder-gray-400"
                      placeholder="Add internal notes about this order... (Only visible to admin staff)"
                    />
                    <div v-if="errors.internal_notes" class="text-sm text-red-400">{{ errors.internal_notes }}</div>
                    
                    <!-- Quick Note Templates -->
                    <div class="space-y-2">
                      <p class="text-sm font-medium text-gray-300">Quick Templates:</p>
                      <div class="flex flex-wrap gap-2">
                        <button
                          v-for="template in noteTemplates"
                          :key="template.id"
                          @click="addNoteTemplate(template.text)"
                          class="px-3 py-1 text-xs bg-gray-600 text-gray-300 rounded-lg hover:bg-gray-500 transition-colors"
                        >
                          {{ template.name }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Special Instructions -->
                <div>
                  <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-[#d4a02f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Special Instructions
                  </h3>
                  <div class="space-y-4">
                    <textarea
                      v-model="form.special_instructions"
                      rows="8"
                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-[#d4a02f] focus:border-[#d4a02f] placeholder-gray-400"
                      placeholder="Any special instructions for processing this order..."
                    />
                    <div v-if="errors.special_instructions" class="text-sm text-red-400">{{ errors.special_instructions }}</div>
                  </div>

                  <!-- Client Communication History -->
                  <div class="mt-8">
                    <h4 class="text-lg font-medium text-white mb-4">Communication History</h4>
                    <div class="bg-gray-700/50 rounded-xl p-4 max-h-64 overflow-y-auto">
                      <div v-if="order.messages && order.messages.length > 0" class="space-y-3">
                        <div v-for="message in order.messages.slice(-5)" :key="message.id" class="border-b border-gray-600 pb-3 last:border-b-0">
                          <div class="flex justify-between items-start">
                            <div class="text-sm text-gray-300">
                              <strong class="text-white">{{ message.from_admin ? 'Admin' : 'Client' }}:</strong>
                              {{ message.content }}
                            </div>
                            <span class="text-xs text-gray-500">{{ formatDate(message.created_at) }}</span>
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-gray-400 text-sm italic">No messages yet</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Documents Tab -->
            <div v-if="activeTab === 'documents'" class="space-y-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Uploaded Documents -->
                <div>
                  <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-[#d4a02f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Client Documents
                  </h3>
                  <div class="space-y-4">
                    <div v-if="order.required_documents && Object.keys(order.required_documents).length > 0">
                      <!-- Identity Documents -->
                      <div v-if="order.required_documents.passport || order.required_documents.id_card || order.required_documents.drivers_license" class="mb-6">
                        <h4 class="text-sm font-medium text-gray-300 mb-3">Identity Documents</h4>
                        <div class="space-y-3">
                          <div v-if="order.required_documents.passport" class="flex items-center justify-between p-4 bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                              <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2a1 1 0 01-1 1H6a1 1 0 01-1-1v-2h8z" clip-rule="evenodd"/>
                                </svg>
                              </div>
                              <div>
                                <p class="text-white font-medium">Passport</p>
                                <p class="text-sm text-gray-400">{{ order.required_documents.passport.original_name }}</p>
                              </div>
                            </div>
                            <div class="flex space-x-2">
                              <button class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                View
                              </button>
                              <button class="px-3 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700">
                                Download
                              </button>
                            </div>
                          </div>

                          <div v-if="order.required_documents.id_card" class="flex items-center justify-between p-4 bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                              <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2a1 1 0 01-1 1H6a1 1 0 01-1-1v-2h8z" clip-rule="evenodd"/>
                                </svg>
                              </div>
                              <div>
                                <p class="text-white font-medium">ID Card</p>
                                <p class="text-sm text-gray-400">{{ order.required_documents.id_card.original_name }}</p>
                              </div>
                            </div>
                            <div class="flex space-x-2">
                              <button class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                View
                              </button>
                              <button class="px-3 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700">
                                Download
                              </button>
                            </div>
                          </div>

                          <div v-if="order.required_documents.drivers_license" class="flex items-center justify-between p-4 bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                              <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2a1 1 0 01-1 1H6a1 1 0 01-1-1v-2h8z" clip-rule="evenodd"/>
                                </svg>
                              </div>
                              <div>
                                <p class="text-white font-medium">Driver's License</p>
                                <p class="text-sm text-gray-400">{{ order.required_documents.drivers_license.original_name }}</p>
                              </div>
                            </div>
                            <div class="flex space-x-2">
                              <button class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                View
                              </button>
                              <button class="px-3 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700">
                                Download
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Photos -->
                      <div v-if="order.required_documents.photos && order.required_documents.photos.length > 0">
                        <h4 class="text-sm font-medium text-gray-300 mb-3">Photos</h4>
                        <div class="grid grid-cols-2 gap-3">
                          <div v-for="(photo, index) in order.required_documents.photos" :key="index" class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                              <div class="w-8 h-8 bg-pink-500/20 rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-4 h-4 text-pink-400" fill="currentColor" viewBox="0 0 20 20">
                                  <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                </svg>
                              </div>
                              <div>
                                <p class="text-white text-sm">Photo {{ index + 1 }}</p>
                                <p class="text-xs text-gray-400">{{ photo.original_name }}</p>
                              </div>
                            </div>
                            <button class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                              View
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="text-gray-400 text-center py-8">
                      <svg class="w-12 h-12 mx-auto mb-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      <p>No documents uploaded yet</p>
                    </div>
                  </div>
                </div>

                <!-- Document Requirements -->
                <div>
                  <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-[#d4a02f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Document Checklist
                  </h3>
                  <div class="space-y-4">
                    <div class="bg-gray-700/50 rounded-xl p-6">
                      <div class="space-y-3">
                        <div class="flex items-center justify-between">
                          <div class="flex items-center">
                            <div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center mr-3">
                              <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                              </svg>
                            </div>
                            <span class="text-white">Identity Documents</span>
                          </div>
                          <span class="text-sm text-green-400">Complete</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                          <div class="flex items-center">
                            <div class="w-5 h-5 rounded-full bg-yellow-500 flex items-center justify-center mr-3">
                              <span class="text-xs text-white">!</span>
                            </div>
                            <span class="text-white">Photos (if required)</span>
                          </div>
                          <span class="text-sm text-yellow-400">Pending</span>
                        </div>

                        <div class="flex items-center justify-between">
                          <div class="flex items-center">
                            <div class="w-5 h-5 rounded-full bg-gray-500 flex items-center justify-center mr-3">
                              <span class="text-xs text-white">-</span>
                            </div>
                            <span class="text-white">Additional Documents</span>
                          </div>
                          <span class="text-sm text-gray-400">Not Required</span>
                        </div>
                      </div>
                    </div>

                    <!-- Document Upload for Admin -->
                    <div class="border-2 border-dashed border-gray-600 rounded-xl p-6 text-center">
                      <svg class="w-8 h-8 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                      </svg>
                      <p class="text-gray-300 mb-2">Upload Additional Documents</p>
                      <p class="text-sm text-gray-400 mb-4">Add any additional documents for this order</p>
                      <button class="px-4 py-2 bg-[#d4a02f] text-[#0b1e33] rounded-lg hover:bg-[#d4a02f]/80 transition-colors">
                        Choose Files
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Professional Action Bar -->
          <div class="border-t border-gray-700 bg-gray-800/50 px-4 sm:px-8 py-4 sm:py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div class="flex flex-wrap items-center gap-3">
                <div class="text-sm text-gray-400">
                  Last updated: {{ formatDate(order.updated_at) }}
                </div>
                <div v-if="hasUnsavedChanges" class="flex items-center text-sm text-yellow-400">
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  Unsaved changes
                </div>
              </div>
              <div class="flex flex-wrap gap-3">
                <Link :href="`/admin/orders/${order.id}`" class="px-6 py-3 border border-gray-600 rounded-xl text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                  Cancel
                </Link>
                <button
                  @click="saveDraft"
                  :disabled="processing"
                  class="px-6 py-3 border border-[#d4a02f] rounded-xl text-[#d4a02f] bg-[#d4a02f]/10 hover:bg-[#d4a02f]/20 focus:outline-none focus:ring-2 focus:ring-[#d4a02f] transition-all duration-200 disabled:opacity-50"
                >
                  <span v-if="processing">Saving...</span>
                  <span v-else>Save Draft</span>
                </button>
                <button
                  @click="updateOrder"
                  :disabled="processing"
                  class="px-8 py-3 bg-gradient-to-r from-[#d4a02f] to-[#b8941a] text-[#0b1e33] rounded-xl hover:from-[#d4a02f]/90 hover:to-[#b8941a]/90 focus:outline-none focus:ring-2 focus:ring-[#d4a02f] font-semibold transition-all duration-200 disabled:opacity-50 shadow-lg"
                >
                  <span v-if="processing" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Updating Order...
                  </span>
                  <span v-else class="flex items-center">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Order
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { onMounted, ref, watch } from 'vue'

const props = defineProps({
  order: Object,
  errors: Object
})

// Tab Management
const activeTab = ref('details')
const processing = ref(false)
const hasUnsavedChanges = ref(false)

// Tab Configuration
const tabs = [
  {
    id: 'details',
    name: 'Order Details',
    icon: 'svg',
  },
  {
    id: 'financial',
    name: 'Financial',
    icon: 'svg',
  },
  {
    id: 'notes',
    name: 'Notes & Communication',
    icon: 'svg',
  },
  {
    id: 'documents',
    name: 'Documents',
    icon: 'svg',
  },
]

// Form Data
const form = ref({
  status: props.order.status,
  estimated_completion_date: props.order.estimated_completion ? props.order.estimated_completion.split('T')[0] : '',
  state_filing_date: props.order.state_filing_date ? props.order.state_filing_date.split('T')[0] : '',
  processing_speed: props.order.processing_speed || 'standard',
  entity_name: props.order.business_name || '',
  state: props.order.state || '',
  business_purpose: props.order.business_purpose || '',
  service_fee: parseFloat(props.order.service_fee || 0),
  state_fee: parseFloat(props.order.state_fee || 0),
  processing_fee: parseFloat(props.order.processing_fee || 0),
  total_amount: parseFloat(props.order.total_amount || 0),
  internal_notes: props.order.internal_notes || '',
  special_instructions: props.order.special_instructions || ''
})

// Note Templates for Quick Access
const noteTemplates = [
  { id: 1, name: 'Documents Received', text: 'All required documents have been received and reviewed.' },
  { id: 2, name: 'Pending Client Response', text: 'Waiting for client response on additional information requested.' },
  { id: 3, name: 'State Filing Complete', text: 'Documents have been successfully filed with the state.' },
  { id: 4, name: 'Rush Processing', text: 'Order marked for expedited processing due to client request.' },
  { id: 5, name: 'Quality Review', text: 'Order is currently under quality review before final submission.' },
]

// Watch for changes to detect unsaved modifications
watch(form, () => {
  hasUnsavedChanges.value = true
}, { deep: true })

// Auto-calculate total when fees change
watch([() => form.value.service_fee, () => form.value.state_fee, () => form.value.processing_fee], () => {
  form.value.total_amount = 
    parseFloat(form.value.service_fee || 0) + 
    parseFloat(form.value.state_fee || 0) + 
    parseFloat(form.value.processing_fee || 0)
})

// Methods
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const addNoteTemplate = (templateText) => {
  const currentNotes = form.value.internal_notes || ''
  const timestamp = new Date().toLocaleString()
  const newNote = `[${timestamp}] ${templateText}`
  
  form.value.internal_notes = currentNotes 
    ? `${currentNotes}\n\n${newNote}`
    : newNote
}

const saveDraft = () => {
  processing.value = true
  
  // Add a draft flag to the form data
  const draftData = { ...form.value, is_draft: true }
  
  router.patch(`/admin/orders/${props.order.id}`, draftData, {
    onSuccess: () => {
      processing.value = false
      hasUnsavedChanges.value = false
      // Show success message without redirecting
    },
    onError: () => {
      processing.value = false
    }
  })
}

const saveAndContinue = () => {
  processing.value = true
  
  router.patch(`/admin/orders/${props.order.id}`, form.value, {
    onSuccess: () => {
      processing.value = false
      hasUnsavedChanges.value = false
      // Stay on edit page but show success
    },
    onError: () => {
      processing.value = false
    }
  })
}

const updateOrder = () => {
  processing.value = true
  
  router.patch(`/admin/orders/${props.order.id}`, form.value, {
    onSuccess: () => {
      processing.value = false
      hasUnsavedChanges.value = false
      // Redirect to order view
      router.visit(`/admin/orders/${props.order.id}`)
    },
    onError: () => {
      processing.value = false
    }
  })
}

// Keyboard shortcuts
onMounted(() => {
  const handleKeyDown = (event) => {
    // Ctrl+S or Cmd+S to save
    if ((event.ctrlKey || event.metaKey) && event.key === 's') {
      event.preventDefault()
      saveAndContinue()
    }
    
    // Escape to cancel
    if (event.key === 'Escape') {
      router.visit(`/admin/orders/${props.order.id}`)
    }
  }
  
  document.addEventListener('keydown', handleKeyDown)
  
  // Cleanup
  return () => {
    document.removeEventListener('keydown', handleKeyDown)
  }
})

// Warn before leaving with unsaved changes
onMounted(() => {
  const handleBeforeUnload = (event) => {
    if (hasUnsavedChanges.value) {
      event.preventDefault()
      event.returnValue = 'You have unsaved changes. Are you sure you want to leave?'
    }
  }
  
  window.addEventListener('beforeunload', handleBeforeUnload)
  
  return () => {
    window.removeEventListener('beforeunload', handleBeforeUnload)
  }
})
</script>