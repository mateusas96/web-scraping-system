import VueRouter from 'vue-router';
import { Form, HasError, AlertError } from 'vform';
import VueProgressBar from 'vue-progressbar';
import Swal from 'sweetalert2';
import VueMaterial from 'vue-material';
import 'vue-material/dist/vue-material.min.css';
import 'vue-material/dist/theme/default.css';
import Vuetify from 'vuetify';
import VueToastr from "vue-toastr";

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

Vue.material.locale.dateFormat = 'dd/MM/yyyy';

let routes = [
    { path: '/home', component: require('./components/Home/HomeComponent.vue').default },
    { path: '/users/management', component: require('./components/UsersManagement/UsersManagementComponent.vue').default },
    { path: '/upload-configs', component: require('./components/Scraping/UploadConfigComponent.vue').default }
];

const router = new VueRouter({
    mode: 'history',
    routes
});

const app = new Vue({
    el: '#app',
    router,
    vuetify: new Vuetify(),
});
