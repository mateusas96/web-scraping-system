<template>
    <div class="container component">
        <div class="row justify-content-center">
            <v-card 
                class="mx-auto"
            >
                <v-card-title>
                    Scraper ({{ this.$route.params.scraperName }}) scraped data
                    <v-spacer></v-spacer>
                    Total records: {{ this.scrapedDataPagination.total }}
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

                    <template v-slot:[`item.actions`]="{ item }">
                        <v-progress-circular
                            :size="30"
                            color="primary"
                            indeterminate
                            v-show="loadingChart"
                        ></v-progress-circular>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-icon
                                    v-on="on"
                                    class="ml-2"
                                    v-on:click="displayDataInChart(item.product_link, item.product_name, item.currency);"
                                    v-show="!loadingChart"
                                >
                                    fas fa-chart-line
                                </v-icon>
                            </template>
                            <small>Display in chart</small>
                        </v-tooltip>
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

            <v-card 
                class="mt-16" 
                v-show="showChart"
                width="75rem"
            >
                <v-card-title>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-icon 
                                medium
                                v-on="on"
                                v-on:click="showChart = false;"
                            >fas fa-times</v-icon>
                        </template>
                        <small>Close chart</small>
                    </v-tooltip>
                </v-card-title>
                <v-card-text>
                    <line-chart 
                        :chart-data="chartData"
                        :options="chartOptions"
                        :height="500"
                        :width="1000"
                    ></line-chart>
                </v-card-text>
            </v-card>
        </div>
    </div>
</template>

<script>
import {showScrollbar ,hideScrollbar} from '../../app';
import LineChart from '../../LineChart.js'

export default {
    components: {
        LineChart
    },
    data: () => {
        return {
            headers: [
                { text: '#', align: 'start', sortable: false, value: 'hashtag' },
                { text: 'Root category', value: 'category' },
                { text: 'Subcategory', value: 'category_name' },
                { text: 'Product name', value: 'product_name' },
                { text: 'Current price', value: 'normal_price' },
                { text: 'Old price', value: 'old_price' },
                { text: 'Currency', value: 'currency' },
                { text: 'Scraped at', value: 'created_at' },
                { text: 'Actions', value: 'actions' },
                { text: '', value: 'data-table-expand' },
            ],
            loading: false,
            scrapedDataPagination: [],
            scrapedData: [],
            expanded: [],
            tempChartData: [],
            chartData: null,
            showChart: false,
            loadingChart: false,
            chartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                scales:{
                    yAxes:[{
                        ticks:{
                            beginAtZero: true,
                        }
                    }]
                },
            },
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
        expanded: {
            handler: function() {
                this.handleScrollbar();
            }
        },
        scrapedData: {
            handler: function() {
                this.handleScrollbar();
            }
        },
        showChart: {
            handler: function() {
                this.handleScrollbar();
            }
        }
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
        },
        async displayDataInChart (productLink, productName, currency) {
            this.loadingChart = true;

            let params = {
                'product_link': productLink,
                'scraper_name': this.$route.params.scraperName,
            }

            await axios.get(`${window.location.origin}/api/get_chart_data/`, { params: params })
            .then(({data}) => {
                this.loadingChart = false;
                this.tempChartData = data;
            });

            this.chartData = {
                labels: this.tempChartData['created_at'],
                datasets: [
                    {
                        label: `${productName}, Currency: ${currency}, Current price`,
                        backgroundColor: '#1976d2',
                        data: this.tempChartData['normal_price'],
                        pointRadius:4.5,
                        pointHoverRadius: 7,
                        fill: true,
                    },
                    {
                        label: `${productName}, Currency: ${currency}, Old price`,
                        backgroundColor: '#f87979',
                        data: this.tempChartData['old_price'],
                        pointRadius:4.5,
                        pointHoverRadius: 7,
                        fill: true,
                    },
                ]
            };

            this.showChart = true;
        },
        handleScrollbar() {
            setTimeout(() => {
                $(window).height() < 950 &&
                $('.container.component').height() > $(window).height() - 100 ?
                showScrollbar() : hideScrollbar();
            }, 300);
        }
    }
}
</script>
