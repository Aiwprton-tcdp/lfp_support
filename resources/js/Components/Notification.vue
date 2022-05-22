<style>
.slide-fade-enter-active {
  transition: all .3s ease;
}
.slide-fade-leave-active {
  transition: all .8s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}
.slide-fade-enter, .slide-fade-leave-to {
  transform: translateX(150px);
  opacity: 0;
}
</style>

<template>
  <transition name="slide-fade">
    <div v-if="toast && visible" class="absolute fixed inset-0 flex items-start justify-end p-6 px-4 py-6 pointer-events-none" style="z-index: 9999999999999;">
      <div class="w-full max-w-sm">
        <div class="flex w-full max-w-sm mx-auto mt-4 overflow-hidden bg-white rounded-lg">
          <div class="flex items-center justify-center w-12" v-bind:class="toast.status == 'success' ? 'bg-green-500' : 'bg-red-500'">
            <svg class="w-6 h-6 text-white fill-current" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
              <path d="M20 3.33331C10.8 3.33331 3.33337 10.8 3.33337 20C3.33337 29.2 10.8 36.6666 20 36.6666C29.2 36.6666 36.6667 29.2 36.6667 20C36.6667 10.8 29.2 3.33331 20 3.33331ZM16.6667 28.3333L8.33337 20L10.6834 17.65L16.6667 23.6166L29.3167 10.9666L31.6667 13.3333L16.6667 28.3333Z" />
            </svg>
          </div>

          <div class="px-4 py-2 -mx-3">
            <div class="mx-3">
              <span class="font-semibold" v-bind:class="toast.status == 'success' ? 'text-green-500' : 'text-red-500'">
                {{ toast.status == 'success' ? 'Успех' : 'Ошибка' }}
              </span>
              <p class="text-sm text-gray-600">{{ toast.message }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  props: {
    toast: Object
  },
  data() {
    return {
      visible: false
    }
  },
  watch: {
    toast: {
      deep: true,
      handler() {
        this.visible = true;

        setTimeout(() => {
          this.visible = false;
        }, 2000);
      }
    }
  }
}
</script>