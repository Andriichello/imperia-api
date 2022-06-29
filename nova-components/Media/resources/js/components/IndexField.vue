<template>
    <div v-if="field.value && field.value.length" style="position: relative; width: 40px; height: 40px; border-radius: 4px; background-image: linear-gradient(45deg, rgba(241, 245, 249, 0.5), rgba(100, 116, 139, 0.4))">
        <img :alt="field.value[0].title" :src="field.value[0].url"/>
        <div v-if="field.value.length > 1" class="bg-primary-500hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold text-sm"
             style="position: absolute; top: 0; right: 0; padding: 2px; border-radius: 2px; border-top-right-radius: 4px;">
            <span style="font-size: 8px; line-height: 8px">{{ field.value.length }}</span>
        </div>
    </div>
</template>

<script>
export default {
    props: ['resourceName', 'field'],
}
</script>
