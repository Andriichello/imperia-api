<template>
  <card class="container h-auto w-full flex flex-col">
    <div class="w-full flex flex-row justify-between px-3 py-3">
      <h1 class="text-center text-3xl text-gray-500 font-light">Метрики</h1>

      <button class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"
              :disabled="isFetching" @click="onFetch">
        {{ isFetching ? 'Завантаження...' : 'Завантажити'}}
      </button>
    </div>

    <div class="w-full flex flex-row justify-center gap-6 px-3 py-3">
      <div class="flex flex-col justify-center">
        <label for="beg" class="font-semibold">Початок</label>
        <input name="beg" type="date"
               class="bg-transparent"
               v-model="beg">
      </div>

      <div class="flex flex-col justify-center">
        <label for="end" class="font-semibold">Кінець</label>
        <input name=end type="date"
               class="bg-transparent"
               v-model="end">
      </div>
    </div>

    <div class="w-full flex flex-col flex-wrap justify-center items-between px-4 py-4 gap-2">
      <div class="w-full flex flex-wrap justify-start items-start gap-6">
        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="banquets['amount'] ?? null"
                    title="Банкети"
                    description="Кількість банкетів у станах: новий, підтверджений та завершений"/>
      </div>

      <div class="w-full flex flex-wrap justify-start items-start gap-6">
        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="guests['total'] ?? null"
                    title="Гості"
                    description="Кількість гостів (діти + дорослі)"/>

        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="guests['adults'] ?? null"
                    title="Дорослі"
                    description="Кількість дорослих"/>

        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="guests['children'] ?? null"
                    title="Діти"
                    description="Кількість дітей"/>
      </div>

      <div class="w-full flex flex-wrap justify-start items-start gap-6">
        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="totals['total'] ?? null"
                    title="Вартість"
                    description="Загальна вартість усіх банкетів (розрахована по замовленим у системі позиціям + вартість квитків)"/>

        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="totals['actual_total'] ?? null"
                    title="Фактична вартість"
                    description="Загальна фактична вартість усіх банкетів (введена вручну працівниками закладу, при відсутності цього значення використовується вартість)"/>

        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="totals['tickets_total'] ?? null"
                    title="Вартість квитків"
                    description="Загальна вартість квитків усіх банкетів"/>
      </div>

      <div class="w-full flex flex-wrap justify-start items-start gap-6">
        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="totals?.metrics?.difference ?? null"
                    title="Різниця вартостей"
                    description="Різниця вартостей = Фактична Вартість - Вартість"/>

        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="totals?.metrics?.percentage ?? null"
                    title="Відсоткова різниця"
                    description="Відсоткова різниця = Різниця вартостей / Вартість"/>

        <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                    :value="totals?.actual_total && banquets?.amount ? Math.round((totals?.actual_total / banquets?.amount +  Number.EPSILON) * 100) / 100 : null"
                    title="Середня вартість"
                    description="Середня вартість = Фактична вартість / Банкети"/>
      </div>

      <template v-if="menuSales?.length">
        <div class="w-full flex justify-center items-center mt-8">
          <h1 class="text-center text-3xl text-gray-500 font-light">Меню</h1>
        </div>

        <div class="w-full flex flex-wrap justify-start items-start gap-6"
             v-for="menuSale in menuSales" :key="menuSale?.id">
          <div style="flex-basis: 25%; max-width: 30%;">
            <span class="text-lg text-gray-500 font-normal">{{ menuSale?.title ?? null }}</span>
          </div>

          <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                      :value="menuSale?.amount ?? null"
                      title="Продажі"/>

          <ValueBlock class="flex-grow" style="flex-basis: 25%; max-width: 30%;"
                      :value="menuSale?.total ?? null"
                      title="На суму"/>
        </div>

      </template>
    </div>
  </card>
</template>

<script>
import ValueBlock from "./ValueBlock.vue";

export default {
  components: {ValueBlock},
  props: [
    'card',

    // The following props are only available on resource detail cards...
    // 'resource',
    // 'resourceId',
    // 'resourceName',
  ],
  data() {
    const date = new Date();
    const beg = new Date(date.getFullYear(), date.getMonth(), 1);
    const end = new Date(date.getFullYear(), date.getMonth() + 1, 0);

    return {
      beg: this.formatDate(beg),
      end: this.formatDate(end),
      metrics: null,
      isFetching: false,
    }
  },
  computed: {
    summary() {
      return this.metrics?.summary ?? null;
    },
    banquets() {
      return this.summary?.banquets ?? {
        amount: null,
      }
    },
    guests() {
      return this.summary?.guests ?? {
        adults: null,
        children: null,
        total: null,
      }
    },
    totals() {
      return this.summary?.totals ?? {
        total: null,
        actual_total: null,
        tickets_total: null,
        metrics: {
          difference: null,
          percentage: null,
        }
      }
    },
    sales() {
      return this.summary?.sales ?? {
        menus: null,
        products: null,
      }
    },
    menuSales() {
      return this.sales?.menus ?? null;
    },
    productSales() {
      return this.sales?.products ?? null;
    }
  },
  methods: {
    formatDate(date) {
      let d = new Date(date);
      let month = '' + (d.getMonth() + 1);
      let day = '' + d.getDate();
      const year = d.getFullYear();

      if (month.length < 2) {
        month = '0' + month;
      }

      if (day.length < 2) {
        day = '0' + day;
      }

      return [year, month, day].join('-');
    },
    splitOnChunks(array, chunkSize) {
      const chunks = [];

      for (let i = 0; i < array.length; i += chunkSize) {
        chunks.push(array.slice(i, i + chunkSize));
      }

      return chunks;
    },
    fetchMetrics(restaurantId) {
      const url = `/nova-vendor/metrics-card/full`;
      let query = `?restaurant_id=${restaurantId}`;

      if (this.beg?.length) {
        query += `&beg=${this.beg}`;
      }
      if (this.end?.length) {
        query += `&end=${this.end}`;
      }

      this.isFetching = true;
      Nova.request().get(url + query)
        .then(response => {
          this.metrics = response.data;
          this.isFetching = false;
        });
    },
    onFetch() {
      if (this.isFetching) {
        return;
      }

      this.fetchMetrics(1);
    },
  },
  mounted() {
    this.fetchMetrics(1);
  },
}
</script>
