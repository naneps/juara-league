<script setup lang="ts">
const props = defineProps<{
  modelValue: number | string
  size?: any
  placeholder?: string
  icon?: string
}>()

const emit = defineEmits(['update:modelValue'])

const displayValue = ref('')

const formatNumber = (val: number | string) => {
  if (!val && val !== 0) return ''
  return new Intl.NumberFormat('id-ID').format(Number(val))
}

const parseNumber = (val: string) => {
  return val.replace(/\./g, '').replace(/[^0-9]/g, '')
}

watch(() => props.modelValue, (newVal) => {
  const formatted = formatNumber(newVal)
  if (formatted !== displayValue.value) {
    displayValue.value = formatted
  }
}, { immediate: true })

const onInput = (e: any) => {
  const raw = parseNumber(e.target.value)
  const num = raw === '' ? 0 : parseInt(raw)
  displayValue.value = formatNumber(num)
  emit('update:modelValue', num)
}
</script>

<template>
  <UInput
    v-model="displayValue"
    :size="size"
    :placeholder="placeholder"
    :icon="icon"
    @input="onInput"
  >
    <template #leading>
      <span class="text-neutral-400 text-xs font-bold">Rp</span>
    </template>
  </UInput>
</template>
