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
    <!-- Background Stardust Fixed -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden bg-white dark:bg-neutral-950 transition-colors duration-500">
       <div v-if="colorMode.value === 'dark'" class="stardust-container">
          <div class="star star-small"></div>
          <div class="star star-medium"></div>
          <div class="star star-large"></div>
       </div>
       <div class="absolute inset-0 bg-gradient-to-tr from-primary-500/5 dark:from-primary-950/20 via-transparent to-indigo-500/5 dark:to-indigo-950/20"></div>
    </div>

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
const colorMode = useColorMode()
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

.stardust-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.star {
  position: absolute;
  top: 0;
  left: 0;
  background: white;
  border-radius: 50%;
  opacity: 1;
}

.star-small {
  width: 1px;
  height: 1px;
  box-shadow: 
    10vw 15vh #fff, 
    20vw 35vh #fff, 
    5vw 80vh #fff, 
    50vw 10vh #fff, 
    60vw 60vh #fff, 
    90vw 90vh #fff, 
    80vw 20vh #fff,
    35vw 70vh #fff,
    45vw 25vh #fff,
    75vw 40vh #fff,
    15vw 95vh #fff,
    25vw 5vh #fff;
  animation: twinkle 4s infinite ease-in-out;
}

.star-medium {
  width: 2px;
  height: 2px;
  box-shadow: 
    15vw 25vh #fff, 
    40vw 45vh #fff, 
    10vw 70vh #fff, 
    65vw 15vh #fff, 
    85vw 55vh #fff, 
    25vw 85vh #fff,
    75vw 75vh #fff,
    30vw 10vh #fff;
  animation: twinkle 6s infinite ease-in-out 1s;
}

.star-large {
  width: 3px;
  height: 3px;
  box-shadow: 
    30vw 20vh rgba(255, 255, 255, 0.4), 
    70vw 60vh rgba(255, 255, 255, 0.4), 
    10vw 90vh rgba(255, 255, 255, 0.4), 
    90vw 10vh rgba(255, 255, 255, 0.4);
  animation: twinkle 8s infinite ease-in-out 2s;
}

@keyframes twinkle {
  0%, 100% { opacity: 0.2; transform: scale(0.8); }
  50% { opacity: 1; transform: scale(1.2); }
}
</style>
