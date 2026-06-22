@props(['text', 'position' => 'top'])

<span
    class="inline-flex"
    x-data="{
        open: false,
        position: @js($position),
        style: '',
        repositionHandler: null,
        init() {
            this.repositionHandler = () => {
                if (this.open) this.updatePosition();
            };
            window.addEventListener('resize', this.repositionHandler);
            document.addEventListener('scroll', this.repositionHandler, true);
        },
        destroy() {
            window.removeEventListener('resize', this.repositionHandler);
            document.removeEventListener('scroll', this.repositionHandler, true);
        },
        updatePosition() {
            if (! this.$refs.trigger || ! this.$refs.tooltip) return;

            const rect = this.$refs.trigger.getBoundingClientRect();
            const tooltip = this.$refs.tooltip.getBoundingClientRect();
            const gap = 8;
            const viewportPadding = 8;
            let left = rect.left + ((rect.width - tooltip.width) / 2);
            let top = rect.top - tooltip.height - gap;

            if (this.position === 'bottom') {
                top = rect.bottom + gap;
            } else if (this.position === 'left') {
                left = rect.left - tooltip.width - gap;
                top = rect.top + ((rect.height - tooltip.height) / 2);
            } else if (this.position === 'right') {
                left = rect.right + gap;
                top = rect.top + ((rect.height - tooltip.height) / 2);
            } else if (top < viewportPadding) {
                top = rect.bottom + gap;
            }

            left = Math.min(
                Math.max(viewportPadding, left),
                window.innerWidth - tooltip.width - viewportPadding,
            );
            top = Math.min(
                Math.max(viewportPadding, top),
                window.innerHeight - tooltip.height - viewportPadding,
            );

            this.style = `left: ${Math.round(left)}px; top: ${Math.round(top)}px;`;
        },
        show() {
            this.open = true;
            this.$nextTick(() => this.updatePosition());
        },
        hide() {
            this.open = false;
        },
    }"
>
    <span
        x-ref="trigger"
        class="inline-flex"
        @mouseenter="show()"
        @mouseleave="hide()"
        @focusin="show()"
        @focusout="hide()"
    >
        {{ $slot }}
    </span>

    <template x-teleport="body">
        <span
            x-ref="tooltip"
            x-show="open"
            x-cloak
            x-transition.opacity
            :style="style"
            class="pointer-events-none fixed z-[100002] max-w-xs whitespace-normal rounded-lg bg-gray-900 px-2.5 py-1.5 text-center text-xs font-medium leading-5 text-white shadow-tooltip dark:bg-gray-700"
            role="tooltip"
        >
            {{ $text }}
        </span>
    </template>
</span>
