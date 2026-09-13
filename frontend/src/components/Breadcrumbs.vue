<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { ChevronRightIcon, HomeIcon } from '@heroicons/vue/20/solid'

const route = useRoute()

const breadcrumbs = computed(() => {
  const matched = route.matched.filter(
    (record) => record.meta && record.meta.title
  )

  return matched.map((record) => ({
    name: record.meta.title as string,
    path: record.path,
  }))
})
</script>

<template>
  <nav v-if="breadcrumbs.length > 0" class="flex mb-4" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
      <li>
        <router-link to="/" class="text-gray-400 hover:text-gray-500">
          <HomeIcon class="h-5 w-5 flex-shrink-0" />
          <span class="sr-only">Home</span>
        </router-link>
      </li>
      <li v-for="(breadcrumb, index) in breadcrumbs" :key="breadcrumb.path">
        <div class="flex items-center">
          <ChevronRightIcon class="h-5 w-5 flex-shrink-0 text-gray-400" />
          <router-link
            v-if="index < breadcrumbs.length - 1"
            :to="breadcrumb.path"
            class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
          >
            {{ breadcrumb.name }}
          </router-link>
          <span
            v-else
            class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300"
          >
            {{ breadcrumb.name }}
          </span>
        </div>
      </li>
    </ol>
  </nav>
</template>
