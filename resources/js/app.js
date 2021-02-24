import VueRouter from 'vue-router';
import { Form, HasError, AlertError } from 'vform';
import VueProgressBar from 'vue-progressbar';
import Swal from 'sweetalert2';
import VueMaterial from 'vue-material';
import 'vue-material/dist/vue-material.min.css';
import 'vue-material/dist/theme/default.css';
import Vuetify from 'vuetify';
import VueToastr from 'vue-toastr';
import { template } from 'lodash';

require('./bootstrap');

window.Vue = require('vue');
window.Fire = new Vue();
window.Form = Form;
window.Swal = Swal;

Vue.use(VueRouter);
Vue.use(Vuetify);
Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);
Vue.use(VueProgressBar, {
    color: 'rgb(143, 255, 199)',
    failedColor: 'red',
    thickness: '4px'
});
Vue.use(VueMaterial);
Vue.use(VueToastr);

let routes = [
    { path: '/home', component: require('./components/Home/HomeComponent.vue').default },
    { path: '/users/management', component: require('./components/UsersManagement/UsersManagementComponent.vue').default },
    { path: '/upload-configs', component: require('./components/Scraping/UploadConfigComponent.vue').default },
    { path: '/scrape-data', component: require('./components/Scraping/ScrapeDataComponent.vue').default },
    { path: '/scrape-data/view-scraper/:scraperName', component: require('./components/Scraping/ViewScraperByUuidComponent.vue').default },
    { path: '/403', component: require('./components/Errors/403.vue').default },
];

// hide scrollbar if needed
export function hideScrollbar() {
    let isMobile = window.matchMedia('only screen and (max-width: 768px)').matches;

    if (!isMobile) {
        window.scrollTo({
            top: 0, 
            left: 0, 
            behavior: 'smooth',
        });
        let elHtml = document.getElementsByTagName('html')[0];
        elHtml.style.overflowY = 'hidden';
    }
}

// show scrollbar if needed
export function showScrollbar() {
    let elHtml = document.getElementsByTagName('html')[0];
    elHtml.style.overflowY = null;
}

/**
 * get current user anywhere if needed,
 * user is initialized on login or on every page refresh
 */
export var current_user = axios.get(`${window.location.origin}/api/current`);

/**
 * reinitialize current user when user self-updates
 */
export function updateCurrentUser() {
    current_user = axios.get(`${window.location.origin}/api/current`);
}

const router = new VueRouter({
    mode: 'history',
    routes
});

const app = new Vue({
    el: '#app',
    router,
    vuetify: new Vuetify(),
});
