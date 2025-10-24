<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform translate-x-full opacity-0"
        enter-to-class="transform translate-x-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="transform translate-x-0 opacity-100"
        leave-to-class="transform translate-x-full opacity-0"
    >
        <div
            v-if="show"
            class="fixed top-4 right-4 z-50 flex items-center gap-3 rounded-lg px-4 py-3 shadow-lg"
            :class="{
                'bg-green-50 text-green-800 ring-1 ring-green-600/20': type === 'success',
                'bg-red-50 text-red-800 ring-1 ring-red-600/20': type === 'error',
                'bg-blue-50 text-blue-800 ring-1 ring-blue-600/20': type === 'info'
            }"
        >
            <!-- Icône -->
            <div class="flex-shrink-0">
                <svg v-if="type === 'success'" class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else-if="type === 'error'" class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>

            <!-- Message -->
            <p class="text-sm font-medium">{{ message }}</p>

            <!-- Bouton fermer -->
            <button
                @click="close"
                class="ml-4 flex-shrink-0 rounded-lg p-1 hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                :class="{
                    'focus:ring-green-600': type === 'success',
                    'focus:ring-red-600': type === 'error',
                    'focus:ring-blue-600': type === 'info'
                }"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    message: {
        type: String,
        required: true
    },
    type: {
        type: String,
        default: 'success',
        validator: (value) => ['success', 'error', 'info'].includes(value)
    },
    duration: {
        type: Number,
        default: 3000
    }
});

const show = ref(false);
let timeout = null;

const close = () => {
    show.value = false;
    if (timeout) {
        clearTimeout(timeout);
    }
};

const startTimer = () => {
    if (timeout) {
        clearTimeout(timeout);
    }

    timeout = setTimeout(() => {
        close();
    }, props.duration);
};

watch(() => props.message, (newMessage) => {
    if (newMessage) {
        show.value = true;
        startTimer();
    }
});

onMounted(() => {
    if (props.message) {
        show.value = true;
        startTimer();
    }
});
</script>
