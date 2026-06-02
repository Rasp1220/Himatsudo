<script setup lang="ts">
import { RouterView, useRoute } from 'vue-router'
import AppHeader from './AppHeader.vue'
import AppSidebar from './AppSidebar.vue'

const route = useRoute()
</script>

<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden">
    <AppSidebar />
    <div class="flex flex-col flex-1 overflow-hidden">
      <AppHeader />
      <main
        class="flex-1 overflow-hidden"
        :class="route.meta.fullscreen ? 'overflow-hidden' : 'overflow-y-auto p-6'"
      >
        <RouterView />
      </main>
    </div>
  </div>
</template>
