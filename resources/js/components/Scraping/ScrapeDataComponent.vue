<template>
    <div class="container component" style="width: 80vw;">
        <div class="row justify-content-center">
            <v-card 
                class="mx-auto"
            >
                <v-card-title>
                    <v-col
                        cols="12"
                    >
                        My selected scraping files list
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn 
                                    v-on:click="refreshMyFiles = !refreshMyFiles"
                                    v-on="on"
                                    small
                                    :disabled="refreshMyFiles"
                                    class="ml-3"
                                >
                                    <v-icon v-if="refreshMyFiles" small>fas fa-sync fa-spin</v-icon>
                                    <v-icon v-else small>fas fa-sync</v-icon>
                                </v-btn>
                            </template>
                            <small>Refresh my files list</small>
                        </v-tooltip>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn 
                                    v-on:click="showDrawer = !showDrawer"
                                    v-on="on"
                                    small
                                    class="ml-3"
                                >
                                    <v-icon small>fas fa-plus</v-icon>
                                </v-btn>
                            </template>
                            <small>Add file for scraping</small>
                        </v-tooltip>
                    </v-col>
                    <!-- TODO -->
                    <v-row>
                        <v-col
                            cols="2"
                        >
                            <v-select
                                v-model="scrape_everything"
                                id="scrape-everything"
                                label="Scrape everything"
                                :items="scrapingEverything"
                                item-text="name"
                                item-value="value"
                                clearable
                                v-on:change="getMyFiles"
                                :open-on-clear="true"
                            ></v-select>
                        </v-col>
                        <v-col
                            cols="2"
                        >
                            <v-select
                                v-model="scraping_status"
                                id="scraping-status"
                                label="Scraping status"
                                :items="scrapingStatus"
                                item-text="name"
                                item-value="value"
                                clearable
                                v-on:change="getMyFiles"
                                :open-on-clear="true"
                            ></v-select>
                        </v-col>
                        <v-col
                            cols="2"
                        >
                            <v-text-field
                                v-on:input="searchit"
                                v-model="search"
                                append-icon="mdi-magnify"
                                label="Search"
                                single-line
                                hide-details
                                style="max-width: 18.75rem;"
                                clearable
                            ></v-text-field>
                        </v-col>
                        <v-col
                            cols="2"
                        >
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn 
                                        v-on:click="
                                            () => {
                                                search = '';
                                                scraping_status = '';
                                                scrape_everything = '';
                                                loading = true;
                                                getMyFiles();
                                            }
                                        "
                                        v-on="on"
                                        small
                                        class="mt-4"
                                    >
                                        <v-icon small>fas fa-times</v-icon>
                                    </v-btn>
                                </template>
                                <small>Clear filters</small>
                            </v-tooltip>
                        </v-col>
                    </v-row>
                </v-card-title>
                <v-data-table
                    hide-default-footer
                    :loading="loading"
                    sort-by="scraper_created_at"
                    loading-text="Loading... Please wait"
                    :headers="headers"
                    :items="myFiles"
                    id="myFilesTable"
                >
                    <template v-slot:[`item.hashtag`]="{}">
                        #
                    </template>
                    <template v-slot:[`item.scraping_status`]="{ item }">
                        <div v-for="status in scrapingStatus" :key="status.value">
                            {{ status.value == item.scraping_status ? status.name : null }}
                        </div>
                    </template>
                    <template v-slot:[`item.started_scraping_date`]="{ item }">
                        {{item.started_scraping_date === null ? '-' : item.started_scraping_date}}
                    </template>
                    <template v-slot:[`item.stopped_scraping_date`]="{ item }">
                        {{item.stopped_scraping_date === null ? '-' : item.stopped_scraping_date}}
                    </template>

                </v-data-table>
                <v-pagination
                    v-model="myFilesPagination.current_page"
                    :length="myFilesPagination.last_page"
                    v-on:input="getFileResults"
                    :total-visible="7"
                ></v-pagination>
            </v-card>
            <v-navigation-drawer
                v-model="showDrawer"
                absolute
                temporary
                bottom
                right
                width="30vw"
                height="100vh"
                class="mt-12"
            >
                <v-container>
                    <v-list-item>
                        <v-list-item-content>
                            <v-list-item-title>
                                <h3>Add file for scraping</h3>
                            </v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    <v-divider></v-divider>
                    <validation-observer
                        ref="observer"
                        v-slot="{ invalid }"
                    >
                        <form
                            class="d-flex justify-start ml-10"
                        >
                            <v-row align="center">
                                <v-col
                                    cols="12"
                                    sm="11"
                                >
                                    <validation-provider
                                        v-slot="{ errors }"
                                        name="Scraper name"
                                        :rules="{
                                            required: true,
                                            min: 3,
                                            max: 50
                                        }"
                                    >
                                    <v-text-field
                                        v-model="selectedFilesForm.scraper_name"
                                        :counter="50"
                                        :error-messages="errors"
                                        label="Scraper name"
                                    ></v-text-field>
                                </validation-provider>
                                </v-col>
                                <v-col
                                    cols="12"
                                    sm="11"
                                    style="margin-top: -1.5rem;"
                                >
                                    <validation-provider
                                        v-slot="{ errors }"
                                        name="Scraper select"
                                        :rules="{
                                            required: true,
                                        }"
                                    >
                                        <v-autocomplete
                                            v-model="selectedFilesForm.selected_files"
                                            :items="filesForSelect"
                                            item-text="file_name"
                                            item-value="id"
                                            label="Select one or more scraper files"
                                            :error-messages="errors"
                                            chips
                                            deletable-chips
                                            multiple
                                        ></v-autocomplete>
                                    </validation-provider>
                                </v-col>
                                <v-col
                                    cols="12"
                                    sm="12"
                                    style="margin-bottom: -2.5rem; margin-top: -2rem;"
                                    v-show="!selectedFilesForm.scrape_all"
                                >
                                    <div v-for="(param, index) in selectedFilesForm.scraper_params" :key="index">
                                        <validation-provider
                                            v-slot="{ errors }"
                                            name="Products to scrape"
                                            :rules="{
                                                required: !selectedFilesForm.scrape_all,
                                                max: 15
                                            }"
                                        >
                                            <v-row
                                                v-bind:style="{ 'margin-top': index > 0 ? '-1.5rem' : '0rem'}"
                                            >
                                                <v-col
                                                    sm="10"
                                                    v-bind:style="{ 'margin-top': index > 0 ? '0.75rem' : '0rem'}"
                                                >
                                                <v-text-field
                                                    rows="1"
                                                    v-model="param.name"
                                                    :counter="15"
                                                    :error-messages="errors"
                                                    label="Products to scrape"
                                                    auto-grow
                                                    clearable
                                                    :persistent-hint="true"
                                                    :hint="'Product #' + `${index + 1}`"
                                                ></v-text-field>
                                                </v-col>
                                                <v-col
                                                    sm="2"
                                                    class="mt-6"
                                                >
                                                    <v-tooltip bottom>
                                                        <template v-slot:activator="{ on }">
                                                            <v-icon 
                                                                v-if="index === 0" 
                                                                medium 
                                                                v-on="on"
                                                                v-on:click="addScraperParam"
                                                            >fas fa-plus</v-icon>
                                                        </template>
                                                        <small>Add product for scraping</small>
                                                    </v-tooltip>
                                                    <v-tooltip bottom>
                                                        <template v-slot:activator="{ on }">
                                                            <v-icon 
                                                                v-if="index > 0" 
                                                                medium 
                                                                v-on="on"
                                                                v-on:click="deleteScraperParam(index)"
                                                            >fas fa-trash</v-icon>
                                                            </template>
                                                        <small>Remove product from scraping</small>
                                                    </v-tooltip>
                                                </v-col>
                                            </v-row>
                                        </validation-provider>
                                    </div>
                                </v-col>
                                <v-row 
                                    align="center"
                                    v-bind:style="{ 'margin-top': selectedFilesForm.scrape_all ? '-1.5rem' : '1rem' }"
                                >
                                    <v-col cols="1">
                                        <v-checkbox
                                            v-model="selectedFilesForm.scrape_all"
                                            color="success"
                                            :value="true"
                                            hide-details
                                            v-on:change="selectedFilesForm.scraper_params = [{ name: '' }]"
                                            class="mb-5 ml-2"
                                        ></v-checkbox>
                                    </v-col>
                                    <v-col cols="6">
                                        <v-subheader>
                                            <b>Scrape everything</b>
                                        </v-subheader>
                                    </v-col>
                                    <v-col
                                        cols="5"
                                        sm="5"
                                    >
                                        <v-btn
                                            class="success ml-16"
                                            :disabled="invalid"
                                            v-on:click="submitSelectedFilesForm"
                                        >
                                            Submit
                                        </v-btn>
                                </v-col>
                                </v-row>
                            </v-row>
                        </form>
                        <br><br><br><br>
                    </validation-observer>
                </v-container>
            </v-navigation-drawer>
        </div>
    </div>
</template>

<script>
import {hideScrollbar, showScrollbar} from '../../app';
import { required, min, max, regex } from 'vee-validate/dist/rules'
import { extend, ValidationObserver, ValidationProvider, setInteractionMode } from 'vee-validate'

setInteractionMode('eager');

extend('required', {
    ...required,
    message: '{_field_} can not be empty',
});
extend('min', {
    ...min,
    message: '{_field_} must contain at least three character',
});
extend('max', {
    ...max,
    message: '{_field_} may not be greater than {length} characters',
});

export default {
    components: {
        ValidationProvider,
        ValidationObserver,
    },
    data: () => {
        return {
            myFiles: [],
            myFilesPagination: [],
            loading: false,
            refreshMyFiles: false,
            showDrawer: false,
            selectedFilesForm: new Form({
                scraper_name: '',
                scraper_params: [ { name: '' } ],
                selected_files: [],
                scrape_all: true,
            }),
            scrapeAll: true,
            filesForSelect: [],
            headers: [
                { text: '#', align: 'start', sortable: false, value: 'hashtag' },
                { text: 'Scraper name', value: 'scraper_name' },
                { text: 'Selected files', value: 'selected_files', width: '250px' },
                { text: 'Scraping status', value: 'scraping_status' },
                { text: 'Scrape everything', value: 'scrape_all' },
                { text: 'Products to scrape', value: 'scraping_params' },
                { text: 'Started scraping date', value: 'started_scraping_date' },
                { text: 'Stopped scraping date', value: 'stopped_scraping_date' },
                { text: 'Scraper created at', value: 'scraper_created_at' },
                { text: 'Actions', value: 'actions', sortable: false },
            ],
            search: '',
            scrapingEverything: [
                { name: 'True', value: 1 },
                { name: 'False', value: 0 },
            ],
            scrapingStatus: [
                { value: 'scraping_not_started', name: 'Scraping not started' },
                { value: 'scraping_initiated', name: 'Scraping started' },
                { value: 'scraping_finished', name: 'Scraping finished' },
                { value: 'scraping_stopped_for_a_reason', name: 'Scraping stopped for a reason' },
            ],
            scrape_everything: '',
            scraping_status: '',
        }
    },
    mounted() {
        this.getFilesForSelect();
        this.loading = true;
        this.getMyFiles();
        Fire.$on('searchMyFile', () => {
            this.getMyFiles();
        });
    },
    destroyed() {
        hideScrollbar();
    },
    watch: {
        refreshMyFiles: {
            handler: function(newVal, oldVal) {
                if (newVal) {
                    this.loading = true;
                    this.getMyFiles();
                }
            }
        },
        showDrawer: {
            handler: function(newVal, oldVal) {
                if (oldVal) {
                    this.$refs.observer.reset();
                    this.selectedFilesForm.reset();
                }
            }
        },
        myFiles: {
            handler: function(newVal, oldVal) {
                setTimeout(() => {
                    $(window).height() < 950 &&
                    $('.container.component').height() > $(window).height() - 100 ?
                    showScrollbar() : hideScrollbar();
                }, 300);        
            }
        }
    },
    methods: {
        getMyFiles() {
            let params = {
                'query': (this.search === null ? '' : this.search),
                'scrape_everything': this.scrape_everything,
                'scraping_status': this.scraping_status,
            };

            axios.get('/api/selectedFilesForScraping', { params: params })
            .then(({data}) => {
                this.myFilesPagination = data;
                this.myFiles = data.data;
                this.loading = false;
                this.refreshMyFiles = false;
            })
            .catch({});
        },
        submitSelectedFilesForm() {
            this.$refs.observer.validate();
            axios.post('/api/selectedFilesForScraping', this.selectedFilesForm)
            .then(({data}) => {
                if (!data.error) {
                    this.$toastr.Add({
                        title: 'Success',
                        msg: data.message,
                        type: 'success',
                        timeout: 3500,
                        progressbar: true,
                        position: 'toast-top-right',
                    });
                    this.showDrawer = false;
                    this.getMyFiles();
                }
            });
        },
        getFilesForSelect() {
            axios.get('/api/get_files_for_select')
            .then(({data}) => {
                this.filesForSelect = data;
            })
            .catch(() => {})
        },
        addScraperParam() {
            this.selectedFilesForm.scraper_params.push({ name: '' });
        },
        deleteScraperParam(index) {
            this.selectedFilesForm.scraper_params.splice(index, 1);
        },
        getFileResults(page = 1) {
            this.loading = true;
            let params = {
                'page': page,
                'query': (this.search === null ? '' : this.search),
                'scrape_everything': this.scrape_everything,
                'scraping_status': this.scraping_status,
            };

            axios.get('/api/selectedFilesForScraping', { params: params })
                .then(({data}) => {
                this.myFilesPagination = data;
                this.myFiles = data.data;
                this.loading = false;
                this.refreshMyFiles = false;
            });
        },
        searchit: _.debounce(() => {
            Fire.$emit('searchMyFile');
        }, 500),
    },
}
</script>
