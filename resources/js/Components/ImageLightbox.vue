<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    images: {
        type: Object,
        default: () => ({}),
    },
    storagePrefix: {
        type: String,
        default: '/storage/',
    },
});

const lightboxOpen = ref(false);
const activeImage = ref(null);
const activeLabel = ref('');

const openLightbox = (url, label) => {
    activeImage.value = url;
    activeLabel.value = label;
    lightboxOpen.value = true;
};

const closeLightbox = () => {
    lightboxOpen.value = false;
    activeImage.value = null;
    activeLabel.value = '';
};

const handleKeydown = (e) => {
    if (e.key === 'Escape') closeLightbox();
};

watch(lightboxOpen, (open) => {
    if (open) {
        document.addEventListener('keydown', handleKeydown);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', handleKeydown);
        document.body.style.overflow = '';
    }
});

const handleImageError = (event) => {
    event.target.closest('.image-thumb')?.classList.add('hidden');
};
</script>

<template>
    <div>
        <!-- Thumbnails Grid -->
        <div class="grid grid-cols-2 gap-3">
            <template v-for="(url, type) in images" :key="type">
                <button
                    type="button"
                    class="image-thumb group relative aspect-square rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-600 hover:ring-2 hover:ring-blue-500 transition focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @click="openLightbox(`${storagePrefix}${url}`, type)"
                >
                    <img
                        :src="`${storagePrefix}${url}`"
                        :alt="`${type} End Image`"
                        class="w-full h-full object-cover group-hover:scale-105 transition"
                        @error="handleImageError"
                    >
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
                        <div class="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded capitalize">
                            {{ type }}
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-white opacity-0 group-hover:opacity-100 transition drop-shadow-lg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                        </svg>
                    </div>
                </button>
            </template>
        </div>

        <!-- Lightbox Overlay -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="lightboxOpen"
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4"
                    @click.self="closeLightbox"
                >
                    <!-- Close button -->
                    <button
                        @click="closeLightbox"
                        class="absolute top-4 right-4 z-10 p-2 bg-white/10 hover:bg-white/20 rounded-full text-white transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Label -->
                    <div class="absolute top-4 left-4 z-10 bg-white/10 text-white text-sm font-bold px-4 py-2 rounded-full capitalize backdrop-blur-sm">
                        {{ activeLabel }} End
                    </div>

                    <!-- Image -->
                    <Transition
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="opacity-0 scale-90"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-200"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-90"
                    >
                        <img
                            v-if="lightboxOpen"
                            :src="activeImage"
                            :alt="activeLabel"
                            class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl"
                        >
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
