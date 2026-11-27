<div {{ $attributes->class(['relative']) }}
     x-data="{
        overflowing: false, atStart: true, atEnd: false,
        dragging: false, moved: false, startX: 0, startLeft: 0,
        update() {
            const row = this.$refs.row;
            this.overflowing = row.scrollWidth > row.clientWidth + 2;
            this.atStart = row.scrollLeft <= 2;
            this.atEnd = row.scrollLeft + row.clientWidth >= row.scrollWidth - 2;
        },
        nudge(direction) {
            this.$refs.row.scrollBy({ left: direction * 240, behavior: 'smooth' });
        },
        down(event) {
            if (event.pointerType !== 'mouse' || event.button !== 0 || !this.overflowing) { return; }
            this.dragging = true;
            this.moved = false;
            this.startX = event.clientX;
            this.startLeft = this.$refs.row.scrollLeft;
        },
        move(event) {
            if (!this.dragging) { return; }
            const distance = event.clientX - this.startX;
            if (Math.abs(distance) > 4) { this.moved = true; }
            this.$refs.row.scrollLeft = this.startLeft - distance;
        },
        up() { this.dragging = false; },
        guardClick(event) {
            if (this.moved) { event.preventDefault(); event.stopPropagation(); this.moved = false; }
        },
        revealCurrent() {
            const row = this.$refs.row;
            const current = row.querySelector('[aria-current=page]');
            if (!current) { return; }
            const offset = current.getBoundingClientRect().left - row.getBoundingClientRect().left + row.scrollLeft;
            row.scrollLeft = Math.max(0, offset - (row.clientWidth - current.offsetWidth) / 2);
        }
     }"
     x-init="$nextTick(() => { revealCurrent(); update(); })"
     x-on:resize.window="update()"
     x-on:pointermove.window="move($event)"
     x-on:pointerup.window="up()">

    <button type="button" x-show="overflowing && !atStart" x-cloak @click="nudge(-1)" tabindex="-1" aria-hidden="true"
            class="scroll-arrow absolute left-1 top-1/2 z-10 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 6-6 6 6 6"/></svg>
    </button>

    <button type="button" x-show="overflowing && !atEnd" x-cloak @click="nudge(1)" tabindex="-1" aria-hidden="true"
            class="scroll-arrow absolute right-1 top-1/2 z-10 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
    </button>

    <div x-ref="row"
         x-on:scroll.passive="update()"
         x-on:pointerdown="down($event)"
         x-on:dragstart.prevent
         x-on:click.capture="guardClick($event)"
         :class="{
             'fade-r': overflowing && atStart,
             'fade-l': overflowing && atEnd,
             'fade-lr': overflowing && !atStart && !atEnd,
             'cursor-grab': overflowing && !dragging,
             'cursor-grabbing select-none': dragging
         }"
         class="no-scrollbar -mx-4 overflow-x-auto overscroll-x-contain px-4">
        {{ $slot }}
    </div>
</div>