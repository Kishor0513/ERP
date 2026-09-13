<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCatalogStore } from '@/stores/catalog'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'

const route = useRoute()
const router = useRouter()
const catalogStore = useCatalogStore()

const product = ref<any>(null)
const activeTab = ref('info')
const showVariantModal = ref(false)

const variantForm = ref({
  name: '',
  sku: '',
  price: 0,
  stock_quantity: 0,
  attributes: {},
})

onMounted(async () => {
  const id = Number(route.params.id)
  product.value = await catalogStore.fetchProduct(id)
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value)
}

const handleAddVariant = async () => {
  await catalogStore.addVariant(product.value.id, variantForm.value)
  product.value = await catalogStore.fetchProduct(product.value.id)
  showVariantModal.value = false
}
</script>

<template>
  <div class="space-y-6" v-if="product">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <button
          @click="router.back()"
          class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-2"
        >
          ← Back to Products
        </button>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ product.name }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">SKU: {{ product.sku }}</p>
      </div>
      <StatusBadge :status="product.status" />
    </div>

    <div class="border-b border-gray-200 dark:border-slate-700">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in ['info', 'variants', 'images']"
          :key="tab"
          @click="activeTab = tab"
          :class="[
            activeTab === tab
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm capitalize',
          ]"
        >
          {{ tab }}
        </button>
      </nav>
    </div>

    <div v-if="activeTab === 'info'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <div class="grid grid-cols-2 gap-6">
        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Price</h3>
          <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(product.price) }}</p>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Cost Price</h3>
          <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(product.cost_price) }}</p>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Stock Quantity</h3>
          <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ product.stock_quantity }}</p>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Category</h3>
          <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ product.category?.name || '-' }}</p>
        </div>
      </div>
      <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Description</h3>
        <p class="mt-2 text-gray-900 dark:text-white">{{ product.description || 'No description' }}</p>
      </div>
    </div>

    <div v-if="activeTab === 'variants'" class="space-y-4">
      <div class="flex justify-end">
        <button @click="showVariantModal = true" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Variant</button>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
          <thead class="bg-gray-50 dark:bg-slate-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">SKU</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Stock</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
            <tr v-if="product.variants.length === 0">
              <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No variants</td>
            </tr>
            <tr v-for="variant in product.variants" :key="variant.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ variant.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ variant.sku }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatCurrency(variant.price) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ variant.stock_quantity }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="activeTab === 'images'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <div v-if="product.images.length === 0" class="text-center py-12">
        <p class="text-gray-500 dark:text-slate-400">No images uploaded</p>
      </div>
      <div v-else class="grid grid-cols-4 gap-4">
        <div v-for="image in product.images" :key="image.id" class="relative">
          <img :src="image.path" :alt="image.alt_text" class="w-full h-32 object-cover rounded-lg" />
        </div>
      </div>
    </div>

    <Modal :open="showVariantModal" title="Add Variant" @close="showVariantModal = false">
      <form @submit.prevent="handleAddVariant" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Name</label>
          <input v-model="variantForm.name" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">SKU</label>
          <input v-model="variantForm.sku" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Price</label>
          <input v-model.number="variantForm.price" type="number" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Stock Quantity</label>
          <input v-model.number="variantForm.stock_quantity" type="number" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
        </div>
      </form>
      <template #footer>
        <button @click="showVariantModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleAddVariant" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Variant</button>
      </template>
    </Modal>
  </div>
</template>
