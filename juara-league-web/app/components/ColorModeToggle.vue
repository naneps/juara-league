<script setup lang="ts">
const colorMode = useColorMode()
const isDark = computed({
  get () {
    return colorMode.value === 'dark'
  },
  set () {
    colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
  }
})
</script>

<template>
  <ClientOnly>
    <UButton
      :icon="isDark ? 'i-lucide-moon' : 'i-lucide-sun'"
      color="neutral"
      variant="ghost"
      @click="isDark = !isDark"
      class="rounded-xl hover:bg-neutral-100 dark:hover:bg-white/5 transition-all"
    />
    <template #placeholder>
      <div class="size-8" />
    </template>
  </ClientOnly>
</template>
