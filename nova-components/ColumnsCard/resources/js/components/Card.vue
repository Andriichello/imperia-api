<template>
    <card class="columns-card lex flex-col p-6">

        <div id="header">
            <h4>{{ settings.title }}</h4>

            <button class="btn btn-default btn-primary"
                    @click="applyFields">
                Save
            </button>
        </div>

        <ul id="checkboxes">
            <li v-for="model of models"
                id="fields" class="pt-1 pb-1">

                <label :for="model.attribute" class="label flex items-center">

                    <input :id="model.attribute"
                           :checked="model.checked"
                           type="checkbox"
                           class="checkbox"
                           @change="onFieldToggled">

                    <span class="ml-3">{{ model.label }}</span>

                </label>
            </li>

        </ul>

    </card>
</template>

<script>


import {isEmpty} from "lodash";
import {Filterable, mapProps} from "laravel-nova";
import HandlesActions from "../../../../../nova/resources/js/mixins/HandlesActions";
import {escapeUnicode} from "../../../../../nova/resources/js/util/escapeUnicode";

export default {
    props: [
        'card',

        ...mapProps([
            'resourceName',
            'viaResource',
            'viaResourceId',
            'viaRelationship'
        ])
    ],
    mixins: [Filterable, HandlesActions],
    data() {
        const settings = this.card.settings;
        const fields = this.relevantFields(this.card.fields, this.cached(settings.cache.key, 'fields'));

        return {
            fields: fields,
            models: fields,
            settings: settings,
        }
    },
    mounted() {
        this.applyFields();
    },

    methods: {
        cache(cacheKey, value, property = undefined) {
            try {
                if (property == null) {
                    localStorage.setItem(cacheKey, JSON.stringify(value));
                    return true;
                }

                const item = JSON.parse(localStorage.getItem(cacheKey));
                item[property] = value;

                return this.cache(cacheKey, item);
            } catch (error) {
                return false;
            }
        },

        cached(cacheKey, property = undefined) {
            try {
                const item = JSON.parse(localStorage.getItem(cacheKey));
                return property == null ? item : item[property];
            } catch (error) {
                return null
            }
        },

        applyFields() {
            const query = this.getEncodedQueryString();

            this.cache(this.settings.cache.key, {query: query, fields: this.fields});

            this.updateColumnsFilter();
        },

        relevantFields(provided, stored) {
            if (isEmpty(stored)) {
                return provided;
            }

            let fields = isEmpty(provided) ? [] : provided;
            for (const field of fields) {
                for (const storedField of stored) {
                    if (field.attribute !== storedField.attribute) {
                        continue;
                    }

                    field.checked = storedField.checked;
                }
            }

            return fields;
        },

        onFieldToggled(event) {
            console.log('onFieldToggled')

            const id = event.target.id;
            const checked = event.target.checked;

            for (let field of this.fields) {
                if (field.attribute !== id) {
                    continue;
                }
                field.checked = checked;
            }
        },

        updateColumnsFilter() {
            const filter = this.$store.getters[`${this.resourceName}/getFilter`](this.settings.filter.class);
            if (filter == null) {
                console.error('There is no filter for ColumnsCard');
                return;
            }

            if (filter.currentValue === this.fields) {
                return;
            }

            filter.currentValue = this.fields;
            this.filterChanged();
        },

        updateQueryString(value) {
            console.log('updateQueryString');

            try {
                const key = this.resourceName + '_filter';
                const query = this.$router.history.current['query'];

                if (query[key] === value[key]) {
                    this.$router.replace({query: null});
                    return;
                }
            } catch (error) {
                //
            }
            this.$router.replace({query: value});
        },

        decodeObject(data) {
            try {
                return JSON.parse(atob(data))
            } catch (e) {
                return {}
            }
        },

        encodeObject(data) {
            return btoa(escapeUnicode(JSON.stringify(data)));
        },

        getEncodedQueryString() {
            return this.encodeObject({...this.fields})
        },

        cachedEncodedQueryString() {
            return this.cached(this.settings.cache.key, 'query');
        },
    },
}
</script>

<style lang="scss">

.columns-card {
    height: auto
}

#header {
    display: flex;
    justify-content: space-between;

    padding-bottom: 10px;
}

#checkboxes {
    height: auto;
    column-count: 4;
    padding: 0;
    list-style-type: none;
}

li {
    -webkit-column-break-inside: avoid;
    page-break-inside: avoid;
    break-inside: avoid;
}

</style>
