<template>
  <div class="relative w-full h-full bg-[#1a2332] rounded-lg p-2 sm:p-4 select-none">
    <div class="relative w-full h-full min-h-[400px] sm:min-h-[500px] overflow-hidden">
      <svg
        viewBox="0 0 959 593"
        preserveAspectRatio="xMinYMin meet"
        class="w-full h-full cursor-grab"
        @wheel.prevent="onWheel"
        @mousedown="startDrag"
        @mousemove="onDrag"
        @mouseup="stopDrag"
        @mouseleave="stopDrag"
      >
        <g :transform="transformValue">
          <g v-for="location in usaMap.locations" :key="location.id">
            <path
              :d="location.path"
              :fill="hoveredState === location.id ? '#fbbf24' : getStateColor(location.id)"
              :stroke="hoveredState === location.id ? '#f59e0b' : '#333'"
              :stroke-width="hoveredState === location.id ? 2 : 0.5"
              class="transition-all duration-200 cursor-pointer"
              @mouseenter="onStateHover(location.id, $event)"
              @mouseleave="onStateLeave"
            />
          </g>
        </g>
      </svg>
      
      <!-- Tooltip -->
      <div 
        v-if="hoveredState" 
        class="absolute bg-gray-900 text-white px-4 py-2 rounded-lg shadow-xl text-sm pointer-events-none z-50 border border-yellow-500"
        :style="{ left: tooltipX + 'px', top: tooltipY + 'px', transform: 'translate(-50%, -120%)' }"
      >
        <div class="font-bold text-yellow-400">{{ getStateName(hoveredState) }}</div>
        <div class="text-gray-200">{{ getStateOrders(hoveredState) }} orders • {{ getStatePercentage(hoveredState) }}%</div>
      </div>
    </div>

    <!-- Legend -->
    <div class="mt-2 sm:mt-4 flex flex-wrap justify-start gap-2 sm:gap-4 text-xs">
      <div class="flex items-center gap-1.5">
        <div class="w-3 h-3 sm:w-4 sm:h-4 rounded-sm bg-[#004d99] border border-gray-300"></div>
        <span class="text-gray-700">High</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-3 h-3 sm:w-4 sm:h-4 rounded-sm bg-[#1a9cff] border border-gray-300"></div>
        <span class="text-gray-700">Medium</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-3 h-3 sm:w-4 sm:h-4 rounded-sm bg-[#8dceff] border border-gray-300"></div>
        <span class="text-gray-700">Low</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-3 h-3 sm:w-4 sm:h-4 rounded-sm bg-[#d4ebff] border border-gray-300"></div>
        <span class="text-gray-700">Minimal</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-3 h-3 sm:w-4 sm:h-4 rounded-sm bg-[#E5E5E5] border border-gray-300"></div>
        <span class="text-gray-700">No Data</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import usa from '@svg-maps/usa';
import { computed, ref } from 'vue';

const props = defineProps({
  salesData: { type: Object, default: () => ({}) }
});

const hoveredState = ref(null);
const usaMap = usa;
const tooltipX = ref(0);
const tooltipY = ref(0);

// --- Zoom + Pan state ---
const scale = ref(1);
const offsetX = ref(0);
const offsetY = ref(0);
const dragging = ref(false);
const lastX = ref(0);
const lastY = ref(0);

const transformValue = computed(() => `translate(${offsetX.value}, ${offsetY.value}) scale(${scale.value})`);

const onWheel = (event) => {
  const zoomSpeed = 0.1;
  const direction = event.deltaY < 0 ? 1 : -1;
  scale.value = Math.min(5, Math.max(0.5, scale.value + direction * zoomSpeed));
};

const startDrag = (e) => {
  dragging.value = true;
  lastX.value = e.clientX;
  lastY.value = e.clientY;
};

const onDrag = (e) => {
  if (!dragging.value) return;
  offsetX.value += (e.clientX - lastX.value) / scale.value;
  offsetY.value += (e.clientY - lastY.value) / scale.value;
  lastX.value = e.clientX;
  lastY.value = e.clientY;
};

const stopDrag = () => {
  dragging.value = false;
};

const onStateHover = (stateId, event) => {
  hoveredState.value = stateId;
  const rect = event.target.getBoundingClientRect();
  const container = event.target.closest('.relative');
  if (container) {
    const containerRect = container.getBoundingClientRect();
    tooltipX.value = rect.left + rect.width / 2 - containerRect.left;
    tooltipY.value = rect.top - containerRect.top;
  }
};

const onStateLeave = () => {
  hoveredState.value = null;
};

// --- Your existing state data code ---
const stateNames = {
  'AL': 'ALABAMA', 'AK': 'ALASKA', 'AZ': 'ARIZONA', 'AR': 'ARKANSAS', 'CA': 'CALIFORNIA',
  'CO': 'COLORADO', 'CT': 'CONNECTICUT', 'DE': 'DELAWARE', 'FL': 'FLORIDA', 'GA': 'GEORGIA',
  'HI': 'HAWAII', 'ID': 'IDAHO', 'IL': 'ILLINOIS', 'IN': 'INDIANA', 'IA': 'IOWA',
  'KS': 'KANSAS', 'KY': 'KENTUCKY', 'LA': 'LOUISIANA', 'ME': 'MAINE', 'MD': 'MARYLAND',
  'MA': 'MASSACHUSETTS', 'MI': 'MICHIGAN', 'MN': 'MINNESOTA', 'MS': 'MISSISSIPPI', 'MO': 'MISSOURI',
  'MT': 'MONTANA', 'NE': 'NEBRASKA', 'NV': 'NEVADA', 'NH': 'NEW HAMPSHIRE', 'NJ': 'NEW JERSEY',
  'NM': 'NEW MEXICO', 'NY': 'NEW YORK', 'NC': 'NORTH CAROLINA', 'ND': 'NORTH DAKOTA', 'OH': 'OHIO',
  'OK': 'OKLAHOMA', 'OR': 'OREGON', 'PA': 'PENNSYLVANIA', 'RI': 'RHODE ISLAND', 'SC': 'SOUTH CAROLINA',
  'SD': 'SOUTH DAKOTA', 'TN': 'TENNESSEE', 'TX': 'TEXAS', 'UT': 'UTAH', 'VT': 'VERMONT',
  'VA': 'VIRGINIA', 'WA': 'WASHINGTON', 'WV': 'WEST VIRGINIA', 'WI': 'WISCONSIN', 'WY': 'WYOMING'
};

const totalOrders = computed(() => Object.values(props.salesData).reduce((sum, count) => sum + count, 0));
const getStateOrders = (code) => props.salesData[code?.toUpperCase()] || 0;
const getStatePercentage = (code) => totalOrders.value === 0 ? '0.0' : ((getStateOrders(code) / totalOrders.value) * 100).toFixed(1);
const getStateName = (code) => stateNames[code?.toUpperCase()] || code?.toUpperCase();

const getStateColor = (code) => {
  const orders = getStateOrders(code);
  if (orders === 0) return '#E5E5E5';
  const maxOrders = Math.max(...Object.values(props.salesData), 1);
  const intensity = orders / maxOrders;
  if (intensity >= 0.80) return '#004d99';
  if (intensity >= 0.50) return '#1a9cff';
  if (intensity >= 0.20) return '#8dceff';
  return '#d4ebff';
};

const getStateTooltip = (code) => {
  const orders = getStateOrders(code);
  const name = stateNames[code?.toUpperCase()] || code?.toUpperCase();
  if (orders === 0) return `${name}: No orders`;
  const percentage = getStatePercentage(code);
  return `${name}: ${orders} orders (${percentage}%)`;
};
</script>

<style scoped>
svg path {
  transition: all 0.2s ease;
}
svg:active {
  cursor: grabbing;
}
</style>
