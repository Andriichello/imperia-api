<template>
    <div
        class="flex flex-col md:flex-row -mx-6 px-6 py-2 md:py-0 space-y-2 md:space-y-0 border-t border-gray-100 dark:border-gray-700"
        dusk="{{ field.attribute }}">

        <div class="md:w-1/4 md:py-3">
            <h4 class="font-bold md:font-normal">
                <span>{{ field.name }}</span>
            </h4>
        </div>

        <div class="md:w-3/4 md:py-3 break-all lg:break-words">
            <div class="md:w-3/4 md:py-3 break-all lg:break-words"
                 style=" display: flex; flex-wrap: wrap;
                 justify-content: stretch; align-items: start;
                 gap: 8px;">

                <div v-for="item in field.value ? field.value : []"
                     style=" display: flex; flex-direction: column;
                     flex-wrap: wrap; justify-content: stretch;
                     align-items: start; gap: 4px;">

                    <img :alt="item.title ?? item.name" :src="item.url"
                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;
                        background-image: linear-gradient(45deg, rgba(241, 245, 249, 0.5), rgba(100, 116, 139, 0.4));"/>
                    <span style="width: 100px; height: 16px;
                          font-style: normal; font-weight: 400; font-size: 14px; text-align: center;
                          max-lines: 1; overflow: hidden;text-overflow: ellipsis;">
                            {{ item.title ?? item.name }}
                    </span>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
    mounted() {
        console.log('index: ', this.index);
        console.log('resource: ', this.resource);
        console.log('resourceName: ', this.resourceName);
        console.log('resourceId: ', this.resourceId);
        console.log('field: ', this.field);
    },
}
</script>
