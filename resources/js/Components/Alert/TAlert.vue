<script setup>
import { inject, ref, watch, computed, useSlots } from 'vue'
import { alertConf } from '@/config'

const props = defineProps({
  id: { type: [String, Number], default: () => `alert-${Date.now()}` },
  title: { type: String, default: null },
  type: { type: String, default: null }, // success | warning | error | info
  design: { type: String, default: 'filled' }, // filled | outline | line
  color: { type: String, default: 'gray' },
  radius: { type: Number, default: 2 }, // 0–3
  closeable: { type: Boolean, default: false },
  timerStatus: { type: Boolean, default: false },
  timer: { type: Number, default: null },
})

const emit = defineEmits(['destroy'])
const slots = useSlots()
const appConf = inject('appConf', {})

const show = ref(true)
const countDown = ref(100)

/* 🔹 FINAL CONFIG */
const finalDesign = props.design ?? alertConf.design ?? appConf.design ?? 'filled'
const finalColor = props.type
  ? { success: 'green', warning: 'yellow', error: 'red', info: 'blue' }[props.type]
  : props.color

/* 🔹 CLASS MAP (STATIC) */
const alertClass = computed(() => {
  const base = 'flex gap-3 p-4 text-sm'
  const radiusMap = ['rounded-none', 'rounded-sm', 'rounded-md', 'rounded-lg']

  const styles = {
    filled: {
      yellow: 'bg-yellow-100 text-yellow-800 border border-yellow-300',
      red: 'bg-red-100 text-red-800 border border-red-300',
      green: 'bg-green-100 text-green-800 border border-green-300',
      blue: 'bg-blue-100 text-blue-800 border border-blue-300',
      gray: 'bg-gray-100 text-gray-800 border border-gray-300',
    },
    outline: {
      yellow: 'border border-yellow-400 text-yellow-600',
      red: 'border border-red-400 text-red-600',
      green: 'border border-green-400 text-green-600',
      blue: 'border border-blue-400 text-blue-600',
      gray: 'border border-gray-400 text-gray-600',
    },
    line: {
      yellow: 'border-l-4 border-yellow-400 text-yellow-700',
      red: 'border-l-4 border-red-400 text-red-700',
      green: 'border-l-4 border-green-400 text-green-700',
      blue: 'border-l-4 border-blue-400 text-blue-700',
      gray: 'border-l-4 border-gray-400 text-gray-700',
    },
  }

  return [
    base,
    radiusMap[props.radius] ?? 'rounded-md',
    styles[finalDesign]?.[finalColor] ?? styles.filled.gray,
  ]
})

/* 🔹 TIMER */
watch(
  () => props.timerStatus,
  (v) => {
    if (!v || !props.timer) return
    const interval = setInterval(() => (countDown.value -= 1), props.timer / 100)
    setTimeout(() => {
      clearInterval(interval)
      close()
    }, props.timer)
  }
)

const close = () => {
  show.value = false
  emit('destroy', props.id)
}

const hasSlot = (name) => !!slots[name]
</script>


<template>
  <transition name="alert">
    <div v-if="show" :class="alertClass">
      <!-- Line decoration -->
      <div v-if="finalDesign === 'line'" class="w-1 shrink-0"></div>

      <!-- Icon -->
      <div v-if="hasSlot('icon')" class="shrink-0">
        <slot name="icon" />
      </div>

      <!-- Content -->
      <div class="flex-1">
        <p v-if="title" class="font-semibold mb-1">{{ title }}</p>
        <slot />
      </div>

      <!-- Close -->
      <button
        v-if="closeable"
        @click="close"
        class="ml-auto text-current opacity-60 hover:opacity-100"
      >
        ✕
      </button>

      <!-- Countdown -->
      <div
        v-if="timerStatus"
        class="absolute bottom-0 left-0 h-1 bg-current opacity-30 transition-all"
        :style="{ width: countDown + '%' }"
      />
    </div>
  </transition>
</template>


<style scoped>
.alert-enter-active,
.alert-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.alert-enter-from,
.alert-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
