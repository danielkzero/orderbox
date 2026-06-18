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
            if (! this.$refs.trigger) return;

            const rect = this.$refs.trigger.getBoundingClientRect();
            const gap = 8;
            let left = rect.left + (rect.width / 2);
            let top = rect.top - gap;
            let transform = 'translate(-50%, -100%)';

            if (this.position === 'bottom') {
                top = rect.bottom + gap;
                transform = 'translate(-50%, 0)';
            } else if (this.position === 'left') {
                left = rect.left - gap;
                top = rect.top + (rect.height / 2);
                transform = 'translate(-100%, -50%)';
            } else if (this.position === 'right') {
                left = rect.right + gap;
                top = rect.top + (rect.height / 2);
                transform = 'translate(0, -50%)';
            }

            this.style = `left: ${left}px; top: ${top}px; transform: ${transform};`;
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
