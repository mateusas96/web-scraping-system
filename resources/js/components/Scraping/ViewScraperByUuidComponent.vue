<template>
    <div class="container component">
        <div class="row justify-content-center">
            <v-card 
                class="mx-auto"
            >
                <v-card-title>
                    Scraper ({{ this.$route.params.scraperName }}) scraped data
                </v-card-title>

                <v-data-table
                    hide-default-footer
                    :loading="loading"
                    sort-by="created_at"
                    :sort-desc="true"
                    loading-text="Loading... Please wait"
                    :headers="headers"
                    :items="scrapedData"
                    show-expand
                    :single-expand="true"
                    :expanded.sync="expanded"
                    item-key="id"
                >
                    <template v-slot:[`item.hashtag`]="{}">
                        #
                    </template>
                    <template v-slot:expanded-item="{ headers, item }">
                        <td :colspan="headers.length">
                            <div class="mt-4" v-if="item.scraped_detail_info == true">
                                More info about <u>{{ item.product_name }}:</u>
                                <ul class="mt-2">
                                    <li>
                                        <b>Color:</b> {{ item.color }}
                                    </li>
                                    <li>
                                        <b>Available sizes:</b> {{ item.available_size }}
                                    </li>
                                    <li>
                                        <b>Unavailable sizes:</b> {{ item.unavailable_size }}
                                    </li>
                                    <li>
                                        <b>URL to product:</b> <a :href="item.product_link" target="_blank">here</a>
                                    </li>
                                </ul>
                            </div>
                            <div v-else>
                                More info about <u>{{ item.product_name }}</u> is <a :href="item.product_link" target="_blank">here</a>
                            </div>
                        </td>
                    </template>

                </v-data-table>
                <v-pagination
                    v-model="scrapedDataPagination.current_page"
                    :length="scrapedDataPagination.last_page"
                    v-on:input="getScrapedData"
                    :total-visible="7"
                ></v-pagination>
            </v-card>
        </div>
    </div>
</template>

<script>
import {showScrollbar ,hideScrollbar} from '../../app';

export default {
    data: () => {
        return {
            headers: [
                { text: '#', align: 'start', sortable: false, value: 'hashtag' },
                { text: 'Root category', value: 'category' },
                { text: 'Subcategory', value: 'category_name' },
                { text: 'Product name', value: 'product_name' },
                { text: 'Current price', value: 'normal_price' },
                { text: 'Old price', value: 'old_price' },
                { text: 'Scraped at', value: 'created_at' },
                { text: '', value: 'data-table-expand' },
            ],
            loading: false,
            scrapedDataPagination: [],
            scrapedData: [],
            expanded: []
        }
    },
    mounted() {
        hideScrollbar();
        this.getScrapedData();
    },
    destroyed() {
        hideScrollbar();
    },
    watch: {
        
    },
    methods: {
        getScrapedData(page = 1) {
            this.loading = true;
            let params = {
                'page': page,
                'scraper_name': this.$route.params.scraperName,
            }

            axios.get(`${window.location.origin}/api/scraping/`, { params: params })
            .then(({data}) => {
                this.scrapedData = data.data;
                this.scrapedDataPagination = data;
                this.loading = false;
            });
        }
    }
}
</script>
