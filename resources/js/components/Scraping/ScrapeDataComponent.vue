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
                        My scrapers
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    v-on:click="refreshMyFiles = !refreshMyFiles"
                                    v-on="on"
                                    small
                                    :disabled="refreshMyFiles"
                                    class="ml-6"
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
                    <v-row>
                        <v-col
                            cols="12"
                            sm="12"
                            md="3"
                            xl="2"
                        >
                            <v-select
                                v-model="scrape_everything"
                                id="scrape-everything"
                                label="Scrape everything"
                                :items="scrapingEverything"
                                item-text="name"
                                item-value="value"
                                clearable
                                v-on:change="
                                    () => {
                                        loading = true;
                                        getMyFiles();
                                    }
                                "
                                :open-on-clear="true"
                            ></v-select>
                        </v-col>
                        <v-col
                            cols="12"
                            sm="12"
                            md="3"
                            xl="2"
                        >
                            <v-select
                                v-model="scraping_status"
                                id="scraping-status"
                                label="Scraping status"
                                :items="scrapingStatus"
                                item-text="name"
                                item-value="value"
                                clearable
                                v-on:change="
                                    () => {
                                        loading = true;
                                        getMyFiles();
                                    }
                                "
                                :open-on-clear="true"
                            ></v-select>
                        </v-col>
                        <v-col
                            cols="9"
                            sm="9"
                            md="3"
                            xl="2"
                        >
                            <v-text-field
                                v-on:input="searchit"
                                v-model="search"
                                append-icon="mdi-magnify"
                                label="Search"
                                hide-details
                                style="max-width: 18.75rem;"
                                clearable
                            ></v-text-field>
                        </v-col>
                        <v-col
                            cols="3"
                            sm="3"
                            md="2"
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
                                        :disabled="
                                            (search === '' || search === null) &&
                                            (scraping_status === '' || scraping_status === undefined) &&
                                            (scrape_everything === '' || scrape_everything === undefined)
                                        "
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
                    :sort-desc="true"
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
                    <template v-slot:[`item.schedule`]="{ item }">
                        <div v-for="schedule in scraping_schedule" :key="schedule.value">
                            {{ schedule.value == item.schedule ? schedule.name : '' }}
                        </div>
                    </template>
                    <template v-slot:[`item.started_scraping_date`]="{ item }">
                        {{item.started_scraping_date === null ? '-' : item.started_scraping_date}}
                    </template>
                    <template v-slot:[`item.finished_scraping_date`]="{ item }">
                        {{item.finished_scraping_date === null ? '-' : item.finished_scraping_date}}
                    </template>

                    <template v-slot:[`item.actions`]="{ item }">
                        <v-progress-circular
                            :size="30"
                            color="primary"
                            indeterminate
                            v-show="scraping"
                        ></v-progress-circular>
                        <v-row v-show="!scraping">
                            <v-speed-dial
                                :top="false"
                                direction="left"
                                :open-on-hover="false"
                                transition="slide-x-reverse-transition"
                            >
                                <template v-slot:activator>
                                    <v-btn
                                        v-model="fab"
                                        color="blue darken-2"
                                        dark
                                        fab
                                        small
                                    >
                                        <v-icon>
                                            fas fa-cog
                                        </v-icon>
                                    </v-btn>
                                </template>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn
                                            v-on="on"
                                            class="ml-2"
                                            v-on:click="deleteScraper(item.uuid, item.scraper_name)"
                                            fab
                                            dark
                                            color="purple"
                                            small
                                        >
                                            <v-icon small>
                                                fas fa-trash
                                            </v-icon>
                                        </v-btn>
                                    </template>
                                    <small>Delete scraper</small>
                                </v-tooltip>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn
                                            v-on="on"
                                            class="ml-2"
                                            v-show="
                                                item.scraping_status != 'scraping_not_started' ||
                                                item.scraping_status != 'scraping_stopped_manually'
                                            "
                                            fab
                                            dark
                                            color="pink"
                                            small
                                            v-on:click="changeScraperStoppedStatus(item.uuid, 'scraping_stopped_manually')"
                                        >
                                            <v-icon small>
                                                fas fa-ban
                                            </v-icon>
                                        </v-btn>
                                    </template>
                                    <small>Stop scraper</small>
                                </v-tooltip>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn
                                            v-on="on"
                                            class="ml-2"
                                            v-show="
                                                (
                                                    item.scraping_status == 'scraping_stopped_manually' ||
                                                    item.scraping_status == 'scraping_not_started'
                                                ) &&
                                                item.selected_files_error_messages == null
                                            "
                                            fab
                                            dark
                                            color="green"
                                            small
                                            v-on:click="changeScraperStoppedStatus(item.uuid, 'scraping_not_started')"
                                        >
                                            <v-icon small>
                                                fas fa-play-circle
                                            </v-icon>
                                        </v-btn>
                                    </template>
                                    <small>Start scraper</small>
                                </v-tooltip>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn
                                            v-on="on"
                                            class="ml-2"
                                            v-on:click="scrapeOnce(item.uuid, item.scrape_all)"
                                            fab
                                            dark
                                            color="teal"
                                            small
                                            v-show="
                                                (
                                                    item.scraping_status == 'scraping_finished' ||
                                                    item.selected_files_error_messages == null
                                                ) && item.scraping_status != 'scraping_initiated'
                                            "
                                        >
                                            <v-icon small>
                                                fas fa-chevron-circle-right
                                            </v-icon>
                                        </v-btn>
                                    </template>
                                    <small>Run scraper ONCE</small>
                                </v-tooltip>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn
                                            v-on="on"
                                            :to="`/scrape-data/view-scraper/${item.scraper_name}`"
                                            fab
                                            dark
                                            color="cyan"
                                            small
                                        >
                                            <v-icon small color="white">
                                                fas fa-external-link-alt
                                            </v-icon>
                                        </v-btn>
                                    </template>
                                    <small>Open scraper</small>
                                </v-tooltip>
                            </v-speed-dial>

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-icon
                                        v-on="on"
                                        class="ml-2"
                                        v-show="item.selected_files_error_messages != null"
                                    >
                                        fas fa-exclamation-triangle
                                    </v-icon>
                                </template>
                                <small>Error in config will be fixed by administration</small>
                            </v-tooltip>
                        </v-row>
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
                style="position: fixed"
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
                        ref="form"
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
                                        name="scraper name"
                                        :rules="{
                                            required: true,
                                            min: 3,
                                            max: 50,
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
                                        name="scraper files"
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
                                        <v-row
                                            v-bind:style="{ marginTop: index > 0 ? '-1.5rem' : '0rem' }"
                                        >
                                            <v-col
                                                cols="10"
                                                sm="10"
                                                v-bind:style="{ marginTop: index > 0 ? '0.75rem' : '0rem' }"
                                            >
                                                {{'Product #' + (index + 1)}}
                                                <v-col
                                                    style="margin-bottom: -1.5rem; margin-top: -1rem"
                                                >
                                                    <validation-provider
                                                        v-slot="{ errors }"
                                                        name="root category"
                                                        :rules="{
                                                            required: !selectedFilesForm.scrape_all,
                                                        }"
                                                    >
                                                        <v-select
                                                            v-model="param.selected_root_category"
                                                            id="select-root-category"
                                                            label="Select root category"
                                                            :items="root_category"
                                                            :error-messages="errors"
                                                            item-text="name"
                                                            item-value="value"
                                                            clearable
                                                            :open-on-clear="true"
                                                        ></v-select>
                                                    </validation-provider>
                                                    <validation-provider
                                                        v-slot="{ errors }"
                                                        name="subcategory"
                                                        :rules="{
                                                            required: !selectedFilesForm.scrape_all,
                                                            max: 50
                                                        }"
                                                    >
                                                        <v-text-field
                                                            rows="1"
                                                            v-model="param.subcategory"
                                                            :counter="50"
                                                            :error-messages="errors"
                                                            label="Subcategory"
                                                            auto-grow
                                                            clearable
                                                            :persistent-hint="true"
                                                            hint="Subcategory must be exact same as in a website"
                                                        ></v-text-field>
                                                    </validation-provider>
                                                </v-col>
                                                <v-col>
                                                    <validation-provider
                                                        v-slot="{ errors }"
                                                        name="product to scrape"
                                                        :rules="{
                                                            required: !selectedFilesForm.scrape_all,
                                                            max: 50
                                                        }"
                                                    >
                                                        <v-text-field
                                                            rows="1"
                                                            v-model="param.name"
                                                            :counter="50"
                                                            :error-messages="errors"
                                                            label="Product to scrape"
                                                            auto-grow
                                                            clearable
                                                            :persistent-hint="true"
                                                            hint="Product name must be exact same as in a website"
                                                        ></v-text-field>
                                                    </validation-provider>
                                                </v-col>
                                            </v-col>
                                            <v-col
                                                sm="2"
                                                style="margin-top: 7rem"
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
                                            <v-divider></v-divider>
                                        </v-row>
                                    </div>
                                </v-col>
                                <v-col
                                    cols="12"
                                    sm="11"
                                    v-bind:style="{ marginTop: selectedFilesForm.scrape_all ? '-1.5rem' : '1rem' }"
                                >
                                    <validation-provider
                                        v-slot="{ errors }"
                                        name="schedule"
                                        :rules="{
                                            required: true,
                                        }"
                                    >
                                        <v-select
                                            v-model="selectedFilesForm.schedule"
                                            id="select-schedule"
                                            label="Select schedule"
                                            :items="scraping_schedule"
                                            :error-messages="errors"
                                            item-text="name"
                                            item-value="value"
                                            clearable
                                            :open-on-clear="true"
                                        ></v-select>
                                    </validation-provider>
                                </v-col>
                                <v-row
                                    align="center"
                                    v-bind:style="{ marginTop: selectedFilesForm.scrape_all ? '-1.5rem' : '-1rem' }"
                                >
                                    <v-col cols="1">
                                        <v-checkbox
                                            v-model="selectedFilesForm.scrape_all"
                                            color="success"
                                            :value="true"
                                            hide-details
                                            v-on:change="
                                                () => {
                                                    selectedFilesForm.scraper_params = [{ name: '' }];
                                                    selectedFilesForm.detailed_information_about_product = false;
                                                }
                                            "
                                            class="mb-5 ml-2"
                                        ></v-checkbox>
                                    </v-col>
                                    <v-col cols="10" sm="6">
                                        <v-subheader>
                                            <b>Scrape everything</b>
                                        </v-subheader>
                                    </v-col>
                                </v-row>
                                <v-row
                                    align="center"
                                    style="margin-top: -2.5rem"
                                >
                                    <v-col cols="1">
                                        <v-checkbox
                                            v-model="selectedFilesForm.detailed_information_about_product"
                                            color="success"
                                            :value="false"
                                            hide-details
                                            :disabled="selectedFilesForm.scrape_all"
                                            class="mb-5 ml-2"
                                        ></v-checkbox>
                                    </v-col>
                                    <v-col cols="10" sm="6">
                                        <v-subheader>
                                            <b>Scrape detailed information about product</b>
                                        </v-subheader>
                                    </v-col>
                                    <v-col
                                        cols="12"
                                        sm="7"
                                        style="margin-top: -1.5rem"
                                        v-show="selectedFilesForm.scrape_all"
                                    >
                                        <v-subheader
                                            style="font-size: 12px;"
                                        >Can not scrape detailed product information if scrape everything is selected</v-subheader>
                                    </v-col>
                                    <v-col
                                        cols="5"
                                        sm="5"
                                        v-bind:style="{marginBottom: selectedFilesForm.scrape_all ? '1.5rem' : '0rem'}"
                                    >
                                        <v-btn
                                            class="success"
                                            :disabled="invalid"
                                            v-on:click="submitSelectedFilesForm"
                                        >
                                            Save
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
import { required, min, max } from 'vee-validate/dist/rules';
import { extend, ValidationObserver, ValidationProvider, setInteractionMode } from 'vee-validate';

setInteractionMode('eager');

extend('required', {
    ...required,
    message: 'The {_field_} can not be empty',
});
extend('min', {
    ...min,
    message: 'The {_field_} must contain at least {length} characters',
});
extend('max', {
    ...max,
    message: 'The {_field_} may not be greater than {length} characters',
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
                scraper_params: [
                    {selected_root_category: '', subcategory: '', name: ''},
                ],
                selected_files: [],
                scrape_all: true,
                detailed_information_about_product: false,
                schedule: '',
            }),
            scrapeAll: true,
            filesForSelect: [],
            headers: [
                {text: '#', align: 'start', sortable: false, value: 'hashtag'},
                {text: 'Scraper name', value: 'scraper_name'},
                {text: 'Selected files', value: 'selected_files', width: '250px'},
                {text: 'Scrape everything', value: 'scrape_all'},
                {text: 'Scraped detailed product info', value: 'detailed_information_about_product'},
                {text: 'Scraping parameters', value: 'scraping_params'},
                {text: 'Scraping status', value: 'scraping_status'},
                {text: 'Schedule', value: 'schedule'},
                {text: 'Scraper stopped', value: 'scraper_stopped'},
                {text: 'Started scraping date', value: 'started_scraping_date'},
                {text: 'Finished scraping date', value: 'finished_scraping_date'},
                {text: 'Scraper created at', value: 'scraper_created_at'},
                {text: 'Actions', value: 'actions', sortable: false, width: '100px'},
            ],
            search: '',
            scrapingEverything: [
                {name: 'Yes', value: 1},
                {name: 'No', value: 0},
            ],
            scrapingStatus: [
                {value: 'scraping_not_started', name: 'Scraping not started'},
                {value: 'scraping_initiated', name: 'Scraping started'},
                {value: 'scraping_finished', name: 'Scraping finished'},
                {value: 'scraping_stopped_for_a_reason', name: 'Scraping stopped for a reason'},
                {value: 'scraping_stopped_manually', name: 'Scraping was stopped manually'},
            ],
            scrape_everything: '',
            scraping_status: '',
            root_category: [
                {name: 'Women', value: 'Women'},
                {name: 'Men', value: 'Men'},
                {name: 'Children', value: 'Children'},
            ],
            scraping: false,
            fab: false,
            scraping_schedule: [
                {name: 'Daily (at 6AM)', value: 'daily'},
                {name: 'Weekly (every Monday at 6AM)', value: 'weekly'},
            ],
            windowWidth: 0,
        }
    },
    mounted() {
        this.loading = true;
        this.getMyFiles();
        this.getFilesForSelect();
        Fire.$on('searchMyFile', () => {
            this.getMyFiles();
        });
        this.windowWidth = window.innerWidth;
    },
    destroyed() {
        hideScrollbar();
    },
    watch: {
        refreshMyFiles: {
            handler: function (newVal, oldVal) {
                if (newVal) {
                    this.loading = true;
                    this.getMyFiles();
                }
            }
        },
        showDrawer: {
            handler: function (newVal, oldVal) {
                if (oldVal) {
                    this.$refs.form.reset();
                    this.selectedFilesForm.reset();
                }
            }
        },
        myFiles: {
            handler: function (newVal, oldVal) {
                setTimeout(() => {
                    $(window).height() < 950 &&
                    $('.container.component').height() > $(window).height() - 110 ?
                        showScrollbar() : hideScrollbar();
                }, 300);
            }
        }
    },
    methods: {
        async getMyFiles() {
            let params = {
                'query': (this.search === null ? '' : this.search),
                'scrape_everything': this.scrape_everything,
                'scraping_status': this.scraping_status,
            };

            await axios.get('/api/selectedFilesForScraping', {params: params})
                .then(({data}) => {
                    this.myFilesPagination = data;
                    this.myFiles = data.data;
                    this.loading = false;
                    this.refreshMyFiles = false;
                })
                .catch({});
        },
        submitSelectedFilesForm() {
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
                        this.loading = true;
                        this.showDrawer = false;
                        this.getMyFiles();
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.$refs.form.setErrors({
                            'scraper name': 'You have already created scraper with this name',
                        });
                    }
                });
        },
        getFilesForSelect() {
            axios.get('/api/get_files_for_select')
                .then(({data}) => {
                    this.filesForSelect = data;
                })
                .catch(() => {
                })
        },
        addScraperParam() {
            this.selectedFilesForm.scraper_params.push({name: ''});
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

            axios.get('/api/selectedFilesForScraping', {params: params})
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
        scrapeOnce(itemUuid, scrapeAll) {
            this.scraping = true;

            if (scrapeAll == 'Yes') {
                this.$toastr.Add({
                    title: 'Info',
                    msg: 'Might take some time to scrape all data',
                    type: 'info',
                    timeout: 10000,
                    progressbar: true,
                    position: 'toast-top-right',
                });
            }

            axios.post(`/api/scrape_data_once/${itemUuid}`)
                .then(({data}) => {
                    this.scraping = false;
                    this.$toastr.Add({
                        title: 'Success',
                        msg: data.message,
                        type: 'success',
                        timeout: 3500,
                        progressbar: true,
                        position: 'toast-top-right',
                    });
                })
                .catch(error => {
                    this.scraping = false;

                    this.$toastr.Add({
                        title: 'Error',
                        msg: 'Something went wrong, we will fix it soon',
                        type: 'error',
                        timeout: 3500,
                        progressbar: true,
                        position: 'toast-top-right',
                    });

                    let error_params = {
                        'error_message': error.response.data.message,
                        'uuid': itemUuid,
                    };

                    axios.put('/api/update_file_with_error_message_from_scraper', error_params);

                    let status_params = {
                        'uuid': itemUuid,
                        'status_code': 'scraping_stopped_for_a_reason',
                    };

                    axios.put('/api/update_scraper_status', status_params)
                        .then(() => {
                            this.getMyFiles();
                        });

                });
        },
        deleteScraper(itemUuid, scraperName) {
            Swal.fire({
                title: 'Delete scraper',
                text: `Are you sure you want to delete scraper - ${scraperName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/api/selectedFilesForScraping/${itemUuid}`)
                        .then(({data}) => {
                            if (!data.error) {
                                this.loading = true;
                                this.getMyFiles();
                                this.$toastr.Add({
                                    title: 'Success',
                                    msg: data.message,
                                    type: 'success',
                                    timeout: 3500,
                                    progressbar: true,
                                    position: 'toast-top-right',
                                });
                            }
                        });
                }
            });
        },
        openScraperInfo(scraperName) {
            window.location.href = `/scrape-data/view-scraper/${scraperName}`;
        },
        changeScraperStoppedStatus(scraperUuid, statusCode) {
            let params = {
                'status_code': statusCode,
                'uuid': scraperUuid,
            };

            if (statusCode == 'scraping_not_started') {
                this.$toastr.Add({
                    title: 'Info',
                    msg: 'Scraper will start as it is scheduled',
                    type: 'info',
                    timeout: 3500,
                    progressbar: true,
                    position: 'toast-top-right',
                });
            }

            axios.put('/api/change_scraper_stopped_status', params)
                .then(() => {
                    this.getMyFiles();
                });
        }
    },
}
</script>
