<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})
const state = reactive<{ [key: string]: boolean }>({
  email: true,
  desktop: false,
  product_updates: true,
  weekly_digest: false,
  important_updates: true
})

const { t } = useI18n()

const sections = computed(() => [{
  title: t('settings.notifications.channels_title'),
  description: t('settings.notifications.channels_desc'),
  fields: [{
    name: 'email',
    label: t('settings.notifications.email_label'),
    description: t('settings.notifications.email_desc')
  }, {
    name: 'desktop',
    label: t('settings.notifications.desktop_label'),
    description: t('settings.notifications.desktop_desc')
  }]
}, {
  title: t('settings.notifications.account_updates_title'),
  description: t('settings.notifications.account_updates_desc'),
  fields: [{
    name: 'weekly_digest',
    label: t('settings.notifications.weekly_digest_label'),
    description: t('settings.notifications.weekly_digest_desc')
  }, {
    name: 'product_updates',
    label: t('settings.notifications.product_updates_label'),
    description: t('settings.notifications.product_updates_desc')
  }, {
    name: 'important_updates',
    label: t('settings.notifications.important_updates_label'),
    description: t('settings.notifications.important_updates_desc')
  }]
}])

async function onChange() {
  // Do something with data
  console.log(state)
}
</script>

<template>
  <div v-for="(section, index) in sections" :key="index">
    <UPageCard
      :title="section.title"
      :description="section.description"
      variant="naked"
      class="mb-4"
    />

    <UPageCard variant="subtle" :ui="{ container: 'divide-y divide-default' }">
      <UFormField
        v-for="field in section.fields"
        :key="field.name"
        :name="field.name"
        :label="field.label"
        :description="field.description"
        class="flex items-center justify-between not-last:pb-4 gap-2"
      >
        <USwitch
          v-model="state[field.name]"
          @update:model-value="onChange"
        />
      </UFormField>
    </UPageCard>
  </div>
</template>
