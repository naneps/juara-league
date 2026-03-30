<template>
  <div class="relative w-16 shrink-0" :style="{ height: `${height}px` }">
    <svg 
      class="absolute inset-0 w-full h-full overflow-visible" 
      :viewBox="`0 0 64 ${height}`" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg"
    >
      <defs>
        <filter :id="`glow-bloom-${id}`" x="-100%" y="-100%" width="300%" height="300%">
          <feGaussianBlur in="SourceGraphic" stdDeviation="2.5" result="blur1" />
          <feGaussianBlur in="SourceGraphic" stdDeviation="5" result="blur2" />
          <feMerge>
            <feMergeNode in="blur1" />
            <feMergeNode in="blur2" />
            <feMergeNode in="SourceGraphic" />
          </feMerge>
        </filter>

        <!-- Mask for the flowing effect -->
        <mask :id="`mask-${id}`">
          <path 
            :d="fullPath" 
            stroke="white" 
            stroke-width="3" 
            fill="none" 
            stroke-dasharray="100 400"
            class="animate-liquid"
          />
        </mask>

        <linearGradient :id="`grad-${id}`" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" :stop-color="glowColor" stop-opacity="0.2" />
          <stop offset="50%" :stop-color="glowColor" stop-opacity="1" />
          <stop offset="100%" :stop-color="glowColor" stop-opacity="0.2" />
        </linearGradient>
      </defs>

      <!-- Base static path (Very dim) -->
      <path :d="path1" class="stroke-white/5" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round" fill="none" />
      <path :d="path2" class="stroke-white/5" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round" fill="none" />

      <!-- Subtle pulse path (Entire line) -->
      <g class="opacity-10 animate-pulse-slow">
        <path :d="path1" :stroke="glowColor" stroke-width="2" stroke-linejoin="round" fill="none" />
        <path :d="path2" :stroke="glowColor" stroke-width="2" stroke-linejoin="round" fill="none" />
      </g>

      <!-- Flowing Liquid "Comet" -->
      <g :filter="`url(#glow-bloom-${id})`">
        <path 
          :d="path1" 
          :stroke="glowColor" 
          stroke-width="2.5" 
          stroke-dasharray="80 320"
          stroke-linecap="round"
          fill="none"
          class="animate-liquid"
        />
        <path 
          :d="path2" 
          :stroke="glowColor" 
          stroke-width="2.5" 
          stroke-dasharray="80 320"
          stroke-linecap="round"
          fill="none"
          class="animate-liquid-delayed"
        />
      </g>

      <!-- Top sharp highlight -->
      <path 
        :d="path1" 
        stroke="white" 
        stroke-width="1"
        stroke-dasharray="20 380"
        stroke-linecap="round"
        fill="none"
        class="animate-liquid opacity-50"
      />
      <path 
        :d="path2" 
        stroke="white" 
        stroke-width="1"
        stroke-dasharray="20 380"
        stroke-linecap="round"
        fill="none"
        class="animate-liquid-delayed opacity-50"
      />
    </svg>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  height: number
  glowColor?: string
}>(), {
  glowColor: '#60a5fa' // blue-400 for a more "neon" look
})

const id = Math.random().toString(36).substring(2, 9)

const w = 64
const h = props.height
const midX = w / 2
const midY = h / 2
const r = 16 // radius

const path1 = `
  M 0 2 
  H ${midX - r}
  Q ${midX} 2 ${midX} ${r + 2}
  V ${midY - r}
  Q ${midX} ${midY} ${midX + r} ${midY}
  H ${w}
`.trim()

const path2 = `
  M 0 ${h - 2}
  H ${midX - r}
  Q ${midX} ${h - 2} ${midX} ${h - r - 2}
  V ${midY + r}
  Q ${midX} ${midY} ${midX + r} ${midY}
  H ${w}
`.trim()

const fullPath = path1 + " " + path2
</script>

<style scoped>
.animate-liquid {
  animation: liquid 2.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}
.animate-liquid-delayed {
  animation: liquid 2.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
  animation-delay: 1.25s;
}

.animate-pulse-slow {
  animation: pulse-slow 4s ease-in-out infinite;
}

@keyframes liquid {
  0% {
    stroke-dashoffset: 400;
  }
  100% {
    stroke-dashoffset: 0;
  }
}

@keyframes pulse-slow {
  0%, 100% { opacity: 0.05; }
  50% { opacity: 0.15; }
}
</style>
