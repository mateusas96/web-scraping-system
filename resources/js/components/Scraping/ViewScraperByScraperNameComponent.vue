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
                                    v-on:click="
                                        () => {
                                            productLink = item.product_link;
                                            productName = item.product_name;
                                            productCurrency = item.currency;
                                            fromDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().substr(0, 10);
                                            toDate = new Date().toISOString().substr(0, 10);
                                            loadingChart = true;
                                            displayDataInChart();
                                        }
                                    "
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
                <div
                    class="pt-6 pl-6"
                >
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
                </div>
                <v-card-title class="d-flex justify-center">
                    <p>Select date to view product info</p>
                    <v-col
                        cols="12"
                        md="3"
                    >
                        <v-menu
                            v-model="fromDateMenu"
                            :close-on-content-click="false"
                            :nudge-right="40"
                            transition="scale-transition"
                            offset-y
                            min-width="auto"
                        >
                            <template v-slot:activator="{ on, attrs }">
                                <v-text-field
                                    v-model="fromDate"
                                    label="From date"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                    v-bind="attrs"
                                    v-on="on"
                                ></v-text-field>
                            </template>
                            <v-date-picker
                                v-model="fromDate"
                                v-on:input="fromDateMenu = false"
                                :first-day-of-week="1"
                                :max="toDate"
                                v-on:change="
                                    () => {
                                        newChartDateLoading = true;
                                        displayDataInChart();
                                    }
                                "
                            ></v-date-picker>
                        </v-menu>
                    </v-col>
                    <v-col
                        cols="12"
                        md="3"
                    >
                        <v-menu
                            v-model="toDateMenu"
                            :close-on-content-click="false"
                            :nudge-right="40"
                            transition="scale-transition"
                            offset-y
                            min-width="auto"
                        >
                            <template v-slot:activator="{ on, attrs }">
                                <v-text-field
                                    v-model="toDate"
                                    label="To date"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                    v-bind="attrs"
                                    v-on="on"
                                ></v-text-field>
                            </template>
                            <v-date-picker
                                v-model="toDate"
                                v-on:input="toDateMenu = false"
                                :first-day-of-week="1"
                                :max="maxDate"
                                :min="fromDate"
                                v-on:change="
                                    () => {
                                        newChartDateLoading = true;
                                        displayDataInChart();
                                    }
                                "
                            ></v-date-picker>
                        </v-menu>
                    </v-col>
                </v-card-title>
                <v-card-text>
                    <v-progress-linear
                        indeterminate
                        color="primary"
                        v-show="newChartDateLoading"
                    ></v-progress-linear>
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
            fromDateMenu: false,
            toDateMenu: false,
            fromDate: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().substr(0, 10),
            toDate: new Date().toISOString().substr(0, 10),
            maxDate: new Date().toISOString().substr(0, 10),
            productLink: null,
            productName: null,
            productCurrency: null,
            newChartDateLoading: false,
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
                this.fromDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().substr(0, 10);
                this.toDate = new Date().toISOString().substr(0, 10);
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
        async displayDataInChart () {

            let params = {
                'product_link': this.productLink,
                'scraper_name': this.$route.params.scraperName,
                'from_date': this.fromDate,
                'to_date': this.toDate,
            };

            await axios.get(`${window.location.origin}/api/get_chart_data/`, { params: params })
            .then(({data}) => {
                this.loadingChart = false;
                this.newChartDateLoading = false;
                this.tempChartData = data;
            });

            this.chartData = {
                labels: this.tempChartData['created_at'],
                datasets: [
                    {
                        label: `${this.productName}, Currency: ${this.productCurrency}, Current price`,
                        backgroundColor: '#1976d2',
                        data: this.tempChartData['normal_price'],
                        pointRadius:4.5,
                        pointHoverRadius: 7,
                        fill: true,
                    },
                    {
                        label: `${this.productName}, Currency: ${this.productCurrency}, Old price`,
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
                $('.container.component').height() > $(window).height() - 110 ?
                showScrollbar() : hideScrollbar();
            }, 300);
        }
    }
}
</script>
