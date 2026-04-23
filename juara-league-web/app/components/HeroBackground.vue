<template>
  <div ref="container" class="fixed inset-0 -z-10 overflow-hidden bg-slate-50 dark:bg-slate-950 transition-colors duration-700">
    <canvas ref="canvas" class="w-full h-full opacity-60 dark:opacity-80 transition-opacity duration-1000" />
    
    <!-- Subtle gradient to keep content readable -->
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-white/50 dark:via-transparent to-slate-50 dark:to-slate-950 pointer-events-none"></div>
  </div>
</template>

<script setup lang="ts">
import * as THREE from 'three'

const colorMode = useColorMode()
const container = ref<HTMLElement | null>(null)
const canvas = ref<HTMLCanvasElement | null>(null)

let scene: THREE.Scene
let camera: THREE.PerspectiveCamera
let renderer: THREE.WebGLRenderer
let group: THREE.Group
let trophies: THREE.Mesh[] = []
let particles: THREE.Points
let lines: THREE.LineSegments
let animationId: number

const mouse = new THREE.Vector2(0, 0)
const targetRotation = new THREE.Vector2(0, 0)

const init = () => {
  if (!canvas.value) {
    console.error('[HeroBackground] Canvas element not found')
    return
  }
  
  console.log('[HeroBackground] Initializing Three.js Arena...')

  scene = new THREE.Scene()
  
  camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 1, 3000)
  camera.position.z = 1000

  renderer = new THREE.WebGLRenderer({
    canvas: canvas.value,
    alpha: true,
    antialias: true
  })
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.setSize(window.innerWidth, window.innerHeight)

  group = new THREE.Group()
  scene.add(group)

  // Colors from app.config.ts (Amber & Slate)
  const primaryColor = new THREE.Color(0xf59e0b) // Amber-500
  const secondaryColor = new THREE.Color(colorMode.value === 'dark' ? 0x475569 : 0xcbd5e1) // Slate-600 or 300

  // 1. "Trophy" Particles (Octahedrons)
  const trophyGeo = new THREE.OctahedronGeometry(15, 0)
  const trophyCount = 30
  
  for (let i = 0; i < trophyCount; i++) {
    const mat = new THREE.MeshBasicMaterial({
      color: primaryColor,
      wireframe: true,
      transparent: true,
      opacity: 0.4
    })
    const trophy = new THREE.Mesh(trophyGeo, mat)
    trophy.position.set(
      (Math.random() - 0.5) * 2000,
      (Math.random() - 0.5) * 2000,
      (Math.random() - 0.5) * 1000
    )
    trophy.userData = {
      rot: Math.random() * 0.02,
      vel: Math.random() * 0.5 + 0.1
    }
    group.add(trophy)
    trophies.push(trophy)
  }

  // 2. Neural Bracket Network
  const pointCount = 80
  const positions = new Float32Array(pointCount * 3)
  const velocities = new Float32Array(pointCount * 3)
  
  for (let i = 0; i < pointCount; i++) {
    positions[i * 3] = (Math.random() - 0.5) * 2000
    positions[i * 3 + 1] = (Math.random() - 0.5) * 2000
    positions[i * 3 + 2] = (Math.random() - 0.5) * 1000
    
    velocities[i * 3] = (Math.random() - 0.5) * 0.5
    velocities[i * 3 + 1] = (Math.random() - 0.5) * 0.5
    velocities[i * 3 + 2] = (Math.random() - 0.5) * 0.5
  }

  const pointsGeo = new THREE.BufferGeometry()
  pointsGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3))
  
  const pointsMat = new THREE.PointsMaterial({
    size: 5,
    color: primaryColor,
    transparent: true,
    opacity: colorMode.value === 'dark' ? 0.6 : 0.8
  })
  
  particles = new THREE.Points(pointsGeo, pointsMat)
  group.add(particles)

  // Lines for network
  const linesGeo = new THREE.BufferGeometry()
  const linesMat = new THREE.LineBasicMaterial({
    color: primaryColor,
    transparent: true,
    opacity: 0.15
  })
  lines = new THREE.LineSegments(linesGeo, linesMat)
  group.add(lines)

  animate()
}

const animate = () => {
  animationId = requestAnimationFrame(animate)

  // Handle Mouse Tilt
  targetRotation.x = mouse.y * 0.15
  targetRotation.y = mouse.x * 0.15
  group.rotation.x += (targetRotation.x - group.rotation.x) * 0.05
  group.rotation.y += (targetRotation.y - group.rotation.y) * 0.05

  // Animate Trophies
  trophies.forEach(t => {
    t.rotation.x += t.userData.rot
    t.rotation.z += t.userData.rot
    t.position.y += t.userData.vel
    if (t.position.y > 1000) t.position.y = -1000
  })

  // Update Network
  const posAttr = particles.geometry.getAttribute('position') as THREE.BufferAttribute
  const linePositions = []
  const coords = posAttr.array as Float32Array

  for (let i = 0; i < pointCount; i++) {
    coords[i * 3] += (Math.random() - 0.5) * 0.5
    coords[i * 3 + 1] += (Math.random() - 0.5) * 0.5
    
    for (let j = i + 1; j < pointCount; j++) {
      const dx = coords[i * 3] - coords[j * 3]
      const dy = coords[i * 3 + 1] - coords[j * 3 + 1]
      const dist = Math.sqrt(dx * dx + dy * dy)
      
      if (dist < 300) {
        linePositions.push(
          coords[i * 3], coords[i * 3 + 1], coords[i * 3 + 2],
          coords[j * 3], coords[j * 3 + 1], coords[j * 3 + 2]
        )
      }
    }
  }
  posAttr.needsUpdate = true
  lines.geometry.setAttribute('position', new THREE.Float32BufferAttribute(linePositions, 3))

  renderer.render(scene, camera)
}

const pointCount = 80 // Defined outside for access in animate

const onPointerMove = (e: PointerEvent) => {
  mouse.x = (e.clientX / window.innerWidth) * 2 - 1
  mouse.y = -(e.clientY / window.innerHeight) * 2 + 1
}

const handleResize = () => {
  if (!camera || !renderer) return
  camera.aspect = window.innerWidth / window.innerHeight
  camera.updateProjectionMatrix()
  renderer.setSize(window.innerWidth, window.innerHeight)
}

onMounted(() => {
  // Use nextTick to ensure canvas is ready
  nextTick(() => {
    init()
    window.addEventListener('resize', handleResize)
    window.addEventListener('pointermove', onPointerMove)
  })
})

onUnmounted(() => {
  cancelAnimationFrame(animationId)
  window.removeEventListener('resize', handleResize)
  window.removeEventListener('pointermove', onPointerMove)
  if (renderer) renderer.dispose()
})
</script>
