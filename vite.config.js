import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/addon.js'],
            publicDirectory: 'resources/dist',
        }),
        vue(),
        statamicExternals(),
    ],
});

function statamicExternals() {
    const RESOLVED_ID = '\0vue-external';

    return {
        name: 'statamic-vue-external',
        enforce: 'pre',

        resolveId(id) {
            if (id === 'vue') return RESOLVED_ID;
            return null;
        },

        load(id) {
            if (id === RESOLVED_ID) {
                return `
                    const Vue = window.Vue;
                    export default Vue;
                    export const {
                        ref, computed, watch, reactive, toRef, toRefs,
                        onMounted, onBeforeUnmount, onUnmounted,
                        nextTick, provide, inject, defineProps, defineEmits,
                        h, createApp, defineComponent, useSlots, withDirectives,
                        Transition, TransitionGroup, Teleport, KeepAlive,
                        isRef, unref, shallowRef, triggerRef,
                        markRaw, toRaw, readonly, shallowReadonly,
                        watchEffect, defineExpose, useAttrs,
                        openBlock, createElementBlock, createElementVNode,
                        createVNode, createBlock, createCommentVNode,
                        Fragment, renderList, renderSlot, resolveComponent,
                        withCtx, normalizeClass, normalizeStyle, toDisplayString,
                        mergeProps, withModifiers, createTextVNode, resolveDirective,
                        pushScopeId, popScopeId, withScopeId, createStaticVNode,
                        vShow, vModelText, vModelCheckbox, vModelSelect
                    } = Vue;
                `;
            }
            return null;
        },
    };
}
