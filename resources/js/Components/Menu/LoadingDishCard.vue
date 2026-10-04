<template>
  <div class="w-full flex items-start gap-3 px-2 py-3.5">
    <div class="flex-1 min-w-0 flex flex-col">
      <!-- Title -->
      <p class="skeleton w-[60%] h-[18px] my-[3px] rounded-sm bg-base-content/10"/>

      <!-- Price and weight -->
      <div class="mt-0.5 h-7 flex items-center gap-2">
        <p class="skeleton w-[64px] h-5 rounded-sm bg-primary/20"/>
        <p class="skeleton w-[48px] h-4 rounded-sm bg-base-content/10"/>
      </div>

      <!-- Description -->
      <div class="mt-1.5 py-[3.5px] flex flex-col gap-[7px]">
        <p class="skeleton w-[95%] h-[15px] rounded-sm bg-base-content/10"/>
        <p class="skeleton w-[95%] h-[15px] rounded-sm bg-base-content/10"/>
        <p class="skeleton w-[70%] h-[15px] rounded-sm bg-base-content/10"/>
      </div>
    </div>

    <!-- Photo (matches DishCard) -->
    <div class="size-28 shrink-0 rounded-lg border border-[#E5E5E5]"/>
  </div>
</template>
