<template>
  <div 
    ref="containerRef"
    class="relative w-full h-full min-h-[600px] overflow-hidden cursor-grab active:cursor-grabbing select-none touch-none"
    @wheel.prevent="onWheel"
    @pointerdown="onPointerDown"
    @pointermove="onPointerMove"
    @pointerup="onPointerUp"
    @pointerleave="onPointerUp"
  >
    <!-- Background Stardust Fixed (optional if handled by parent) -->
    <div class="absolute inset-0 pointer-events-none opacity-50"></div>

    <!-- Zoomable Content Wrapper -->
    <div 
      class="absolute origin-center will-change-transform transition-transform duration-75"
      :style="{ 
        transform: `translate(${offset.x}px, ${offset.y}px) scale(${scale})`
      }"
    >
      <slot />
    </div>

    <!-- Controls Overlay -->
    <div class="absolute bottom-8 right-8 flex flex-col gap-2 z-50">
      <button 
        @click="zoomIn"
        class="size-12 rounded-xl bg-neutral-900/80 backdrop-blur border border-white/10 flex items-center justify-center hover:bg-primary-500 hover:border-primary-400 transition-all shadow-2xl group"
      >
        <UIcon name="i-lucide-plus" class="size-6 text-white" />
      </button>
      <button 
        @click="zoomOut"
        class="size-12 rounded-xl bg-neutral-900/80 backdrop-blur border border-white/10 flex items-center justify-center hover:bg-primary-500 hover:border-primary-400 transition-all shadow-2xl group"
      >
        <UIcon name="i-lucide-minus" class="size-6 text-white" />
      </button>
      <button 
        @click="reset"
        class="size-12 rounded-xl bg-neutral-900/80 backdrop-blur border border-white/10 flex items-center justify-center hover:bg-primary-500 hover:border-primary-400 transition-all shadow-2xl group"
      >
        <UIcon name="i-lucide-maximize-2" class="size-6 text-white" />
      </button>
    </div>

    <!-- Scale Indicator -->
    <div class="absolute bottom-8 left-8 bg-neutral-900/50 backdrop-blur px-3 py-1.5 rounded-full border border-white/5 text-[10px] font-mono text-neutral-500 pointer-events-none uppercase tracking-widest">
      Zoom: {{ Math.round(scale * 100) }}%
    </div>
  </div>
</template>

<script setup lang="ts">
const containerRef = ref<HTMLElement | null>(null)
const scale = ref(1)
const offset = ref({ x: 0, y: 0 })
const isDragging = ref(false)
const lastPointerPosition = ref({ x: 0, y: 0 })

// Zoom Constants
const MIN_SCALE = 0.2
const MAX_SCALE = 3
const ZOOM_SPEED = 0.001

const onWheel = (e: WheelEvent) => {
  const delta = -e.deltaY * ZOOM_SPEED
  const newScale = Math.min(Math.max(scale.value + delta, MIN_SCALE), MAX_SCALE)
  scale.value = newScale
}

const onPointerDown = (e: PointerEvent) => {
  isDragging.value = true
  lastPointerPosition.value = { x: e.clientX, y: e.clientY }
  if (containerRef.value) {
    containerRef.value.setPointerCapture(e.pointerId)
  }
}

const onPointerMove = (e: PointerEvent) => {
  if (!isDragging.value) return
  
  const dx = e.clientX - lastPointerPosition.value.x
  const dy = e.clientY - lastPointerPosition.value.y
  
  offset.value.x += dx
  offset.value.y += dy
  
  lastPointerPosition.value = { x: e.clientX, y: e.clientY }
}

const onPointerUp = (e: PointerEvent) => {
  isDragging.value = false
  if (containerRef.value) {
    containerRef.value.releasePointerCapture(e.pointerId)
  }
}

const zoomIn = () => {
  scale.value = Math.min(scale.value + 0.2, MAX_SCALE)
}

const zoomOut = () => {
  scale.value = Math.max(scale.value - 0.2, MIN_SCALE)
}

const reset = () => {
  scale.value = 1
  offset.value = { x: 0, y: 0 }
}
</script>

<style scoped>
.cursor-grab {
  cursor: grab;
}
.cursor-grabbing {
  cursor: grabbing;
}
</style>
